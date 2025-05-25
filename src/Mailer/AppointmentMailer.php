<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\Appointment;
use Cake\Mailer\Mailer;

/**
 * AppointmentMailer for sending appointment-related emails
 */
class AppointmentMailer extends Mailer
{
    /**
     * Build email for appointment confirmation to customer
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return void
     */
    public function appointmentConfirmation(Appointment $appointment): void
    {
        // The appointment should already have a proper meeting link from the controller
        // No need to generate fallback here anymore since we handle it properly in CalendarController
        
        $this
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('✅ Your Writing Consultation is Confirmed!')
            ->setEmailFormat('both')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
                'meetingLink' => $appointment->meeting_link, // Use the actual meeting link generated
            ])
            ->viewBuilder()
                ->setTemplate('appointment_confirmation')
                ->setLayout('default');
    }
    
    /**
     * Build email for coaching appointment confirmation to customer
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return void
     */
    public function coachingAppointmentConfirmation(Appointment $appointment): void
    {
        $this
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('✅ Your Coaching Consultation is Confirmed!')
            ->setEmailFormat('both')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
                'meetingLink' => $appointment->meeting_link, // Use the actual meeting link generated
            ])
            ->viewBuilder()
                ->setTemplate('coaching_appointment_confirmation')
                ->setLayout('default');
    }
    
    /**
     * Build email for appointment confirmation to admin
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return void
     */
    public function adminNotification(Appointment $appointment, string $adminEmail, string $adminName): void
    {
        $this
            ->setEmailFormat('both')
            ->setTo($adminEmail, $adminName)
            ->setSubject('🔔 New Appointment Booked: ' . $appointment->user->first_name . ' ' . $appointment->user->last_name . ' - ' . $appointment->appointment_date->format('M j, Y') . ' at ' . $appointment->appointment_time->format('g:i A'))
            ->setViewVars([
                'appointment' => $appointment,
                'adminName' => $adminName,
                'customerName' => $appointment->user->first_name . ' ' . $appointment->user->last_name,
                'customerEmail' => $appointment->user->email,
                'meetingLink' => $appointment->meeting_link, // Use the actual meeting link
            ])
            ->viewBuilder()
                ->setTemplate('admin_appointment_notification')
                ->setLayout('default');
    }
    
    /**
     * Build email for coaching appointment confirmation to admin
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return void
     */
    public function coachingAdminNotification(Appointment $appointment, string $adminEmail, string $adminName): void
    {
        $this
            ->setEmailFormat('both')
            ->setTo($adminEmail, $adminName)
            ->setSubject('🔔 New Coaching Appointment Booked: ' . $appointment->user->first_name . ' ' . $appointment->user->last_name . ' - ' . $appointment->appointment_date->format('M j, Y') . ' at ' . $appointment->appointment_time->format('g:i A'))
            ->setViewVars([
                'appointment' => $appointment,
                'adminName' => $adminName,
                'customerName' => $appointment->user->first_name . ' ' . $appointment->user->last_name,
                'customerEmail' => $appointment->user->email,
                'meetingLink' => $appointment->meeting_link, // Use the actual meeting link
            ])
            ->viewBuilder()
                ->setTemplate('admin_coaching_appointment_notification')
                ->setLayout('default');
    }
    
    /**
     * Build email for appointment update notification
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return void
     */
    public function appointmentUpdate(Appointment $appointment): void
    {
        $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Your Appointment Has Been Updated')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_update')
                ->setLayout('default');
    }
    
    /**
     * Build email for appointment cancellation notification
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return void
     */
    public function appointmentCancellation(Appointment $appointment): void
    {
        $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Your Appointment Has Been Cancelled')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_cancellation')
                ->setLayout('default');
    }
    
    /**
     * Build email for appointment reminder (24 hours before)
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return void
     */
    public function appointmentReminder(Appointment $appointment): void
    {
        $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Reminder: Your Appointment Tomorrow')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_reminder')
                ->setLayout('default');
    }
}