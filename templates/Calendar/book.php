<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var array $appointmentTypes
 * @var array $writingServiceRequests
 * @var \App\Model\Entity\WritingServiceRequest|null $writingServiceRequest
 */
?>
<div class="container max-w-4xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Book Your Consultation']) ?>

    <?php if (!empty($writingServiceRequest)): ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Writing Service Request Details</h3>
                <div class="mt-1 text-sm text-blue-700">
                    <p><span class="font-semibold"><?= h($writingServiceRequest->writing_service_request_id) ?></span>: <?= h($writingServiceRequest->service_title) ?></p>
                    <p class="mt-1"><span class="font-semibold">Type:</span> <?= h(ucfirst(str_replace('_', ' ', $writingServiceRequest->service_type))) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white">Appointment Details</h2>
            <p class="text-blue-100 mt-1">Please confirm your consultation appointment</p>
        </div>

        <?= $this->Form->create($appointment, ['class' => 'p-6']) ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date and Time (read-only) -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <?php if (!empty($appointment->appointment_date)): ?>
                        <?= $this->Form->hidden('appointment_date') ?>
                        <div class="bg-gray-50 border border-gray-300 rounded-md py-2 px-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-gray-700">
                                <?= $appointment->appointment_date->format('l, F j, Y') ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <?= $this->Form->date('appointment_date', ['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500', 'required' => true]) ?>
                    <?php endif; ?>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                    <?php if (!empty($appointment->appointment_time)): ?>
                        <?= $this->Form->hidden('appointment_time') ?>
                        <div class="bg-gray-50 border border-gray-300 rounded-md py-2 px-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-gray-700">
                                <?= $appointment->appointment_time->format('g:i A') ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <?= $this->Form->time('appointment_time', ['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500', 'required' => true]) ?>
                    <?php endif; ?>
                </div>

                <!-- Duration (read-only) -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                    <?= $this->Form->hidden('duration', ['value' => 30]) ?>
                    <div class="bg-gray-50 border border-gray-300 rounded-md py-2 px-3 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-700">30 minutes</span>
                    </div>
                </div>

                <!-- Appointment Type -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Appointment Type</label>
                    <?= $this->Form->select('appointment_type', $appointmentTypes, [
                        'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                        'empty' => 'Select appointment type',
                        'required' => true,
                    ]) ?>
                </div>

                <!-- Writing Service Request -->
                <?php if (!empty($appointment->writing_service_request_id) || !empty($writingServiceRequest)): ?>
                    <?= $this->Form->hidden('writing_service_request_id') ?>
                <?php elseif (!empty($writingServiceRequests)): ?>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Related Writing Service Request (Optional)</label>
                        <?= $this->Form->select('writing_service_request_id', $writingServiceRequests, [
                            'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            'empty' => 'Select request (optional)',
                        ]) ?>
                        <p class="mt-1 text-sm text-gray-500">Connect this appointment to an existing writing service request</p>
                    </div>
                <?php endif; ?>

                <!-- Description / Notes -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description / Notes (Optional)</label>
                    <?= $this->Form->textarea('description', [
                        'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                        'rows' => 3,
                        'placeholder' => 'Add any additional details or questions for the consultation...',
                    ]) ?>
                </div>
            </div>

            <div class="mt-8 p-4 bg-gray-50 rounded-md">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Important Information</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Consultations are conducted via Google Meet. You'll receive a meeting link via email.
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Initial consultations are free of charge. 
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Cancellations must be made at least 24 hours in advance.
                    </li>
                </ul>
            </div>

            <div class="flex justify-between mt-6">
                <?= $this->Html->link(
                    'Back',
                    ['action' => 'availability'],
                    ['class' => 'inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500']
                ) ?>
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Confirm Booking
                </button>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>