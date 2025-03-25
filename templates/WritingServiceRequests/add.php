<div class="max-w-3xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Add Writing Service Request</h1>

        <div class="space-y-6">
            <?= $this->Form->create($writingServiceRequest, ['type' => 'file']) ?>
            <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>

            <!-- Service Type -->
            <div>
                <?= $this->Form->label('service_type_display', 'Service Type', ['class' => 'block font-semibold text-gray-700 mb-1']) ?>
                <?= $this->Form->select(
                    'service_type_display',
                    [
                        '' => 'Please select a service',
                        'creative_writing' => 'Creative Writing',
                        'editing' => 'Editing',
                        'proofreading' => 'Proofreading',
                    ],
                    ['class' => 'w-full border-gray-300 rounded shadow-sm']
                ) ?>
            </div>

            <!-- Word Count Range -->
            <div>
                <?= $this->Form->label('word_count_range_display', 'Word Count Range', ['class' => 'block font-semibold text-gray-700 mb-1']) ?>
                <?= $this->Form->select(
                    'word_count_range_display',
                    [
                        '' => 'Please select a word count range',
                        'under_5000' => 'Under 5000',
                        '5000_20000' => '5000 - 20000',
                        '20000_50000' => '20000 - 50000',
                        '50000_plus' => '50000+',
                    ],
                    ['class' => 'w-full border-gray-300 rounded shadow-sm']
                ) ?>
            </div>

            <!-- Notes -->
            <div>
                <?= $this->Form->label('notes', 'Notes (maximum 100 characters)', ['class' => 'block font-semibold text-gray-700 mb-1']) ?>
                <?= $this->Form->textarea('notes', [
                    'maxlength' => 100,
                    'rows' => 3,
                    'class' => 'w-full border-gray-300 rounded shadow-sm'
                ]) ?>
            </div>

            <!-- Upload Document -->
            <div>
                <?= $this->Form->label('document', 'Upload Document (TXT, PDF, Word)', ['class' => 'block font-semibold text-gray-700 mb-1']) ?>
                <?= $this->Form->file('document', ['class' => 'w-full border-gray-300 rounded shadow-sm']) ?>
            </div>

            <!-- Estimated Price -->
            <?php if ($writingServiceRequest->estimated_price !== null): ?>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Estimated Price</label>
                    <input type="text" class="w-full bg-gray-100 border-gray-300 rounded shadow-sm" value="$<?= h(number_format($writingServiceRequest->estimated_price, 2)) ?>" readonly>
                </div>
            <?php endif; ?>

            <!-- Submit Button -->
            <div class="text-center">
                <?= $this->Form->button(__('Submit Request'), [
                    'class' => 'bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition'
                ]) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>

        <!-- View My Requests -->
        <div class="mt-6 text-center">
            <?= $this->Html->link(
                'View My Requests',
                ['controller' => 'WritingServiceRequests', 'action' => 'index'],
                ['class' => 'inline-block border border-gray-400 text-gray-700 px-5 py-2 rounded hover:bg-gray-100 transition']
            ) ?>
        </div>
    </div>
</div>
