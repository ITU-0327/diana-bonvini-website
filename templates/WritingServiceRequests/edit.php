<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 * @var string $userId
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $writingServiceRequest->request_id], ['confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->request_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Writing Service Requests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="writingServiceRequests form content">
            <?= $this->Form->create($writingServiceRequest, ['type' => 'file', 'id' => 'serviceRequestForm']) ?>
            <fieldset>
                <legend><?= __('Edit Writing Service Request') ?></legend>
                <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>
                <?= $this->Form->hidden('service_type', ['id' => 'serviceTypeHidden']) ?>
                <?= $this->Form->hidden('word_count_range', ['id' => 'wordCountHidden']) ?>
                <?= $this->Form->hidden('estimated_price', ['id' => 'estimatedPriceHidden']) ?>
                <?= $this->Form->select(
                    'service_type_display',
                    [
                        '' => 'Please select a service',
                        'creative_writing' => 'Creative Writing',
                        'editing' => 'Editing',
                        'proofreading' => 'Proofreading',
                    ],
                    ['id' => 'serviceType', 'class' => 'form-select', 'default' => $writingServiceRequest->service_type]
                ) ?>
                <?= $this->Form->select(
                    'word_count_range_display',
                    [
                        '' => 'Please select a word count range',
                        'under_5000' => 'Under 5000',
                        '5000_20000' => '5000 - 20000',
                        '20000_50000' => '20000 - 50000',
                        '50000_plus' => '50000+',
                    ],
                    ['id' => 'wordCountRange', 'class' => 'form-select', 'default' => $writingServiceRequest->word_count_range]
                ) ?>
                <?= $this->Form->control('notes') ?>
                <?= $this->Form->control('final_price') ?>
                <?= $this->Form->control('request_status') ?>
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('Created At') ?></label>
                    <p class="form-control-plaintext"><?= h($writingServiceRequest->created_at) ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('Updated At') ?></label>
                    <p class="form-control-plaintext"><?= h($writingServiceRequest->updated_at) ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('Existing Document') ?></label>
                    <?php if (!empty($writingServiceRequest->document)): ?>
                        <?= $this->Html->link('View Document', '/' . $writingServiceRequest->document, ['target' => '_blank']) ?>
                    <?php else: ?>
                        <span><?= __('No Document Uploaded') ?></span>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?= $this->Form->label('document', __('Upload New Document (TXT, PDF, Word)'), ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->file('document', ['class' => 'form-control']) ?>
                </div>
                <div class="d-grid mb-4">
                    <button type="button" id="getEstimateBtn" class="btn btn-outline-primary">Get Estimated Price</button>
                </div>
                <fieldset id="estimatedPriceFieldset" class="mb-4" style="display: none;">
                    <div class="mb-3">
                        <label for="estimatedPriceDisplay" class="form-label fw-bold">Estimated Price</label>
                        <input type="text" id="estimatedPriceDisplay" class="form-control" readonly>
                    </div>
                </fieldset>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('serviceType');
        const wordCountSelect = document.getElementById('wordCountRange');
        const getEstimateBtn = document.getElementById('getEstimateBtn');
        const estimatedPriceFieldset = document.getElementById('estimatedPriceFieldset');
        const estimatedPriceDisplay = document.getElementById('estimatedPriceDisplay');
        const serviceTypeHidden = document.getElementById('serviceTypeHidden');
        const wordCountHidden = document.getElementById('wordCountHidden');
        const estimatedPriceHidden = document.getElementById('estimatedPriceHidden');

        const multipliers = {
            'creative_writing': 2,
            'editing': 1.5,
            'proofreading': 1.2
        };

        function calculateNumericPrice(service, wordRange) {
            const multiplier = multipliers[service];
            if (wordRange === 'under_5000') {
                return (multiplier * 5000).toFixed(2);
            } else if (wordRange === '5000_20000') {
                return (multiplier * 20000).toFixed(2);
            } else if (wordRange === '20000_50000') {
                return (multiplier * 50000).toFixed(2);
            } else if (wordRange === '50000_plus') {
                return (multiplier * 50000).toFixed(2);
            }
            return 0;
        }

        getEstimateBtn.addEventListener('click', function() {
            const serviceType = serviceTypeSelect.value;
            const wordCountRange = wordCountSelect.value;

            if (!serviceType || !wordCountRange) {
                alert("Please select both a service type and a word count range.");
                return;
            }

            const numericPrice = calculateNumericPrice(serviceType, wordCountRange);
            estimatedPriceDisplay.value = "$" + numericPrice;
            serviceTypeHidden.value = serviceType;
            wordCountHidden.value = wordCountRange;
            estimatedPriceHidden.value = numericPrice;
            estimatedPriceFieldset.style.display = 'block';
        });
    });
</script>
