<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\GoogleCalendarService;
use Cake\I18n\Date;
use Cake\I18n\Time;
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
 */
class CalendarController extends AppController
{
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
        
        $this->loadModel('Appointments');
        $this->loadModel('WritingServiceRequests');
        $this->loadModel('Users');
        $this->googleCalendarService = new GoogleCalendarService();
        
        // Allow availability and book methods for unauthenticated users
        $this->Authentication->addUnauthenticatedActions(['availability', 'book']);
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
                    // Sync with Google Calendar
                    $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminUser->user_id);

                    if ($eventId) {
                        // Update appointment with Google Calendar event ID
                        $appointment->google_calendar_event_id = $eventId;
                        $appointment->is_google_synced = true;
                        $this->Appointments->save($appointment);
                    }

                    // Send confirmation emails
                    try {
                        // Get fresh appointment data with related entities
                        $appointment = $this->Appointments->get($appointment->appointment_id, [
                            'contain' => ['Users', 'WritingServiceRequests'],
                        ]);

                        // Send confirmation to customer
                        $this->getMailer('Appointment')->send('appointmentConfirmation', [$appointment]);

                        // Send notification to admin
                        $this->getMailer('Appointment')->send('adminNotification', [
                            $appointment,
                            $adminUser->email,
                            $adminUser->first_name . ' ' . $adminUser->last_name
                        ]);
                    } catch (\Exception $e) {
                        // Log email sending error but continue
                        $this->log('Failed to send appointment confirmation email: ' . $e->getMessage(), 'error');
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
                $this->getMailer('Appointment')->send('appointmentCancellation', [$appointment]);

                // Send notification to admin
                if (!empty($adminUser)) {
                    $this->getMailer('Appointment')->send('adminNotification', [
                        $appointment,
                        $adminUser->email,
                        $adminUser->first_name . ' ' . $adminUser->last_name
                    ]);
                }
            } catch (\Exception $e) {
                // Log email sending error but continue
                $this->log('Failed to send appointment cancellation email: ' . $e->getMessage(), 'error');
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