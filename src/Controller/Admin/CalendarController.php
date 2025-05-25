<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\GoogleCalendarService;
use Cake\Event\EventInterface;
use Cake\I18n\Date;
use Cake\I18n\Time;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Calendar Controller
 *
 * Admin interface for calendar management and appointment scheduling.
 *
 * @property \App\Model\Table\AppointmentsTable $Appointments
 * @property \App\Model\Table\WritingServiceRequestsTable $WritingServiceRequests
 * @property \App\Model\Table\GoogleCalendarSettingsTable $GoogleCalendarSettings
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

        $this->Appointments = $this->fetchTable('Appointments');
        $this->WritingServiceRequests = $this->fetchTable('WritingServiceRequests');
        $this->GoogleCalendarSettings = $this->fetchTable('GoogleCalendarSettings');
        $this->googleCalendarService = new GoogleCalendarService();

        // Use admin layout
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * BeforeFilter callback.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Ensure user is authenticated and is an admin
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You must be logged in as an administrator to access this area.'));
            $event->setResult($this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]));
        }
    }

    /**
     * Index page - shows calendar view
     *
     * @return void
     */
    public function index()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $month = (int)$this->request->getQuery('month', date('n'));
        $year = (int)$this->request->getQuery('year', date('Y'));

        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }

        if ($year < date('Y') || $year > date('Y') + 2) {
            $year = (int)date('Y');
        }

        // Check if Google Calendar is connected
        $settings = $this->GoogleCalendarSettings->find()
            ->where(['user_id' => $user->user_id, 'is_active' => true])
            ->first();

        $isConnected = !empty($settings);
        $authUrl = $this->googleCalendarService->getAuthUrl();

        if (!$isConnected) {
            $this->Flash->warning(__('Your Google Calendar is not connected. Appointment bookings may not show correctly in your calendar. Please connect your Google Calendar.'));
        }

        // Get current month/year or from query parameters
        $today = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
        $startOfWeek = clone $today;
        $startOfWeek->modify('monday this week');
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('+6 days');

        // Month start/end dates
        $startOfMonth = new DateTime("$year-$month-01", new DateTimeZone(date_default_timezone_get()));
        $endOfMonth = clone $startOfMonth;
        $endOfMonth->modify('last day of this month');

        // Get appointments for this month
        $appointments = $this->Appointments->find()
            ->contain(['Users', 'WritingServiceRequests'])
            ->where([
                'appointment_date >=' => $startOfMonth->format('Y-m-d'),
                'appointment_date <=' => $endOfMonth->format('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->order(['Appointments.appointment_date' => 'ASC', 'Appointments.appointment_time' => 'ASC'])
            ->all();

        // Create calendar data structure
        $calendarData = $this->_buildCalendarData($month, $year, $appointments);

        // Get upcoming appointments for the sidebar
        $upcomingAppointments = $this->Appointments->find()
            ->contain(['Users', 'WritingServiceRequests'])
            ->where([
                'appointment_date >=' => $today->format('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->order(['Appointments.appointment_date' => 'ASC', 'Appointments.appointment_time' => 'ASC'])
            ->limit(5)
            ->all();

        // Get requests without appointments for quick scheduling
        $unscheduledRequests = $this->WritingServiceRequests->find()
            ->contain(['Users'])
            ->where([
                'request_status IN' => ['pending', 'in_progress'],
                'is_deleted' => false,
            ])
            ->order(['created_at' => 'DESC'])
            ->limit(5)
            ->all();

        $this->set(compact(
            'isConnected',
            'authUrl',
            'month',
            'year',
            'calendarData',
            'today',
            'startOfWeek',
            'endOfWeek',
            'upcomingAppointments',
            'unscheduledRequests'
        ));
    }

    /**
     * View appointments for a specific day
     *
     * @param string|null $date Date in Y-m-d format
     * @return void
     */
    public function day(?string $date = null)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        if (empty($date) || !strtotime($date)) {
            $date = date('Y-m-d');
        }

        $dateObj = new DateTime($date, new DateTimeZone(date_default_timezone_get()));

        // Get appointments for this day
        $appointments = $this->Appointments->find()
            ->contain(['Users', 'WritingServiceRequests'])
            ->where([
                'appointment_date' => $dateObj->format('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->order(['Appointments.appointment_time' => 'ASC'])
            ->all();

        // Check if Google Calendar is connected
        $settings = $this->GoogleCalendarSettings->find()
            ->where(['user_id' => $user->user_id, 'is_active' => true])
            ->first();

        $isConnected = !empty($settings);

        // If connected, get Google Calendar events for this day
        $googleEvents = [];
        if ($isConnected) {
            $startOfDay = clone $dateObj;
            $startOfDay->setTime(0, 0, 0);

            $endOfDay = clone $dateObj;
            $endOfDay->setTime(23, 59, 59);

            $googleEvents = $this->googleCalendarService->getCalendarEvents($user->user_id, $startOfDay, $endOfDay) ?: [];
        }

        // Get writing service requests for appointment creation
        $writingServiceRequests = $this->WritingServiceRequests->find(
            'list',
            keyField: 'writing_service_request_id',
            valueField: function ($request) {
                return $request->writing_service_request_id . ' - ' . $request->service_title;
            }
        )
            ->where([
                'request_status IN' => ['pending', 'in_progress'],
                'is_deleted' => false,
            ])
            ->toArray();

        $this->set(compact('date', 'dateObj', 'appointments', 'isConnected', 'googleEvents', 'writingServiceRequests'));
    }

    /**
     * Week view showing appointments for the entire week
     *
     * @return void
     */
    public function week()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        // Get week start date from query or default to current week
        $startDate = $this->request->getQuery('start');

        if (empty($startDate) || !strtotime($startDate)) {
            $today = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
            $startDate = $today->modify('monday this week')->format('Y-m-d');
        }

        $startDateObj = new DateTime($startDate, new DateTimeZone(date_default_timezone_get()));
        $endDateObj = clone $startDateObj;
        $endDateObj->modify('+6 days');

        // Get appointments for this week
        $appointments = $this->Appointments->find()
            ->contain(['Users', 'WritingServiceRequests'])
            ->where([
                'appointment_date >=' => $startDateObj->format('Y-m-d'),
                'appointment_date <=' => $endDateObj->format('Y-m-d'),
                'Appointments.is_deleted' => false,
            ])
            ->order(['Appointments.appointment_date' => 'ASC', 'Appointments.appointment_time' => 'ASC'])
            ->all();

        // Organize appointments by day
        $appointmentsByDay = [];
        foreach ($appointments as $appointment) {
            $day = $appointment->appointment_date->format('Y-m-d');
            if (!isset($appointmentsByDay[$day])) {
                $appointmentsByDay[$day] = [];
            }
            $appointmentsByDay[$day][] = $appointment;
        }

        // Create week days array
        $weekDays = [];
        $currentDate = clone $startDateObj;
        for ($i = 0; $i < 7; $i++) {
            $dateKey = $currentDate->format('Y-m-d');
            $weekDays[$dateKey] = [
                'date' => clone $currentDate,
                'appointments' => $appointmentsByDay[$dateKey] ?? [],
            ];
            $currentDate->modify('+1 day');
        }

        // Navigation links
        $prevWeek = clone $startDateObj;
        $prevWeek->modify('-7 days');
        $nextWeek = clone $startDateObj;
        $nextWeek->modify('+7 days');

        $this->set(compact('startDateObj', 'endDateObj', 'weekDays', 'prevWeek', 'nextWeek'));
    }

    /**
     * Add appointment
     *
     * @return \Cake\Http\Response|null Redirects on successful add
     */
    public function add()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $appointment = $this->Appointments->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Set default values
            $data['is_deleted'] = false;
            $data['is_google_synced'] = false;

            // Convert date and time formats
            if (!empty($data['appointment_date'])) {
                $data['appointment_date'] = new Date($data['appointment_date']);
            }
            if (!empty($data['appointment_time'])) {
                $data['appointment_time'] = new Time($data['appointment_time']);
            }

            $appointment = $this->Appointments->patchEntity($appointment, $data);

            if ($this->Appointments->save($appointment)) {
                // Check if Google Calendar is connected and sync event
                $settings = $this->GoogleCalendarSettings->find()
                    ->where(['user_id' => $user->user_id, 'is_active' => true])
                    ->first();

                if ($settings) {
                    $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $user->user_id);

                    if ($eventId) {
                        // Update appointment with Google Calendar event ID
                        $appointment->google_calendar_event_id = $eventId;
                        $appointment->is_google_synced = true;
                        $this->Appointments->save($appointment);

                        $this->Flash->success(__('Appointment created and synced with Google Calendar.'));
                    } else {
                        $this->Flash->warning(__('Appointment created but failed to sync with Google Calendar.'));
                    }
                } else {
                    $this->Flash->success(__('Appointment created.'));
                }

                return $this->redirect(['action' => 'day', $appointment->appointment_date->format('Y-m-d')]);
            } else {
                $this->Flash->error(__('The appointment could not be saved. Please, try again.'));
            }
        }

        // Get date from query parameters
        $date = $this->request->getQuery('date');
        $time = $this->request->getQuery('time');
        $requestId = $this->request->getQuery('request_id');

        if (!empty($date)) {
            $appointment->appointment_date = new Date($date);
        }
        if (!empty($time)) {
            $appointment->appointment_time = new Time($time);
        }
        if (!empty($requestId)) {
            $appointment->writing_service_request_id = $requestId;

            // Get request details to pre-fill form
            $request = $this->WritingServiceRequests->get($requestId,
                contain: ['Users'],
            );
            $appointment->user_id = $request->user_id;
        }

        // Default to 30 minutes duration
        if (empty($appointment->duration)) {
            $appointment->duration = 30;
        }

        // Get writing service requests for dropdown
        $writingServiceRequests = $this->WritingServiceRequests->find(
            'list',
            keyField: 'writing_service_request_id',
            valueField: function ($request) {
                return $request->writing_service_request_id . ' - ' . $request->service_title;
            }
        )
            ->where([
                'request_status IN' => ['pending', 'in_progress'],
                'is_deleted' => false,
            ])
            ->toArray();

        // Get users for dropdown
        $users = $this->Appointments->Users->find(
            'list',
            keyField: 'user_id',
            valueField: function ($user) {
                return $user->first_name . ' ' . $user->last_name;
            }
        )
            ->where(['user_type' => 'customer'])
            ->toArray();

        $appointmentTypes = [
            'initial_consultation' => 'Initial Consultation',
            'follow_up' => 'Follow-up Meeting',
            'project_review' => 'Project Review',
            'delivery' => 'Final Delivery',
        ];

        $statuses = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
        ];

        $this->set(compact('appointment', 'writingServiceRequests', 'users', 'appointmentTypes', 'statuses'));
    }

    /**
     * Edit appointment
     *
     * @param string|null $id Appointment id.
     * @return \Cake\Http\Response|null Redirects on successful edit.
     */
    public function edit(?string $id = null)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $appointment = $this->Appointments->get($id, contain: ['Users', 'WritingServiceRequests'],
        );

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // Convert date and time formats
            if (!empty($data['appointment_date'])) {
                $data['appointment_date'] = new Date($data['appointment_date']);
            }
            if (!empty($data['appointment_time'])) {
                $data['appointment_time'] = new Time($data['appointment_time']);
            }

            $appointment = $this->Appointments->patchEntity($appointment, $data);

            if ($this->Appointments->save($appointment)) {
                // Check if Google Calendar is connected and update event
                $settings = $this->GoogleCalendarSettings->find()
                    ->where(['user_id' => $user->user_id, 'is_active' => true])
                    ->first();

                if ($settings && $appointment->is_google_synced) {
                    $updated = $this->googleCalendarService->updateAppointmentEvent($appointment, $user->user_id);

                    if ($updated) {
                        $this->Flash->success(__('Appointment updated and synced with Google Calendar.'));
                    } else {
                        $this->Flash->warning(__('Appointment updated but failed to sync with Google Calendar.'));

                        // Check if we should create a new event
                        if (empty($appointment->google_calendar_event_id)) {
                            $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $user->user_id);

                            if ($eventId) {
                                $appointment->google_calendar_event_id = $eventId;
                                $appointment->is_google_synced = true;
                                $this->Appointments->save($appointment);

                                $this->Flash->success(__('Created new event in Google Calendar.'));
                            }
                        }
                    }
                } else {
                    $this->Flash->success(__('Appointment updated.'));
                }

                return $this->redirect(['action' => 'day', $appointment->appointment_date->format('Y-m-d')]);
            } else {
                $this->Flash->error(__('The appointment could not be saved. Please, try again.'));
            }
        }

        // Get writing service requests for dropdown
        $writingServiceRequests = $this->WritingServiceRequests->find(
            'list',
            keyField: 'writing_service_request_id',
            valueField: function ($request) {
                return $request->writing_service_request_id . ' - ' . $request->service_title;
            }
        )
            ->where([
                'request_status IN' => ['pending', 'in_progress', 'completed'],
                'is_deleted' => false,
            ])
            ->toArray();

        // Get users for dropdown
        $users = $this->Appointments->Users->find(
            'list',
            keyField: 'user_id',
            valueField: function ($user) {
                return $user->first_name . ' ' . $user->last_name;
            }
        )
            ->where(['user_type' => 'customer'])
            ->toArray();

        $appointmentTypes = [
            'initial_consultation' => 'Initial Consultation',
            'follow_up' => 'Follow-up Meeting',
            'project_review' => 'Project Review',
            'delivery' => 'Final Delivery',
        ];

        $statuses = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
        ];

        $this->set(compact('appointment', 'writingServiceRequests', 'users', 'appointmentTypes', 'statuses'));
    }

    /**
     * Delete appointment
     *
     * @param string|null $id Appointment id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete(?string $id = null)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $this->request->allowMethod(['post', 'delete']);

        $appointment = $this->Appointments->get($id);
        $returnDate = $appointment->appointment_date->format('Y-m-d');

        // Check if Google Calendar is connected and delete event
        if ($appointment->is_google_synced && !empty($appointment->google_calendar_event_id)) {
            $settings = $this->GoogleCalendarSettings->find()
                ->where(['user_id' => $user->user_id, 'is_active' => true])
                ->first();

            if ($settings) {
                $deleted = $this->googleCalendarService->deleteEvent($appointment->google_calendar_event_id, $user->user_id);

                if (!$deleted) {
                    $this->Flash->warning(__('Failed to delete event from Google Calendar.'));
                }
            }
        }

        if ($this->Appointments->delete($appointment)) {
            $this->Flash->success(__('The appointment has been deleted.'));
        } else {
            $this->Flash->error(__('The appointment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'day', $returnDate]);
    }

    /**
     * Get availability time slots
     *
     * @return void
     */
    public function getTimeSlots()
    {
        $this->request->allowMethod(['get', 'ajax']);

        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        // Return JSON response
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', ['success', 'timeSlots']);

        $success = false;
        $timeSlots = [];

        // Get date from query parameter
        $date = $this->request->getQuery('date');

        if (!empty($date) && strtotime($date)) {
            // Check if Google Calendar is connected
            $settings = $this->GoogleCalendarSettings->find()
                ->where(['user_id' => $user->user_id, 'is_active' => true])
                ->first();

            if ($settings) {
                $dateObj = new DateTime($date, new DateTimeZone(date_default_timezone_get()));

                // Define working hours (24 hours a day)
                $workingHours = [
                    'start' => '00:00',
                    'end' => '23:59',
                ];

                // Get free time slots
                $timeSlots = $this->googleCalendarService->getFreeTimeSlots($user->user_id, $dateObj, $workingHours);
                $success = true;
            }
        }

        $this->set(compact('success', 'timeSlots'));
    }

    /**
     * Build calendar data for month view
     *
     * @param int $month Month number (1-12)
     * @param int $year Year number
     * @param \Cake\Collection\Collection $appointments Collection of appointments
     * @return array Calendar data structure
     */
    protected function _buildCalendarData(int $month, int $year, $appointments): array
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

        // Group appointments by date
        $appointmentsByDate = [];
        foreach ($appointments as $appointment) {
            $date = $appointment->appointment_date->format('Y-m-d');
            if (!isset($appointmentsByDate[$date])) {
                $appointmentsByDate[$date] = [];
            }
            $appointmentsByDate[$date][] = $appointment;
        }

        // Build the calendar data
        $calendarData = [];
        $currentDate = clone $calendarStart;

        while ($currentDate <= $calendarEnd) {
            $dateString = $currentDate->format('Y-m-d');
            $isCurrentMonth = $currentDate->format('n') == $month;

            $calendarData[] = [
                'date' => clone $currentDate,
                'day' => $currentDate->format('j'),
                'is_current_month' => $isCurrentMonth,
                'is_today' => $dateString === date('Y-m-d'),
                'appointments' => $appointmentsByDate[$dateString] ?? [],
                'appointment_count' => count($appointmentsByDate[$dateString] ?? []),
            ];

            $currentDate->modify('+1 day');
        }

        return $calendarData;
    }
}
