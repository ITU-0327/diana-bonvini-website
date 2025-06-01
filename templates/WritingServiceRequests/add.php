<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

$this->assign('title', 'Enquire Writing Service');
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Enquire Writing Service']) ?>

    <!-- Form Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <?= $this->Form->create($writingServiceRequest, ['type' => 'file', 'class' => 'space-y-6']) ?>

        <!-- Service Title -->
        <div>
            <?= $this->Form->label('service_title', 'Request Title', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->text('service_title', [
                'maxlength' => 100,
                'placeholder' => 'Briefly describe your service...',
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
            ]) ?>
        </div>

        <!-- Service Type -->
        <div>
            <?= $this->Form->label('service_type', 'Service Type (describe the service you need)', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->text('service_type', [
                'id' => 'service-type',
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
                'maxlength' => 200,
                'placeholder' => 'Describe the type of writing service you need (max 200 characters)',
            ]) ?>
            <p class="mt-1 text-sm text-gray-500">
                Examples: Creative Writing, Editing, Proofreading, Essay Writing, Technical Writing, etc.
            </p>
        </div>

        <!-- Notes -->
        <div>
            <?= $this->Form->label('notes', 'Notes', [
                'class' => 'block font-medium text-gray-700 mb-2',
            ]) ?>
            <?= $this->Form->textarea('notes', [
                'maxlength' => 1000,
                'rows' => 3,
                'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
            ]) ?>
        </div>

        <!-- File Upload -->
        <div>
            <?= $this->Form->label('document', 'Upload Document <span class="text-sm text-gray-500">(Optional)</span>', [
                'class' => 'block font-medium text-gray-700 mb-2',
                'escape' => false,
            ]) ?>
            <p class="mb-2 text-sm text-gray-600">
                Upload any reference documents, drafts, or examples that will help us understand your requirements better.
            </p>
            <?= $this->Form->file('document', [
                'class' => 'block w-full text-gray-700 py-2 px-3 border border-gray-300 rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500',
                'accept' => '.pdf,.jpeg,.docx',
            ]) ?>
            <p class="mt-1 text-sm text-gray-500">
                Only PDF, and MS Word files can be uploaded.
            </p>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <?= $this->Form->button('Submit Request', [
                'type' => 'submit',
                'class' => 'w-full bg-blue-600 text-white font-semibold py-3 rounded hover:bg-blue-700 transition duration-150',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <!-- Link: View My Requests -->
    <div class="text-center">
        <?= $this->Html->link('View My Requests', ['controller' => 'WritingServiceRequests', 'action' => 'index'], [
            'class' => 'text-teal-600 hover:text-teal-700 font-semibold',
        ]) ?>
    </div>
</div>
