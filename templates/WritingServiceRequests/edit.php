<div class="flex flex-col lg:flex-row gap-6 p-6 max-w-5xl mx-auto">
    <aside class="w-full lg:w-1/4">
        <div class="bg-white p-4 shadow rounded">
            <h4 class="text-lg font-semibold text-gray-700 mb-4"><?= __('Actions') ?></h4>
            <div class="flex flex-col space-y-2">
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $writingServiceRequest->request_id], [
                    'confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->request_id),
                    'class' => 'text-red-600 hover:underline'
                ]) ?>
                <?= $this->Html->link(__('Back To My Request List'), ['action' => 'index'], [
                    'class' => 'text-blue-600 hover:underline'
                ]) ?>
            </div>
        </div>
    </aside>

    <div class="w-full lg:w-3/4">
        <div class="bg-white p-6 shadow rounded">
            <?= $this->Form->create($writingServiceRequest, ['type' => 'file']) ?>
            <fieldset class="space-y-6">
                <legend class="text-2xl font-bold text-gray-800 mb-4"><?= __('Edit Writing Service Request') ?></legend>

                <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>

                <div>
                    <?= $this->Form->label('service_type', 'Service Type', ['class' => 'block font-medium text-gray-700']) ?>
                    <?= $this->Form->select('service_type', [
                        '' => 'Please select a service',
                        'creative_writing' => 'Creative Writing',
                        'editing' => 'Editing',
                        'proofreading' => 'Proofreading',
                    ], ['class' => 'w-full mt-1 border-gray-300 rounded shadow-sm']) ?>
                </div>

                <div>
                    <?= $this->Form->label('word_count_range', 'Word Count Range', ['class' => 'block font-medium text-gray-700']) ?>
                    <?= $this->Form->select('word_count_range', [
                        '' => 'Please select a word count range',
                        'under_5000' => 'Under 5000',
                        '5000_20000' => '5000 - 20000',
                        '20000_50000' => '20000 - 50000',
                        '50000_plus' => '50000+',
                    ], ['class' => 'w-full mt-1 border-gray-300 rounded shadow-sm']) ?>
                </div>

                <div>
                    <?= $this->Form->control('notes', [
                        'label' => 'Notes',
                        'class' => 'w-full border-gray-300 rounded shadow-sm'
                    ]) ?>
                </div>

                <!-- Final Price (if still needed) -->
                <div>
                    <?= $this->Form->control('final_price', [
                        'label' => 'Final Price',
                        'class' => 'w-full border-gray-300 rounded shadow-sm'
                    ]) ?>
                </div>

                <div>
                    <?= $this->Form->control('request_status', [
                        'label' => 'Request Status',
                        'class' => 'w-full border-gray-300 rounded shadow-sm'
                    ]) ?>
                </div>

                <!-- Removed the Estimated Price block -->

                <div>
                    <label class="block font-semibold text-gray-700"><?= __('Created At') ?></label>
                    <p class="text-gray-600">
                        <span class="local-time" data-datetime="<?= h($writingServiceRequest->created_at->format('c')) ?>"></span>
                    </p>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700"><?= __('Updated At') ?></label>
                    <p class="text-gray-600">
                        <span class="local-time" data-datetime="<?= h($writingServiceRequest->updated_at->format('c')) ?>"></span>
                    </p>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700"><?= __('Existing Document') ?></label>
                    <?php if (!empty($writingServiceRequest->document)): ?>
                        <?= $this->Html->link('View Document', '/' . $writingServiceRequest->document, [
                            'target' => '_blank',
                            'class' => 'text-blue-600 hover:underline'
                        ]) ?>
                    <?php else: ?>
                        <span class="text-gray-500 italic"><?= __('No Document Uploaded') ?></span>
                    <?php endif; ?>
                </div>

                <p class="text-sm text-gray-500 mb-1">Want change document?</p>
                <?= $this->Form->file('document', [
                    'class' => 'w-full border-gray-300 rounded shadow-sm',
                    'accept' => '.pdf,.jpg,.jpeg,.docx'
                ]) ?>
                <p class="text-sm text-gray-500 mb-1">Only PDF, JPG, and DOCX files can be uploaded.</p>
            </fieldset>

            <div class="mt-6">
                <?= $this->Form->button(__('Submit'), [
                    'class' => 'bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition'
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

            const formatted = date.toLocaleString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });

            el.textContent = formatted;
        });
    });
</script>
