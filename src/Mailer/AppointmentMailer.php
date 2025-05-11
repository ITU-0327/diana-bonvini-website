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
     * @return self
     */
    public function appointmentConfirmation(Appointment $appointment)
    {
        return $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Your Appointment Confirmation')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_confirmation');
    }
    
    /**
     * Build email for appointment confirmation to admin
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return self
     */
    public function adminNotification(Appointment $appointment, string $adminEmail, string $adminName)
    {
        return $this
            ->setEmailFormat('both')
            ->setTo($adminEmail, $adminName)
            ->setSubject('New Appointment Scheduled: ' . $appointment->appointment_date->format('M j, Y'))
            ->setViewVars([
                'appointment' => $appointment,
                'adminName' => $adminName,
            ])
            ->viewBuilder()
                ->setTemplate('admin_appointment_notification');
    }
    
    /**
     * Build email for appointment update notification
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return self
     */
    public function appointmentUpdate(Appointment $appointment)
    {
        return $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Your Appointment Has Been Updated')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_update');
    }
    
    /**
     * Build email for appointment cancellation notification
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return self
     */
    public function appointmentCancellation(Appointment $appointment)
    {
        return $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Your Appointment Has Been Cancelled')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_cancellation');
    }
    
    /**
     * Build email for appointment reminder (24 hours before)
     *
     * @param \App\Model\Entity\Appointment $appointment The appointment entity
     * @return self
     */
    public function appointmentReminder(Appointment $appointment)
    {
        return $this
            ->setEmailFormat('both')
            ->setTo($appointment->user->email, $appointment->user->first_name . ' ' . $appointment->user->last_name)
            ->setSubject('Reminder: Your Appointment Tomorrow')
            ->setViewVars([
                'appointment' => $appointment,
                'userName' => $appointment->user->first_name,
            ])
            ->viewBuilder()
                ->setTemplate('appointment_reminder');
    }
}