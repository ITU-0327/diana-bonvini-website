<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
?>
<div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
    <div class="px-8 py-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Add Writing Service Request</h1>

        <?= $this->Form->create($writingServiceRequest, ['type' => 'file', 'class' => 'space-y-6']) ?>

        <!-- Service Type -->
        <div>
            <?= $this->Form->label('service_type', 'Service Type', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->select(
                'service_type',
                [
                    '' => 'Please select a service',
                    'creative_writing' => 'Creative Writing',
                    'editing' => 'Editing',
                    'proofreading' => 'Proofreading',
                ],
                [
                    'id' => 'service-type',
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
                ],
            ) ?>
        </div>

        <!-- Word Count Range -->
        <div>
            <?= $this->Form->label('word_count_range', 'Word Count Range', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->select(
                'word_count_range',
                [
                    '' => 'Please select a word count range',
                    'under_5000' => 'Under 5000',
                    '5000_20000' => '5000 - 20000',
                    '20000_50000' => '20000 - 50000',
                    '50000_plus' => '50000+',
                ],
                [
                    'id' => 'word-count',
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
                ],
            ) ?>
        </div>

        <!-- Notes -->
        <div>
            <?= $this->Form->label('notes', 'Notes (maximum 100 characters)', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->textarea('notes', [
                'maxlength' => 100,
                'rows' => 3,
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
            ]) ?>
        </div>

        <!-- File Upload -->
        <div>
            <?= $this->Form->label('document', 'Upload Document', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->file('document', [
                'class' => 'block w-full text-gray-700 py-2 px-3 border border-gray-300 rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500',
                'accept' => '.pdf,.jpg,.jpeg,.docx',
            ]) ?>
            <p class="mt-1 text-sm text-gray-500">
                Only PDF, JPG, and MS Word files can be uploaded.
            </p>
        </div>

        <!-- Submit button -->
        <div class="text-center">
            <?= $this->Form->button('Submit Request', [
                'type' => 'submit',
                'class' => 'w-full bg-blue-600 text-white font-semibold py-3 rounded hover:bg-blue-700 transition duration-150',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>

        <!-- Link: View user requests -->
        <div class="mt-8 text-center">
            <?= $this->Html->link(
                'View My Requests',
                ['controller' => 'WritingServiceRequests', 'action' => 'index'],
                [
                    'class' => 'inline-block border border-gray-400 text-gray-700 font-medium px-5 py-2 rounded hover:bg-gray-100 transition duration-150',
                ],
            ) ?>
        </div>
    </div>
</div>
