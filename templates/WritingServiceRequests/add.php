<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header with left underline -->
    <div class="flex flex-col items-start mb-8">
        <h1 class="text-3xl uppercase text-gray-800">Enquire Writing Service</h1>
        <div class="mt-1 w-16 h-[2px] bg-gray-800"></div>
    </div>

    <!-- Form Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <?= $this->Form->create($writingServiceRequest, ['type' => 'file', 'class' => 'space-y-6']) ?>

        <!-- Service Type -->
        <div>
            <?= $this->Form->label('service_type', 'Service Type', [
                'class' => 'block font-medium text-gray-700 mb-2'
            ]) ?>
            <?= $this->Form->select('service_type', [
                '' => 'Please select a service',
                'creative_writing' => 'Creative Writing',
                'editing' => 'Editing',
                'proofreading' => 'Proofreading',
            ], [
                'id' => 'service-type',
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm'
            ]) ?>
        </div>

        <!-- Word Count Range -->
        <div>
            <?= $this->Form->label('word_count_range', 'Word Count Range', [
                'class' => 'block font-medium text-gray-700 mb-2'
            ]) ?>
            <?= $this->Form->select('word_count_range', [
                '' => 'Please select a word count range',
                'under_5000' => 'Under 5000',
                '5000_20000' => '5000 - 20000',
                '20000_50000' => '20000 - 50000',
                '50000_plus' => '50000+',
            ], [
                'id' => 'word-count',
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm'
            ]) ?>
        </div>

        <!-- Notes -->
        <div>
            <?= $this->Form->label('notes', 'Notes (maximum 100 characters)', [
                'class' => 'block font-medium text-gray-700 mb-2'
            ]) ?>
            <?= $this->Form->textarea('notes', [
                'maxlength' => 100,
                'rows' => 3,
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm'
            ]) ?>
        </div>

        <!-- File Upload -->
        <div>
            <?= $this->Form->label('document', 'Upload Document', [
                'class' => 'block font-medium text-gray-700 mb-2'
            ]) ?>
            <?= $this->Form->file('document', [
                'class' => 'block w-full text-gray-700 py-2 px-3 border border-gray-300 rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500',
                'accept' => '.pdf,.jpg,.jpeg,.docx'
            ]) ?>
            <p class="mt-1 text-sm text-gray-500">
                Only PDF, JPG, and MS Word files can be uploaded.
            </p>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <?= $this->Form->button('Submit Request', [
                'type' => 'submit',
                'class' => 'w-full bg-blue-600 text-white font-semibold py-3 rounded hover:bg-blue-700 transition duration-150'
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <!-- Link: View My Requests -->
    <div class="text-center">
        <?= $this->Html->link('View My Requests', ['controller' => 'WritingServiceRequests', 'action' => 'index'], [
            'class' => 'text-teal-600 hover:text-teal-700 font-semibold'
        ]) ?>
    </div>
</div>
