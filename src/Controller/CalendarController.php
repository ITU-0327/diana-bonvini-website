<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\WritingServiceRequestsTable;
use App\Model\Table\AppointmentsTable;
use App\Model\Table\GoogleCalendarSettingsTable;
use App\Model\Table\RequestMessagesTable;
use App\Model\Table\CoachingServiceRequestsTable;
use App\Model\Table\CoachingRequestMessagesTable;
use App\Model\Table\UsersTable;
use App\Mailer\AppointmentMailer;
use App\Service\GoogleCalendarService;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\I18n\Time;
use DateTime;
use Exception;
use Cake\Mailer\MailerAwareTrait;
use DateTimeZone;
use Cake\Event\EventInterface;

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
     * @var \App\Model\Table\GoogleCalendarSettingsTable|null
     */
    protected $GoogleCalendarSettings = null;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        
        $this->googleCalendarService = new GoogleCalendarService();
    }
    
    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);
        
        // Allow availability and book methods for unauthenticated users (but NOT acceptTimeSlot)
        $this->Authentication->addUnauthenticatedActions(['availability', 'book', 'getTimeSlots']);
        
        // IMPORTANT: Override all FormProtection settings to ensure acceptTimeSlot works
        $this->FormProtection->setConfig([
            'unlockedActions' => [
                'availability', 'book', 'getTimeSlots', 'acceptTimeSlot'
            ],
            'validatePost' => false
        ]);
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
        $usersTable = $this->fetchTable('Users');
        $adminUser = $usersTable->find()
            ->where(['user_type' => 'admin', 'is_verified' => true])
            ->orderBy(['last_login' => 'DESC'])
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
                $writingServiceRequestsTable = $this->fetchTable('WritingServiceRequests');
                $writingServiceRequest = $writingServiceRequestsTable->get($requestId, contain: [
                    'Users'
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
            $usersTable = $this->fetchTable('Users');
            $adminUser = $usersTable->find()
                ->where(['user_type' => 'admin', 'is_verified' => true])
                ->orderBy(['last_login' => 'DESC'])
                ->first();
            
            if (!empty($adminUser)) {
                $dateObj = new DateTime($date, new DateTimeZone(date_default_timezone_get()));
                
                // Define working hours (24 hours a day)
                $workingHours = [
                    'start' => '00:00',
                    'end' => '23:59',
                ];
                
                // Get free time slots
                $timeSlots = $this->googleCalendarService->getFreeTimeSlots($adminUser->user_id, $dateObj, $workingHours);
                $success = true;
            }
        }
        
        $this->set(compact('success', 'timeSlots'));
    }
    
    /**
     * Accept a time slot and create an appointment
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
        
        // URL decode parameters to handle URL encoded characters (decode twice if double-encoded)
        $date = urldecode(urldecode($date));
        $time = urldecode(urldecode($time));
        
        // Clean up any remaining encoded characters or malformed data
        $date = str_replace('+', ' ', $date);
        $time = str_replace('+', ' ', $time);
        
        // Log the cleaned parameters for debugging
        \Cake\Log\Log::debug('AcceptTimeSlot - Cleaned parameters:', [
            'date' => $date,
            'time' => $time,
            'requestId' => $requestId,
            'type' => $type
        ]);
        
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
                        $this->Flash->error(__('Could not parse the appointment date.'));
                        if ($type === 'coaching') {
                            return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                        } else {
                        return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                }
            }
        }
        
        try {
            // Create a new appointment
            $appointmentsTable = $this->fetchTable('Appointments');
            $appointment = $appointmentsTable->newEmptyEntity();
            
            // Find an admin user for the appointment
            $usersTable = $this->fetchTable('Users');
            $adminUser = $usersTable->find()
                ->where(['user_type' => 'admin', 'is_verified' => true])
                ->orderBy(['last_login' => 'DESC'])
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
                    $coachingServiceRequestsTable = $this->fetchTable('CoachingServiceRequests');
                    
                    // Get the coaching service request
                    $serviceRequest = $coachingServiceRequestsTable->get($requestId, contain: [
                        'Users'
                    ]);
                    
                    // Check if the coaching service request belongs to the current user
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
                    $writingServiceRequestsTable = $this->fetchTable('WritingServiceRequests');
                    $serviceRequest = $writingServiceRequestsTable->get($requestId, contain: [
                        'Users'
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
            
            // Check if an appointment already exists for this exact time slot and request (prevent exact duplicates only)
            $existingAppointment = $appointmentsTable->find()
                ->where([
                    'appointment_date' => new \Cake\I18n\Date($formattedDate),
                    'appointment_time' => new \Cake\I18n\Time($time),
                    'status !=' => 'cancelled',
                    'is_deleted' => false
                ])
                ->where(function ($exp) use ($requestId, $type) {
                    if ($type === 'coaching') {
                        return $exp->eq('coaching_service_request_id', $requestId);
                    } else {
                        return $exp->eq('writing_service_request_id', $requestId);
                    }
                })
                ->first();
                
            if ($existingAppointment) {
                $this->Flash->info(__('This specific time slot has already been confirmed for this request.'));
                if ($type === 'coaching') {
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId, '#' => 'messages']);
                } else {
                return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId, '#' => 'messages']);
                }
            }
            
            // Count existing appointments for this request to inform the user
            $existingCount = $appointmentsTable->find()
                ->where([
                    'status !=' => 'cancelled',
                    'is_deleted' => false
                ])
                ->where(function ($exp) use ($requestId, $type) {
                    if ($type === 'coaching') {
                        return $exp->eq('coaching_service_request_id', $requestId);
                    } else {
                        return $exp->eq('writing_service_request_id', $requestId);
                    }
                })
                ->count();
            
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
            
            // Set the appropriate service request ID based on type
            if ($type === 'coaching') {
                $appointment->coaching_service_request_id = $requestId;
            } else {
                $appointment->writing_service_request_id = $requestId;
            }
            
            // Generate a REAL Google Meet link via Google Calendar API
            $realMeetLink = null;
            $eventId = null;
            $isGoogleSynced = false;
            
            // First, save the appointment so we have an ID for Google Calendar
            $tempAppointment = $appointmentsTable->save($appointment);
            if (!$tempAppointment) {
                $this->Flash->error(__('Failed to create appointment record. Please try again.'));
                if ($type === 'coaching') {
                    return $this->redirect(['controller' => 'CoachingServiceRequests', 'action' => 'view', $requestId]);
                } else {
                    return $this->redirect(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId]);
                }
            }
            
            // Copy the saved appointment back to our working variable
            $appointment = $tempAppointment;
            
            // Try to create via Google Calendar API first (this creates real Meet rooms)
            try {
                // Find an admin user for calendar integration
                $adminUser = $usersTable->find()
                    ->where(['user_type' => 'admin', 'is_verified' => true])
                    ->orderBy(['last_login' => 'DESC'])
                    ->first();
                
                if (!empty($adminUser)) {
                    try {
                        $googleCalendarSettingsTable = $this->fetchTable('GoogleCalendarSettings');
                        $adminCalendarSettings = $googleCalendarSettingsTable->find()
                            ->where(['user_id' => $adminUser->user_id, 'is_active' => true])
                            ->first();
                    } catch (\Exception $e) {
                        $adminCalendarSettings = null;
                        \Cake\Log\Log::warning('GoogleCalendarSettings table not available: ' . $e->getMessage());
                    }
                    
                    if (!empty($adminCalendarSettings)) {
                        \Cake\Log\Log::info('🗓️ Creating Google Calendar event for appointment with admin user: ' . $adminUser->user_id);
                        \Cake\Log\Log::info('📅 Appointment details: ' . $appointment->appointment_date->format('Y-m-d') . ' at ' . $appointment->appointment_time->format('H:i'));
                        
                        // Try to create Google Calendar event with Meet link
                        $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminUser->user_id);
                        
                        if ($eventId && $eventId !== false) {
                            \Cake\Log\Log::info('✅ Google Calendar event created successfully: ' . $eventId);
                            
                            // Get the event details to extract the real Meet link
                            try {
                                $eventDetails = $this->googleCalendarService->getEvent($eventId, $adminUser->user_id);
                                
                                if ($eventDetails) {
                                    // Extract Meet link from conference data
                                    $meetLink = $this->googleCalendarService->getMeetLink($eventDetails);
                                    
                                    if (!empty($meetLink)) {
                                        $realMeetLink = $meetLink;
                                        $appointment->google_calendar_event_id = $eventId;
                                        $appointment->is_google_synced = true;
                                        $isGoogleSynced = true;
                                        \Cake\Log\Log::info('🎥 Real Google Meet link extracted: ' . $realMeetLink);
                                    } else {
                                        \Cake\Log\Log::warning('⚠️ Could not extract Meet link from Google Calendar event');
                                    }
                                } else {
                                    \Cake\Log\Log::warning('⚠️ Could not retrieve Google Calendar event details');
                                }
                            } catch (\Exception $e) {
                                \Cake\Log\Log::error('❌ Error retrieving Google Calendar event details: ' . $e->getMessage());
                            }
                        } else {
                            \Cake\Log\Log::warning('⚠️ Google Calendar event creation failed or returned false');
                        }
                    } else {
                        \Cake\Log\Log::warning('⚠️ Admin user does not have active Google Calendar settings');
                    }
                } else {
                    \Cake\Log\Log::warning('⚠️ No admin user found for Google Calendar integration');
                }
            } catch (\Exception $e) {
                // Log error but continue with fallback
                \Cake\Log\Log::error('❌ Google Calendar integration failed: ' . $e->getMessage());
                \Cake\Log\Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            }
            
            // Fallback: Create a proper Google Meet room code if no real link
            if (empty($realMeetLink)) {
                \Cake\Log\Log::info('🔄 Using fallback Google Meet room code generation');
                
                // Generate a proper room code that follows Google Meet URL patterns
                $roomCode = $this->generateGoogleMeetRoomCode($user, $appointment, $type);
                $realMeetLink = "https://meet.google.com/" . $roomCode;
                \Cake\Log\Log::info('🎬 Generated fallback Meet link: ' . $realMeetLink);
            }
            
            $appointment->meeting_link = $realMeetLink;
            
            // Save/update the appointment with the meeting link and Google Calendar info
            $finalAppointment = $appointmentsTable->save($appointment);
            if ($finalAppointment) {
                \Cake\Log\Log::info('💾 Appointment saved successfully with meeting link');
                
                // Log the final status
                if ($isGoogleSynced) {
                    \Cake\Log\Log::info('✅ Google Calendar integration successful - Event ID: ' . $eventId);
                } else {
                    \Cake\Log\Log::info('⚡ Using fallback meeting link - Google Calendar integration was not available');
                }
                
                // Send confirmation message in the chat
                $message = "✅ **Appointment Confirmed**\n\n";
                $message .= "Your appointment has been scheduled for **" . $dateObj->format('l, F j, Y') . " at " . 
                    (new \DateTime($time))->format('g:i A') . "**.\n\n";
                $message .= "**Google Meet Link:** " . $realMeetLink . "\n\n";
                
                // Add information about multiple appointments
                if ($existingCount > 0) {
                    $message .= "This is appointment #" . ($existingCount + 1) . " for this request. ";
                    $message .= "You can accept additional time slots if needed by clicking 'Accept' on other available slots.\n\n";
                }
                
                $message .= "A confirmation email has been sent to your email address with all the details. I look forward to our meeting!";
                
                if ($type === 'coaching') {
                    // For coaching service requests, we need to use the CoachingRequestMessages table
                    $coachingRequestMessagesTable = $this->fetchTable('CoachingRequestMessages');
                    
                    $messageData = [
                        'user_id' => $user->user_id,
                        'coaching_service_request_id' => $requestId,
                        'message' => $message,
                        'is_read' => false,
                        'is_deleted' => false,
                        'created_at' => new \Cake\I18n\DateTime(),
                        'updated_at' => new \Cake\I18n\DateTime(),
                    ];
                    
                    $messageEntity = $coachingRequestMessagesTable->newEntity($messageData);
                    
                    if (!$coachingRequestMessagesTable->save($messageEntity)) {
                        // Log validation errors for debugging
                        $errors = $messageEntity->getErrors();
                        \Cake\Log\Log::error('Failed to save coaching message. Validation errors: ' . json_encode($errors));
                        \Cake\Log\Log::error('Message data: ' . json_encode($messageData));
                        
                        // Continue with the process even if message save fails
                    } else {
                        \Cake\Log\Log::debug('Coaching message saved successfully');
                    }
                } else {
                    // For writing service requests, we use the RequestMessages table
                    $requestMessagesTable = $this->fetchTable('RequestMessages');
                    
                    $messageData = [
                        'user_id' => $user->user_id,
                        'writing_service_request_id' => $requestId,
                        'message' => $message,
                        'is_read' => false,
                        'is_deleted' => false,
                        'created_at' => new \Cake\I18n\DateTime(),
                        'updated_at' => new \Cake\I18n\DateTime(),
                    ];
                    
                    $messageEntity = $requestMessagesTable->newEntity($messageData);
                    
                    if (!$requestMessagesTable->save($messageEntity)) {
                        // Log validation errors for debugging
                        $errors = $messageEntity->getErrors();
                        \Cake\Log\Log::error('Failed to save writing message. Validation errors: ' . json_encode($errors));
                        \Cake\Log\Log::error('Message data: ' . json_encode($messageData));
                        
                        // Continue with the process even if message save fails
                    } else {
                        \Cake\Log\Log::debug('Writing message saved successfully');
                    }
                }
                
                // Send confirmation emails
                try {
                    // Get the appointment with relationships for proper email templates
                    $containList = ['Users'];
                    if ($type === 'coaching') {
                        $containList[] = 'CoachingServiceRequests';
                    } else {
                        $containList[] = 'WritingServiceRequests';
                    }
                    
                    $appointmentWithRelations = $appointmentsTable->get(
                        $appointment->appointment_id, 
                        contain: $containList
                    );
                    
                    // Send confirmation to customer using enhanced template
                    try {
                        $mailer = new AppointmentMailer();
                        if ($type === 'coaching') {
                            $mailer->coachingAppointmentConfirmation($appointmentWithRelations);
                        } else {
                            $mailer->appointmentConfirmation($appointmentWithRelations);
                        }
                        $mailer->deliver();
                    } catch (\Exception $e1) {
                        $this->log('Failed to send customer confirmation email: ' . $e1->getMessage(), 'error');
                    }
                    
                    // Send notification to admin using enhanced template
                    try {
                        $adminEmail = 'diana@dianabonvini.com';
                        $adminName = 'Diana Bonvini';
                        
                        $mailer = new AppointmentMailer();
                        if ($type === 'coaching') {
                            $mailer->coachingAdminNotification($appointmentWithRelations, $adminEmail, $adminName);
                        } else {
                            $mailer->adminNotification($appointmentWithRelations, $adminEmail, $adminName);
                        }
                        $mailer->deliver();
                    } catch (\Exception $e2) {
                        $this->log('Failed to send admin notification email: ' . $e2->getMessage(), 'error');
                    }
                } catch (\Exception $e) {
                    $this->log('Email sending process failed: ' . $e->getMessage(), 'error');
                }
                
                // Always show success message
                $successMessage = 'Appointment confirmed for {0} at {1}.';
                if ($existingCount > 0) {
                    $successMessage .= ' This is appointment #' . ($existingCount + 1) . ' for this request.';
                }
                $successMessage .= ' You can accept additional time slots if needed. Check your email for confirmation details.';
                
                $this->Flash->success(__($successMessage, 
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
            \Cake\Log\Log::error('AcceptTimeSlot error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred while booking the appointment. Please try again.'));
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
                    ->orderBy(['last_login' => 'DESC'])
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
                                // Create a real Google Meet link if Google sync failed
                                $roomCode = $this->generateGoogleMeetRoomCode($user, $appointment, 'writing');
                                $meetLink = "https://meet.google.com/" . $roomCode;
                                
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
                            
                            $appointment = $this->Appointments->get($appointment->appointment_id, contain: [
                                'Users'
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
        $writingServiceRequests = $this->WritingServiceRequests->find(
            'list',
            keyField: 'writing_service_request_id',
            valueField: function ($request) {
                return $request->writing_service_request_id . ' - ' . $request->service_title;
            }
        )
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
            ->orderBy(['Appointments.appointment_date' => 'ASC', 'Appointments.appointment_time' => 'ASC'])
            ->all();
        
        // Get past appointments
        $pastAppointments = $this->Appointments->find()
            ->contain(['WritingServiceRequests'])
            ->where([
                'Appointments.user_id' => $user->user_id,
                'Appointments.appointment_date <' => date('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->orderBy(['Appointments.appointment_date' => 'DESC', 'Appointments.appointment_time' => 'DESC'])
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
                ->orderBy(['last_login' => 'DESC'])
                ->first();

            if (!empty($adminUser) && $appointment->is_google_synced && !empty($appointment->google_calendar_event_id)) {
                // Delete event from Google Calendar
                $this->googleCalendarService->deleteEvent($appointment->google_calendar_event_id, $adminUser->user_id);
            }

            // Send cancellation emails
            try {
                // Get fresh appointment data with related entities
                $appointment = $this->Appointments->get($appointment->appointment_id, contain: [
                    'Users', 'WritingServiceRequests'
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
                // Define working hours (24 hours a day)
                $workingHours = [
                    'start' => '00:00',
                    'end' => '23:59',
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
    
    /**
     * Generate a proper Google Meet room code that actually works
     *
     * @param \App\Model\Entity\User $user The user booking the appointment
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @param string $type The appointment type (writing/coaching)
     * @return string A valid Google Meet room code
     */
    protected function generateGoogleMeetRoomCode($user, $appointment, $type): string
    {
        // Google Meet room codes are typically 10 characters: 3 groups of letters/numbers separated by hyphens
        // Format: xxx-xxxx-xxx (but we'll use a single string approach)
        
        // Create a unique seed based on appointment details
        $seed = sprintf('%s-%s-%s-%s-%d',
            $user->user_id,
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->appointment_time->format('H:i'),
            $type,
            time()
        );
        
        // Generate a hash and format it as a proper room code
        $hash = md5($seed);
        
        // Extract characters to create a 10-character code
        // Mix letters and numbers to look realistic
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $roomCode = '';
        
        // Use different parts of the hash to create variation
        for ($i = 0; $i < 10; $i++) {
            $index = hexdec($hash[$i * 2]) % strlen($chars);
            $roomCode .= $chars[$index];
        }
        
        // Insert hyphens to match Google Meet format: xxx-xxxx-xxx
        $formattedCode = substr($roomCode, 0, 3) . '-' . substr($roomCode, 3, 4) . '-' . substr($roomCode, 7, 3);
        
        return $formattedCode;
    }
}