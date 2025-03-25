<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="mb-4">Add Writing Service Request</h1>

            <div class="writingServiceRequests form content">
                <?= $this->Form->create($writingServiceRequest, ['type' => 'file']) ?>

                <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>

                <!-- Service Type -->
                <div class="mb-3">
                    <?= $this->Form->label('service_type_display', 'Service Type', ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->select(
                        'service_type_display',
                        [
                            '' => 'Please select a service',
                            'creative_writing' => 'Creative Writing',
                            'editing' => 'Editing',
                            'proofreading' => 'Proofreading',
                        ],
                        ['class' => 'form-select']
                    ) ?>
                </div>

                <!-- Word Count Range -->
                <div class="mb-3">
                    <?= $this->Form->label('word_count_range_display', 'Word Count Range', ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->select(
                        'word_count_range_display',
                        [
                            '' => 'Please select a word count range',
                            'under_5000' => 'Under 5000',
                            '5000_20000' => '5000 - 20000',
                            '20000_50000' => '20000 - 50000',
                            '50000_plus' => '50000+',
                        ],
                        ['class' => 'form-select']
                    ) ?>
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <?= $this->Form->label('notes', 'Notes (maximum 100 characters)', ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->textarea('notes', [
                        'maxlength' => 100,
                        'class' => 'form-control',
                        'rows' => 3,
                    ]) ?>
                </div>

                <!-- Upload Document -->
                <div class="mb-3">
                    <?= $this->Form->label('document', 'Upload Document (TXT, PDF, Word)', ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->file('document', ['class' => 'form-control']) ?>
                </div>

                <!-- Estimated Price -->
                <?php if ($writingServiceRequest->estimated_price !== null): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Estimated Price</label>
                        <input type="text" class="form-control" value="$<?= h(number_format($writingServiceRequest->estimated_price, 2)) ?>" readonly>
                    </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <div class="text-center">
                    <?= $this->Form->button(__('Submit Request'), ['class' => 'btn btn-primary px-4']) ?>
                </div>

                <?= $this->Form->end() ?>
            </div>

            <!-- View My Requests Button -->
            <div class="container mt-4 text-center">
                <?= $this->Html->link(
                    'View My Requests',
                    ['controller' => 'WritingServiceRequests', 'action' => 'index'],
                    ['class' => 'btn btn-outline-secondary px-4']
                ) ?>
            </div>
        </div>
    </div>
</div>
