<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\GoogleCalendarService;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Appointments Controller for admin area
 *
 * @property \App\Model\Table\AppointmentsTable $Appointments
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\GoogleCalendarSettingsTable $GoogleCalendarSettings
 */
class AppointmentsController extends AppController
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
        $this->loadModel('Users');
        $this->loadModel('GoogleCalendarSettings');
        
        $this->googleCalendarService = new GoogleCalendarService();
        
        // Use admin layout
        $this->viewBuilder()->setLayout('admin');
    }
    
    /**
     * BeforeFilter callback.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Ensure user is authenticated and is an admin
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        
        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You must be logged in as an administrator to access this area.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }
    }
    
    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        // Get upcoming appointments
        $appointments = $this->Appointments->find()
            ->contain(['Users'])
            ->where(['Appointments.is_deleted' => false])
            ->order(['Appointments.appointment_date' => 'DESC', 'Appointments.appointment_time' => 'DESC'])
            ->all();
        
        $this->set(compact('appointments'));
    }
    
    /**
     * View method
     *
     * @param string|null $id Appointment id.
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $appointment = $this->Appointments->get($id, [
            'contain' => ['Users'],
        ]);
        
        $this->set(compact('appointment'));
    }
    
    /**
     * Sync appointment with Google Calendar
     *
     * @param string|null $id Appointment id.
     * @param string|null $adminId Admin user id.
     * @return \Cake\Http\Response|null
     */
    public function syncCalendar($id = null, $adminId = null)
    {
        $this->request->allowMethod(['post', 'get']);
        
        if (!$id) {
            throw new NotFoundException(__('No appointment ID provided'));
        }
        
        try {
            // Get appointment data
            $appointment = $this->Appointments->get($id, [
                'contain' => ['Users'],
            ]);
            
            // If admin ID is not provided, use the logged-in admin
            if (!$adminId) {
                /** @var \App\Model\Entity\User $user */
                $user = $this->Authentication->getIdentity();
                $adminId = $user->user_id;
            }
            
            // Check if admin user exists
            $adminUser = $this->Users->find()
                ->where(['user_id' => $adminId, 'user_type' => 'admin'])
                ->first();
                
            if (!$adminUser) {
                throw new NotFoundException(__('Admin user not found'));
            }
            
            // Check if admin has Google Calendar settings
            $settings = $this->GoogleCalendarSettings->find()
                ->where(['user_id' => $adminId, 'is_active' => true])
                ->first();
            
            if (!$settings) {
                if ($this->request->is('ajax')) {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Admin does not have Google Calendar configured',
                            'details' => 'Please connect your Google Calendar from the settings page.'
                        ]));
                }
                
                $this->Flash->error(__('Please connect your Google Calendar first.'));
                return $this->redirect(['controller' => 'GoogleAuth', 'action' => 'index']);
            }
            
            // Try to sync with Google Calendar
            if ($appointment->is_google_synced && !empty($appointment->google_calendar_event_id)) {
                // Update existing event
                $result = $this->googleCalendarService->updateAppointmentEvent($appointment, $adminId);
                
                $message = $result
                    ? 'Appointment updated in Google Calendar'
                    : 'Failed to update appointment in Google Calendar';
            } else {
                // Create new event
                $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminId);
                
                if ($eventId) {
                    // Get Google Meet link
                    $event = $this->googleCalendarService->getEvent($eventId, $adminId);
                    $meetLink = $this->googleCalendarService->getMeetLink($event);
                    
                    // Update appointment with event ID and meet link
                    $appointment->google_calendar_event_id = $eventId;
                    $appointment->is_google_synced = true;
                    
                    if ($meetLink) {
                        $appointment->meeting_link = $meetLink;
                    } else {
                        // Create a fallback Google Meet link if none was retrieved
                        $fallbackMeetLink = "https://meet.google.com/lookup/" . substr(md5($appointment->appointment_id . time()), 0, 10);
                        $appointment->meeting_link = $fallbackMeetLink;
                    }
                    
                    if ($this->Appointments->save($appointment)) {
                        // Send admin notification email
                        $this->loadComponent('Mailer');
                        $this->Mailer->send([
                            'to' => 'domqi1111@gmail.com', // Always send to this email
                            'subject' => 'New Appointment Confirmation',
                            'template' => 'admin_appointment_notification',
                            'layout' => 'default',
                            'emailFormat' => 'both',
                            'viewVars' => [
                                'appointment' => $appointment,
                                'customer' => $appointment->user,
                                'meetLink' => $appointment->meeting_link,
                                'calendarEventId' => $eventId,
                                'adminName' => $adminUser->first_name . ' ' . $adminUser->last_name,
                                'adminEmail' => $adminUser->email,
                            ]
                        ]);
                    }
                    
                    $message = 'Appointment added to Google Calendar';
                } else {
                    $message = 'Failed to add appointment to Google Calendar';
                }
                
                $result = !empty($eventId);
            }
            
            // Return response based on request type
            if ($this->request->is('ajax')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => $result,
                        'message' => $message,
                        'appointment' => [
                            'is_google_synced' => $appointment->is_google_synced,
                            'google_calendar_event_id' => $appointment->google_calendar_event_id,
                            'meeting_link' => $appointment->meeting_link,
                        ],
                    ]));
            }
            
            if ($result) {
                $this->Flash->success(__($message));
            } else {
                $this->Flash->error(__($message));
            }
            
            return $this->redirect(['action' => 'view', $id]);
        } catch (\Exception $e) {
            if ($this->request->is('ajax')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage(),
                    ]));
            }
            
            $this->Flash->error(__('Error syncing appointment with Google Calendar: {0}', $e->getMessage()));
            return $this->redirect(['action' => 'index']);
        }
    }
} 