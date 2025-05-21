<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\AppointmentMailer;
use App\Service\GoogleCalendarService;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Mailer\MailerAwareTrait;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Calendar Controller
 *
 * Customer-facing interface for viewing available time slots and scheduling appointments.
 *
 * @property \App\Model\Table\AppointmentsTable $Appointments
 * @property \App\Model\Table\WritingServiceRequestsTable $WritingServiceRequests
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\RequestMessagesTable $RequestMessages
 */
class CalendarController extends AppController
{
    use MailerAwareTrait;
    
    /**
     * @var \App\Service\GoogleCalendarService
     */
    protected GoogleCalendarService $googleCalendarService;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        
        $this->Appointments = $this->fetchTable('Appointments');
        $this->WritingServiceRequests = $this->fetchTable('WritingServiceRequests');
        $this->Users = $this->fetchTable('Users');
        $this->RequestMessages = $this->fetchTable('RequestMessages');
        
        try {
            // Try to load GoogleCalendarSettings if it exists
            $this->GoogleCalendarSettings = $this->fetchTable('GoogleCalendarSettings');
        } catch (\Exception $e) {
            \Cake\Log\Log::warning('GoogleCalendarSettings table not available: ' . $e->getMessage());
        }
        
        $this->googleCalendarService = new GoogleCalendarService();
        
        // Allow availability and book methods for unauthenticated users
        $this->Authentication->addUnauthenticatedActions(['availability', 'book', 'getTimeSlots']);
    }
    
    /**
     * Index method - redirects to availability view
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        return $this->redirect(['action' => 'availability']);
    }
    
    /**
     * Show availability calendar view
     *
     * @param string|null $requestId Writing service request ID
     * @return void
     */
    public function availability(?string $requestId = null)
    {
        // Get month/year from query parameters or default to current
        $month = (int)$this->request->getQuery('month', date('n'));
        $year = (int)$this->request->getQuery('year', date('Y'));
        
        // Ensure valid month/year values
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }
        if ($year < date('Y') || $year > date('Y') + 2) {
            $year = (int)date('Y');
        }
        
        // Get current date and time
        $today = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
        
        // Find an admin user for calendar availability
        $adminUser = $this->Users->find()
            ->where(['user_type' => 'admin', 'is_verified' => true])
            ->order(['last_login' => 'DESC'])
            ->first();
        
        if (empty($adminUser)) {
            $this->Flash->error(__('No admin users are available to check calendar availability.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
        }
        
        // Prepare calendar data structure
        $calendarData = $this->_buildCalendarData($month, $year, $today, $adminUser->user_id);
        
        // Get the related writing service request if ID provided
        $writingServiceRequest = null;
        if (!empty($requestId)) {
            try {
                $writingServiceRequest = $this->WritingServiceRequests->get($requestId, [
                    'contain' => ['Users'],
                ]);
            } catch (Exception $e) {
                $this->Flash->error(__('Invalid writing service request.'));
            }
        }
        
        $this->set(compact('month', 'year', 'today', 'calendarData', 'requestId', 'writingServiceRequest'));
    }
    
    /**
     * Get available time slots for a specific date
     *
     * @return void
     */
    public function getTimeSlots()
    {
        $this->request->allowMethod(['get', 'ajax']);
        
        // Return JSON response
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', ['success', 'timeSlots']);
        
        $success = false;
        $timeSlots = [];
        
        // Get date from query parameter
        $date = $this->request->getQuery('date');
        
        if (!empty($date) && strtotime($date)) {
            // Find an admin user for calendar availability
            $adminUser = $this->Users->find()
                ->where(['user_type' => 'admin', 'is_verified' => true])
                ->order(['last_login' => 'DESC'])
                ->first();
            
            if (!empty($adminUser)) {
                $dateObj = new DateTime($date, new DateTimeZone(date_default_timezone_get()));
                
                // Define working hours (9 AM to 5 PM by default)
                $workingHours = [
                    'start' => '09:00',
                    'end' => '17:00',
                ];
                
                // Get free time slots
                $timeSlots = $this->googleCalendarService->getFreeTimeSlots($adminUser->user_id, $dateObj, $workingHours);
                $success = true;
            }
        }
        
        $this->set(compact('success', 'timeSlots'));
    }
    
    /**
     * Accept a time slot from chat
     *
     * @return \Cake\Http\Response|null
     */
    public function acceptTimeSlot()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            $this->Flash->error(__('You must be logged in to book an appointment.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        // Get parameters from query string
        $date = $this->request->getQuery('date');
        $time = $this->request->getQuery('time');
        $requestId = $this->request->getQuery('request_id');
        $messageId = $this->request->getQuery('message_id');
        $type = $this->request->getQuery('type', 'writing'); // Default to 'writing' for backward compatibility
        
        // URL decode parameters to handle URL encoded characters
        $date = urldecode($date);
        $time = urldecode($time);
        
        if (empty($date) || empty($time) || empty($requestId)) {
            $this->Flash->error(__('Missing required parameters.'));
            
            // Check if this is a coaching request or writing request based on the type parameter
            if ($type === 'coaching') {
                return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
            } else {
            return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
            }
        }
        
        // Extract actual time from formatted time string (e.g., "9:00 AM - 9:30 AM" -> "09:00")
        $timeMatch = [];
        $originalTime = $time; // Keep a copy of the original time string for fallback
        
        if (preg_match('/(\d+:\d+)\s*([AP]M)\s*-/i', $time, $timeMatch)) {
            $timePart = $timeMatch[1];
            $amPm = strtoupper($timeMatch[2]);
            
            // Convert to 24-hour format
            $timeObj = new \DateTime($timePart . ' ' . $amPm);
            $time = $timeObj->format('H:i');
        } else {
            // Try a simpler approach for 24-hour format (e.g., "09:00 - 09:30")
            if (preg_match('/^(\d{1,2}):(\d{2})\s*-/', $time, $timeMatches)) {
                $hours = (int)$timeMatches[1];
                $minutes = (int)$timeMatches[2];
                
                // Validate hours and minutes
                if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
                    $time = sprintf('%02d:%02d', $hours, $minutes);
                } else {
                    $this->Flash->error(__('Invalid time format. Hours and minutes must be valid.'));
                    if ($type === 'coaching') {
                        return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                    } else {
                    return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                    }
                }
            } else {
                // One last approach - try to handle the specific case directly
                if (trim($originalTime) === '09:00 - 09:30' || trim($originalTime) === '9:00 - 9:30') {
                    $time = '09:00';
                } else {
                    $this->Flash->error(__('Could not parse the appointment time.'));
                    if ($type === 'coaching') {
                        return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                    } else {
                    return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                    }
                }
            }
        }
        
        // Parse the date (e.g., "Monday, June 3, 2024" -> "2024-06-03")
        $dateObj = null;
        try {
            $dateObj = new \DateTime($date);
            $formattedDate = $dateObj->format('Y-m-d');
        } catch (\Exception $e) {
            // Try more specific parsing patterns
            if (preg_match('/([A-Za-z]+),\s+([A-Za-z]+)\s+(\d+),\s+(\d{4})/', $date, $matches)) {
                $dateStr = $matches[2] . ' ' . $matches[3] . ', ' . $matches[4];
                try {
                    $dateObj = new \DateTime($dateStr);
                    $formattedDate = $dateObj->format('Y-m-d');
                } catch (\Exception $e2) {
                    // Try another fallback method with specific English month names
                    $months = [
                        'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
                        'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
                        'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
                    ];
                    
                    // Try a more explicit pattern match: weekday, month day, year
                    if (isset($matches[2]) && isset($months[$matches[2]]) && 
                        isset($matches[3]) && isset($matches[4])) {
                        $month = $months[$matches[2]];
                        $day = (int)$matches[3];
                        $year = (int)$matches[4];
                        
                        // Validate day, month, year
                        if ($day > 0 && $day <= 31 && $year >= 2000 && $year <= 2100) {
                            $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $dateObj = new \DateTime($formattedDate);
                        } else {
                            $this->Flash->error(__('Could not parse the appointment date.'));
                            if ($type === 'coaching') {
                                return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                            } else {
                            return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                            }
                        }
                    } else {
                        $this->Flash->error(__('Could not parse the appointment date.'));
                        if ($type === 'coaching') {
                            return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                        } else {
                        return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                        }
                    }
                }
            } else {
                // One more fallback - try to extract month, day, year from anywhere in the string
                if (preg_match('/(?:Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+\d{1,2}(?:st|nd|rd|th)?,?\s+\d{4}/i', $date, $dateMatch)) {
                    try {
                        $extractedDate = new \DateTime($dateMatch[0]);
                        $formattedDate = $extractedDate->format('Y-m-d');
                        $dateObj = $extractedDate;
                    } catch (\Exception $e3) {
                        $this->Flash->error(__('Could not parse the appointment date.'));
                        if ($type === 'coaching') {
                            return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                        } else {
                        return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                        }
                    }
                } else {
                    $this->Flash->error(__('Could not parse the appointment date.'));
                    if ($type === 'coaching') {
                        return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                    } else {
                    return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                    }
                }
            }
        }
        
        try {
            // Create a new appointment
            $appointment = $this->Appointments->newEmptyEntity();
            
            // Find an admin user for the appointment
            $adminUser = $this->Users->find()
                ->where(['user_type' => 'admin', 'is_verified' => true])
                ->order(['last_login' => 'DESC'])
                ->first();
            
            if (empty($adminUser)) {
                $this->Flash->error(__('No admin available for appointment scheduling.'));
                if ($type === 'coaching') {
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                } else {
                return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
            }
            }
            
            if ($type === 'coaching') {
                // Load the CoachingServiceRequests model if not already loaded
                try {
                    $this->CoachingServiceRequests = $this->fetchTable('CoachingServiceRequests');
                    
                    // Get the coaching service request
                    $serviceRequest = $this->CoachingServiceRequests->get($requestId, [
                        'contain' => ['Users']
                    ]);
                    
                    // Check if the writing service request belongs to the current user
                    if ($serviceRequest->user_id !== $user->user_id) {
                        $this->Flash->error(__('You can only book appointments for your own coaching service requests.'));
                        return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'index']);
                    }
                    
                    // We'll use the coaching service request title later
                    $requestTitle = $serviceRequest->service_title ?? 'Coaching Service';
                    $requestTypeDesc = 'coaching service';
                    
                } catch (\Exception $e) {
                    $this->Flash->error(__('Invalid coaching service request.'));
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'index']);
                }
            } else {
            // Get the writing service request
                try {
                    $serviceRequest = $this->WritingServiceRequests->get($requestId, [
                'contain' => ['Users']
            ]);
            
            // Check if the writing service request belongs to the current user
                    if ($serviceRequest->user_id !== $user->user_id) {
                $this->Flash->error(__('You can only book appointments for your own writing service requests.'));
                return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'index']);
                    }
                    
                    // We'll use the writing service request title later
                    $requestTitle = $serviceRequest->service_title ?? 'Writing Service';
                    $requestTypeDesc = 'writing service';
                } catch (\Exception $e) {
                    $this->Flash->error(__('Invalid writing service request.'));
                    return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'index']);
                }
            }
            
            // Check if an appointment already exists for this time slot and request
            $existingAppointment = $this->Appointments->find()
                ->where([
                    'user_id' => $user->user_id,
                    'appointment_date' => new \Cake\I18n\Date($formattedDate),
                    'appointment_time' => new \Cake\I18n\Time($time),
                    'status !=' => 'cancelled',
                    'is_deleted' => false
                ])
                ->first();
                
            if ($existingAppointment) {
                $this->Flash->info(__('You already have an appointment for this time slot. No new appointment was created.'));
                if ($type === 'coaching') {
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId, '#' => 'messages']);
                } else {
                return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId, '#' => 'messages']);
                }
            }
            
            // Set appointment details
            $appointment->user_id = $user->user_id;
            $appointment->appointment_type = 'consultation';
            $appointment->appointment_date = new \Cake\I18n\Date($formattedDate);
            
            try {
                $appointment->appointment_time = new \Cake\I18n\Time($time);
            } catch (\Exception $e) {
                // Try creating with a datetime string instead
                try {
                    $timeObj = new \DateTime('1970-01-01 ' . $time);
                    $timeString = $timeObj->format('H:i:s');
                    $appointment->appointment_time = new \Cake\I18n\Time($timeString);
                } catch (\Exception $e2) {
                    $this->Flash->error(__('Could not parse the appointment time.'));
                    if ($type === 'coaching') {
                        return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                    } else {
                    return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                    }
                }
            }
            
            $appointment->duration = 30; // Default to 30 minutes
            $appointment->status = 'confirmed';
            $appointment->location = 'Google Meet';
            $appointment->is_deleted = false;
            $appointment->is_google_synced = false;
            $appointment->description = 'Consultation for ' . $requestTypeDesc . ' request: ' . $requestTitle . ' (ID: ' . $requestId . ')';
            
            // Save the appointment
            if ($this->Appointments->save($appointment)) {
                // Create Google Calendar event and get Google Meet link
                $eventId = null;
                $meetLink = null;
                
                // Check if admin has Google Calendar configured
                $adminHasCalendarConfig = false;
                if (isset($this->GoogleCalendarSettings)) {
                    $adminCalendarSettings = $this->GoogleCalendarSettings->find()
                        ->where(['user_id' => $adminUser->user_id, 'is_active' => true])
                        ->first();
                    $adminHasCalendarConfig = !empty($adminCalendarSettings);
                }
                
                if ($adminHasCalendarConfig) {
                    try {
                        // Create Google Calendar event
                        $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminUser->user_id);
    
                        if ($eventId) {
                            // Get the Google Meet link and update the appointment
                            $event = $this->googleCalendarService->getEvent($eventId, $adminUser->user_id);
                            $meetLink = $this->googleCalendarService->getMeetLink($event);
                            
                            if ($meetLink) {
                                $appointment->meeting_link = $meetLink;
                                $appointment->google_calendar_event_id = $eventId;
                                $appointment->is_google_synced = true;
                                $this->Appointments->save($appointment);
                            }
                        }
                    } catch (\Exception $e) {
                        // Log error but continue with fallback
                    }
                }
                
                // If Google Calendar sync failed, create a generic Meet link
                if (!$meetLink) {
                    $meetLink = "https://meet.google.com/lookup/" . substr(md5($appointment->appointment_id . time()), 0, 10);
                    $appointment->meeting_link = $meetLink;
                    $this->Appointments->save($appointment);
                }
                
                // Send confirmation message in the chat
                $message = "✅ **Appointment Confirmed**\n\n";
                $message .= "Your appointment has been scheduled for **" . $dateObj->format('l, F j, Y') . " at " . 
                    (new \DateTime($time))->format('g:i A') . "**.\n\n";
                $message .= "A confirmation email has been sent to your email address with all the details and meeting link. I look forward to our meeting!";
                
                if ($type === 'coaching') {
                    // For coaching service requests, we need to use the CoachingRequestMessages table
                    $this->CoachingRequestMessages = $this->fetchTable('CoachingRequestMessages');
                    
                    $messageEntity = $this->CoachingRequestMessages->newEntity([
                        'user_id' => $user->user_id,
                        'coaching_service_request_id' => $requestId,
                        'message' => $message,
                        'is_read' => false
                    ]);
                    
                    $this->CoachingRequestMessages->save($messageEntity);
                } else {
                    // For writing service requests, we use the RequestMessages table
                $messageEntity = $this->RequestMessages->newEntity([
                    'user_id' => $user->user_id,
                    'writing_service_request_id' => $requestId,
                    'message' => $message,
                    'is_read' => false
                ]);
                
                $this->RequestMessages->save($messageEntity);
                }
                
                // Send confirmation emails
                try {
                    // Get fresh appointment data with related entities
                    try {
                        // Check if the WritingServiceRequests association exists
                        $containList = ['Users'];
                        
                        $columns = $this->Appointments->getSchema()->columns();
                        if (in_array('writing_service_request_id', $columns)) {
                            $containList[] = 'WritingServiceRequests';
                        }
                        if (in_array('coaching_service_request_id', $columns)) {
                            $containList[] = 'CoachingServiceRequests';
                        }
                        
                        $appointment = $this->Appointments->get($appointment->appointment_id, [
                            'contain' => $containList,
                        ]);
                    } catch (\Exception $e1) {
                        $appointment = $this->Appointments->get($appointment->appointment_id, [
                            'contain' => ['Users'],
                        ]);
                    }
                    
                    // Send email to admin
                    $mailer = new AppointmentMailer();
                    
                    try {
                        // Send admin notification with fixed email
                        $adminEmail = 'diana@dianabonvini.com';
                        $adminName = 'Diana Bonvini';
                        $mailer->send('adminNotification', [$appointment, $adminEmail, $adminName]);
                    } catch (\Exception $e2) {
                        // Continue even if admin email fails
                    }
                    
                    // Send confirmation to customer
                    try {
                        // Get a fresh instance of the appointment with updated meeting link
                        $freshAppointment = $this->Appointments->get($appointment->appointment_id, [
                            'contain' => ['Users'],
                        ]);
                        
                        // Use the AppointmentMailer directly for better consistency
                        try {
                            $mailer = new \App\Mailer\AppointmentMailer('default');
                            $mailer->appointmentConfirmation($freshAppointment);
                            $mailer->deliver();
                        } catch (\Exception $e4) {
                            // Fall back to previous method if the AppointmentMailer fails
                            $mailer->send('appointmentConfirmation', [$freshAppointment]);
                        }
                    } catch (\Exception $e3) {
                        // Continue even if customer email fails
                    }
                } catch (\Exception $e) {
                    // Continue even if email process fails
                }
                
                // Always show success message even if Google Calendar sync failed
                $this->Flash->success(__('Appointment confirmed for {0} at {1}. You will receive a confirmation email shortly.', 
                    $dateObj->format('l, F j, Y'), 
                    (new \DateTime($time))->format('g:i A')
                ));
                
                if ($type === 'coaching') {
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId, '#' => 'messages']);
                } else {
                return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId, '#' => 'messages']);
                }
            } else {
                $this->Flash->error(__('The appointment could not be saved. Please try again.'));
                if ($type === 'coaching') {
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                } else {
                return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                }
            }
        } catch (\Exception $e) {
            $this->Flash->error(__('An error occurred: {0}', $e->getMessage()));
            if ($type === 'coaching') {
                return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
            } else {
            return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
            }
        }
    }
    
    /**
     * Book an appointment
     *
     * @return \Cake\Http\Response|null
     */
    public function book()
    {
        // Redirect to login if not authenticated
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            $this->Flash->info(__('Please login to book an appointment.'));
            
            // Store booking parameters in session to redirect back after login
            if ($this->request->is('get') && !empty($this->request->getQueryParams())) {
                $this->request->getSession()->write('Calendar.bookingParams', $this->request->getQueryParams());
            }
            
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        $appointment = $this->Appointments->newEmptyEntity();
        
        // Populate appointment from query parameters if available
        $date = $this->request->getQuery('date');
        $time = $this->request->getQuery('time');
        $requestId = $this->request->getQuery('request_id');
        
        // Or retrieve from session if returning from login
        $bookingParams = $this->request->getSession()->read('Calendar.bookingParams');
        if (!empty($bookingParams)) {
            $date = $bookingParams['date'] ?? null;
            $time = $bookingParams['time'] ?? null;
            $requestId = $bookingParams['request_id'] ?? null;
            
            // Clear session data
            $this->request->getSession()->delete('Calendar.bookingParams');
        }
        
        // Get related writing service request if ID provided
        $writingServiceRequest = null;
        if (!empty($requestId)) {
            try {
                $writingServiceRequest = $this->WritingServiceRequests->get($requestId, [
                    'contain' => ['Users'],
                ]);
                
                // Ensure the request belongs to the current user
                if ($writingServiceRequest->user_id !== $user->user_id) {
                    $this->Flash->error(__('You can only book appointments for your own writing service requests.'));
                    return $this->redirect(['action' => 'availability']);
                }
                
                $appointment->writing_service_request_id = $requestId;
            } catch (Exception $e) {
                $this->Flash->error(__('Invalid writing service request.'));
                return $this->redirect(['action' => 'availability']);
            }
        }
        
        if (!empty($date)) {
            $appointment->appointment_date = new Date($date);
        }
        
        if (!empty($time)) {
            $appointment->appointment_time = new Time($time);
        }
        
        // Set default values
        $appointment->user_id = $user->user_id;
        $appointment->appointment_type = 'initial_consultation';
        $appointment->duration = 30;
        $appointment->status = 'pending';
        $appointment->is_deleted = false;
        $appointment->is_google_synced = false;
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Set default values not in form
            $data['user_id'] = $user->user_id;
            $data['is_deleted'] = false;
            $data['is_google_synced'] = false;
            $data['status'] = 'pending';
            
            // Convert date and time formats
            if (!empty($data['appointment_date'])) {
                $data['appointment_date'] = new Date($data['appointment_date']);
            }
            if (!empty($data['appointment_time'])) {
                $data['appointment_time'] = new Time($data['appointment_time']);
            }
            
            $appointment = $this->Appointments->patchEntity($appointment, $data);
            
            if ($this->Appointments->save($appointment)) {
                // Find an admin user for syncing with Google Calendar
                $adminUser = $this->Users->find()
                    ->where(['user_type' => 'admin', 'is_verified' => true])
                    ->order(['last_login' => 'DESC'])
                    ->first();

                if (!empty($adminUser)) {
                    // Check if admin has Google Calendar configured
                    $adminHasCalendarConfig = false;
                    if (isset($this->GoogleCalendarSettings)) {
                        $adminCalendarSettings = $this->GoogleCalendarSettings->find()
                            ->where(['user_id' => $adminUser->user_id, 'is_active' => true])
                            ->first();
                        $adminHasCalendarConfig = !empty($adminCalendarSettings);
                    }
                    
                    if ($adminHasCalendarConfig) {
                        // Try to sync with Google Calendar, but continue if it fails
                        try {
                            $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminUser->user_id);

                            if ($eventId) {
                                // Update appointment with Google Calendar event ID
                                $appointment->google_calendar_event_id = $eventId;
                                $appointment->is_google_synced = true;
                                $this->Appointments->save($appointment);
                            } else {
                                // Create a generic Meet link if Google sync failed
                                $meetLink = "https://meet.google.com/lookup/" . substr(md5($appointment->appointment_id), 0, 10);
                                $appointment->meeting_link = $meetLink;
                                $this->Appointments->save($appointment);
                            }
                        } catch (\Exception $e) {
                            // Log error but continue with fallback
                        }
                    }

                    // Send confirmation emails
                    try {
                        // Get fresh appointment data with related entities
                        try {
                            // Check if the WritingServiceRequests association exists
                            $containList = ['Users'];
                            
                            $columns = $this->Appointments->getSchema()->columns();
                            if (in_array('writing_service_request_id', $columns)) {
                                $containList[] = 'WritingServiceRequests';
                            }
                            
                            $appointment = $this->Appointments->get($appointment->appointment_id, [
                                'contain' => $containList,
                            ]);
                        } catch (\Exception $e1) {
                            // Fallback to just getting the appointment without relationships
                            $appointment = $this->Appointments->get($appointment->appointment_id);
                        }

                        // Send confirmation to customer
                        try {
                            $mailer = new AppointmentMailer('default');
                            $mailer->appointmentConfirmation($appointment);
                            $mailer->deliver();
                        } catch (\Exception $e1) {
                            // Continue even if customer email fails
                        }

                        // Send notification to admin
                        try {
                            $mailer = new AppointmentMailer('default');
                            $adminEmail = 'diana@dianabonvini.com';
                            $adminName = 'Diana Bonvini';
                            $mailer->adminNotification($appointment, $adminEmail, $adminName);
                            $mailer->deliver();
                        } catch (\Exception $e2) {
                            // Continue even if admin email fails
                        }
                    } catch (\Exception $e) {
                        // Continue even if email process fails
                    }
                }

                $this->Flash->success(__('Your appointment has been booked successfully. A confirmation email has been sent to your email address.'));

                // If related to a writing service request, redirect to that request
                if (!empty($appointment->writing_service_request_id)) {
                    return $this->redirect([
                        'controller' => 'WritingServiceRequests',
                        'action' => 'view',
                        $appointment->writing_service_request_id
                    ]);
                }

                return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
            } else {
                $this->Flash->error(__('The appointment could not be saved. Please, try again.'));
            }
        }
        
        $appointmentTypes = [
            'initial_consultation' => 'Initial Consultation',
            'follow_up' => 'Follow-up Meeting',
            'project_review' => 'Project Review',
        ];
        
        // Get user's writing service requests for dropdown
        $writingServiceRequests = $this->WritingServiceRequests->find('list', [
            'keyField' => 'writing_service_request_id',
            'valueField' => function ($request) {
                return $request->writing_service_request_id . ' - ' . $request->service_title;
            },
        ])
            ->where([
                'user_id' => $user->user_id,
                'request_status IN' => ['pending', 'in_progress'],
                'is_deleted' => false,
            ])
            ->toArray();
        
        $this->set(compact('appointment', 'appointmentTypes', 'writingServiceRequests', 'writingServiceRequest'));
    }
    
    /**
     * View customer's booked appointments
     *
     * @return void
     */
    public function myAppointments()
    {
        // Redirect to login if not authenticated
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            $this->Flash->info(__('Please login to view your appointments.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        // Get upcoming appointments
        $upcomingAppointments = $this->Appointments->find()
            ->contain(['WritingServiceRequests'])
            ->where([
                'Appointments.user_id' => $user->user_id,
                'Appointments.appointment_date >=' => date('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->order(['Appointments.appointment_date' => 'ASC', 'Appointments.appointment_time' => 'ASC'])
            ->all();
        
        // Get past appointments
        $pastAppointments = $this->Appointments->find()
            ->contain(['WritingServiceRequests'])
            ->where([
                'Appointments.user_id' => $user->user_id,
                'Appointments.appointment_date <' => date('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->order(['Appointments.appointment_date' => 'DESC', 'Appointments.appointment_time' => 'DESC'])
            ->all();
        
        $this->set(compact('upcomingAppointments', 'pastAppointments'));
    }
    
    /**
     * Cancel an appointment
     *
     * @param string|null $id Appointment id.
     * @return \Cake\Http\Response|null
     */
    public function cancel(?string $id = null)
    {
        // Redirect to login if not authenticated
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            $this->Flash->info(__('Please login to cancel an appointment.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        $this->request->allowMethod(['post', 'delete']);
        
        $appointment = $this->Appointments->get($id);
        
        // Ensure the appointment belongs to the current user
        if ($appointment->user_id !== $user->user_id) {
            $this->Flash->error(__('You can only cancel your own appointments.'));
            return $this->redirect(['action' => 'myAppointments']);
        }
        
        // Only allow canceling pending or confirmed appointments
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            $this->Flash->error(__('This appointment cannot be canceled.'));
            return $this->redirect(['action' => 'myAppointments']);
        }
        
        // Calculate if the appointment is within 24 hours
        $appointmentDateTime = new DateTime(
            $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'),
            new DateTimeZone(date_default_timezone_get())
        );
        
        $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
        $diff = $appointmentDateTime->getTimestamp() - $now->getTimestamp();
        $hoursUntilAppointment = $diff / 3600;
        
        // Don't allow canceling within 24 hours
        if ($hoursUntilAppointment < 24) {
            $this->Flash->error(__('Appointments can only be canceled more than 24 hours in advance. Please contact us directly.'));
            return $this->redirect(['action' => 'myAppointments']);
        }
        
        // Update appointment status to canceled
        $appointment->status = 'cancelled';

        if ($this->Appointments->save($appointment)) {
            // Find an admin user for syncing with Google Calendar
            $adminUser = $this->Users->find()
                ->where(['user_type' => 'admin', 'is_verified' => true])
                ->order(['last_login' => 'DESC'])
                ->first();

            if (!empty($adminUser) && $appointment->is_google_synced && !empty($appointment->google_calendar_event_id)) {
                // Delete event from Google Calendar
                $this->googleCalendarService->deleteEvent($appointment->google_calendar_event_id, $adminUser->user_id);
            }

            // Send cancellation emails
            try {
                // Get fresh appointment data with related entities
                $appointment = $this->Appointments->get($appointment->appointment_id, [
                    'contain' => ['Users', 'WritingServiceRequests'],
                ]);

                // Send cancellation to customer
                try {
                    $mailer = new AppointmentMailer('default');
                    $mailer->appointmentCancellation($appointment);
                    $mailer->deliver();
                } catch (\Exception $e1) {
                    // Continue even if customer email fails
                }

                // Send notification to admin
                if (!empty($adminUser)) {
                    try {
                        $mailer = new AppointmentMailer('default');
                        $adminEmail = 'diana@dianabonvini.com';
                        $adminName = 'Diana Bonvini';
                        $mailer->adminNotification($appointment, $adminEmail, $adminName);
                        $mailer->deliver();
                    } catch (\Exception $e2) {
                        // Continue even if admin email fails
                    }
                }
            } catch (\Exception $e) {
                // Continue even if email process fails
            }

            $this->Flash->success(__('The appointment has been canceled. A confirmation email has been sent to your email address.'));
        } else {
            $this->Flash->error(__('The appointment could not be canceled. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'myAppointments']);
    }
    
    /**
     * Build calendar data structure for month view
     *
     * @param int $month Month number (1-12)
     * @param int $year Year number
     * @param \DateTime $today Today's date
     * @param string $adminUserId Admin user ID for checking availability
     * @return array Calendar data structure
     */
    protected function _buildCalendarData(int $month, int $year, DateTime $today, string $adminUserId): array
    {
        // Set up the calendar dates
        $firstDayOfMonth = new DateTime("$year-$month-01", new DateTimeZone(date_default_timezone_get()));
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        
        // Get day of week for first day (0 = Sunday, 6 = Saturday)
        $firstDayWeekday = (int)$firstDayOfMonth->format('w');
        
        // Adjust for week starting on Monday (0 = Monday, 6 = Sunday)
        $firstDayWeekday = ($firstDayWeekday === 0) ? 6 : $firstDayWeekday - 1;
        
        // Start from the Monday before the first day of the month
        $calendarStart = clone $firstDayOfMonth;
        $calendarStart->modify("-{$firstDayWeekday} days");
        
        // Calculate the last day to show (to complete the grid)
        $lastDayWeekday = (int)$lastDayOfMonth->format('w');
        $lastDayWeekday = ($lastDayWeekday === 0) ? 6 : $lastDayWeekday - 1;
        $daysToAdd = 6 - $lastDayWeekday;
        
        $calendarEnd = clone $lastDayOfMonth;
        $calendarEnd->modify("+{$daysToAdd} days");
        
        // Build the calendar data
        $calendarData = [];
        $currentDate = clone $calendarStart;
        
        while ($currentDate <= $calendarEnd) {
            $dateString = $currentDate->format('Y-m-d');
            $isCurrentMonth = $currentDate->format('n') == $month;
            $isToday = $dateString === $today->format('Y-m-d');
            $isPast = $currentDate < $today;
            
            // Check if day is in the future and within available booking window (e.g., next 30 days)
            $bookingWindowEnd = clone $today;
            $bookingWindowEnd->modify('+30 days');
            $isWithinBookingWindow = $currentDate >= $today && $currentDate <= $bookingWindowEnd;
            
            // For days within booking window, check availability
            $hasAvailability = false;
            if ($isWithinBookingWindow) {
                // Define working hours (9 AM to 5 PM by default)
                $workingHours = [
                    'start' => '09:00',
                    'end' => '17:00',
                ];
                
                // Get free time slots
                $freeSlots = $this->googleCalendarService->getFreeTimeSlots($adminUserId, clone $currentDate, $workingHours);
                $hasAvailability = !empty($freeSlots);
            }
            
            $calendarData[] = [
                'date' => clone $currentDate,
                'day' => $currentDate->format('j'),
                'is_current_month' => $isCurrentMonth,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'is_within_booking_window' => $isWithinBookingWindow,
                'has_availability' => $hasAvailability,
            ];
            
            $currentDate->modify('+1 day');
        }
        
        return $calendarData;
    }
}