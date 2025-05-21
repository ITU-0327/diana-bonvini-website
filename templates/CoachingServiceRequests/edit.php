<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest
 */
?>

<div class="flex flex-col lg:flex-row gap-6 p-6 max-w-5xl mx-auto">
    <aside class="w-full lg:w-1/4">
        <div class="bg-white p-4 shadow rounded">
            <h4 class="text-lg font-semibold text-gray-700 mb-4"><?= __('Actions') ?></h4>
            <div class="flex flex-col space-y-2">
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $coachingServiceRequest->coaching_service_request_id], [
                    'confirm' => __('Are you sure you want to delete # {0}?', $coachingServiceRequest->coaching_service_request_id),
                    'class' => 'text-red-600 hover:underline',
                ]) ?>
                <?= $this->Html->link(__('Back To My Request List'), ['action' => 'index'], [
                    'class' => 'text-blue-600 hover:underline',
                ]) ?>
            </div>
        </div>
    </aside>

    <div class="w-full lg:w-3/4">
        <div class="bg-white p-6 shadow rounded">
            <?= $this->Form->create($coachingServiceRequest, ['type' => 'file']) ?>
            <fieldset class="space-y-6">
                <legend class="text-2xl font-bold text-gray-800 mb-4"><?= __('Edit Coaching Service Request') ?></legend>

                <div>
                    <?= $this->Form->control('service_title', [
                        'label' => 'Service Title (max 100 characters)',
                        'maxlength' => 100,
                        'placeholder' => 'Enter service title',
                        'class' => 'w-full border-gray-300 rounded shadow-sm'
                    ]) ?>
                </div>

                <div>
                    <?= $this->Form->label('service_type', 'Coaching Type (describe the service you need)', ['class' => 'block font-medium text-gray-700 mb-2']) ?>
                    <?= $this->Form->text('service_type', [
                        'class' => 'w-full mt-1 border-gray-300 rounded shadow-sm',
                        'maxlength' => 200,
                        'placeholder' => 'Describe the type of coaching service you need (max 200 characters)',
                    ]) ?>
                    <p class="mt-1 text-sm text-gray-500">
                        Examples: Career Coaching, Life Coaching, Business Coaching, Executive Coaching, Mindfulness Coaching, etc.
                    </p>
                </div>

                <div>
                    <?= $this->Form->label('notes', 'Notes (maximum 1000 characters)', [
                        'class' => 'block font-medium text-gray-700 mb-2',
                    ]) ?>
                    <?= $this->Form->textarea('notes', [
                        'maxlength' => 1000,
                        'rows' => 3,
                        'class' => 'block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm',
                    ]) ?>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700"><?= __('Existing Document') ?></label>
                    <?php if (!empty($coachingServiceRequest->document)) : ?>
                        <?= $this->Html->link('View Document', '/' . $coachingServiceRequest->document, [
                            'target' => '_blank',
                            'class' => 'text-blue-600 hover:underline',
                        ]) ?>
                    <?php else : ?>
                        <span class="text-gray-500 italic"><?= __('No Document Uploaded') ?></span>
                    <?php endif; ?>
                </div>

                <p class="block font-semibold text-gray-700">Want to change document?</p>
                <?= $this->Form->file('document', [
                    'class' => 'w-full border-gray-300 rounded shadow-sm',
                    'accept' => '.pdf,.jpg,.jpeg,.docx',
                ]) ?>
                <p class="text-sm text-gray-500 mb-1">Only PDF, JPG, and DOCX files can be uploaded.</p>
            </fieldset>

            <div class="mt-6">
                <?= $this->Form->button(__('Submit'), [
                    'class' => 'bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition',
                ]) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timeElements = document.querySelectorAll('.local-time');

        timeElements.forEach(el => {
            const isoTime = el.dataset.datetime;
            const date = new Date(isoTime);

            el.textContent = date.toLocaleString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            });
        });
    });
</script> 