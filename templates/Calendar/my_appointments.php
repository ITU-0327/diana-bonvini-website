<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\ResultSet $upcomingAppointments
 * @var \Cake\ORM\ResultSet $pastAppointments
 */
?>
<div class="container max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'My Appointments']) ?>

    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Upcoming Appointments</h2>
            <?= $this->Html->link(
                '<span class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg> Book New</span>',
                ['action' => 'availability'],
                ['class' => 'bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition duration-150 text-sm', 'escape' => false]
            ) ?>
        </div>

        <?php if ($upcomingAppointments->count() > 0): ?>
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Related Request</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= $appointment->appointment_date->format('M j, Y') ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= $appointment->appointment_time->format('g:i A') ?> (<?= $appointment->duration ?> min)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            <?= h(ucfirst(str_replace('_', ' ', $appointment->appointment_type))) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($appointment->writing_service_request)): ?>
                                            <?= $this->Html->link(
                                                h($appointment->writing_service_request->writing_service_request_id),
                                                ['controller' => 'WritingServiceRequests', 'action' => 'view', $appointment->writing_service_request->writing_service_request_id],
                                                ['class' => 'text-blue-600 hover:text-blue-900 text-sm font-medium']
                                            ) ?>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $this->getStatusClass($appointment->status) ?>">
                                            <?= h(ucfirst($appointment->status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($appointment->status !== 'cancelled'): ?>
                                            <?php
                                            // Calculate if appointment is within 24 hours
                                            $appointmentDateTime = new DateTime(
                                                $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s')
                                            );
                                            $now = new DateTime();
                                            $diff = $appointmentDateTime->getTimestamp() - $now->getTimestamp();
                                            $hoursUntilAppointment = $diff / 3600;
                                            
                                            if ($hoursUntilAppointment >= 24):
                                            ?>
                                                <?= $this->Form->postLink(
                                                    'Cancel',
                                                    ['action' => 'cancel', $appointment->appointment_id],
                                                    [
                                                        'class' => 'text-red-600 hover:text-red-900',
                                                        'confirm' => 'Are you sure you want to cancel this appointment?'
                                                    ]
                                                ) ?>
                                            <?php else: ?>
                                                <span class="text-gray-400 cursor-not-allowed" title="Cannot cancel appointments within 24 hours">Cancel</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($appointment->meeting_link)): ?>
                                            <a href="<?= h($appointment->meeting_link) ?>" target="_blank" class="text-green-600 hover:text-green-900 ml-4">
                                                Join Meeting
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 mb-4">You don't have any upcoming appointments.</p>
                <?= $this->Html->link(
                    'Schedule a Consultation',
                    ['action' => 'availability'],
                    ['class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500']
                ) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Past Appointments</h2>
        
        <?php if ($pastAppointments->count() > 0): ?>
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Related Request</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pastAppointments as $appointment): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= $appointment->appointment_date->format('M j, Y') ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= $appointment->appointment_time->format('g:i A') ?> (<?= $appointment->duration ?> min)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            <?= h(ucfirst(str_replace('_', ' ', $appointment->appointment_type))) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($appointment->writing_service_request)): ?>
                                            <?= $this->Html->link(
                                                h($appointment->writing_service_request->writing_service_request_id),
                                                ['controller' => 'WritingServiceRequests', 'action' => 'view', $appointment->writing_service_request->writing_service_request_id],
                                                ['class' => 'text-blue-600 hover:text-blue-900 text-sm font-medium']
                                            ) ?>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $this->getStatusClass($appointment->status) ?>">
                                            <?= h(ucfirst($appointment->status)) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500">You don't have any past appointments.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper method to get status class
if (!$this->helpers->has('getStatusClass')) {
    $this->helpers->load('getStatusClass', function ($status) {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    });
}
?>