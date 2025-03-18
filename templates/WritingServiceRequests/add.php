<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
?>

<!-- Link custom CSS if needed -->
<?= $this->Html->css('writing_service_requests') ?>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="mb-4">Add Writing Service Request</h1>

            <div class="writingServiceRequests form content">

                <?= $this->Form->create($writingServiceRequest, ['id' => 'serviceRequestForm']) ?>

                <!-- Hidden user ID -->
                <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>

                <!-- Hidden fields for service type, word count, and price -->
                <?= $this->Form->hidden('service_type', ['id' => 'serviceTypeHidden']) ?>
                <?= $this->Form->hidden('word_count_range', ['id' => 'wordCountHidden']) ?>
                <?= $this->Form->hidden('estimated_price', ['id' => 'estimatedPriceHidden']) ?>

                <!-- Service Type -->
                <div class="mb-3">
                    <?= $this->Form->label('service_type_display', 'Service Type', ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->select(
                        'service_type_display',
                        [
                            '' => 'Please select a service',
                            'creative_writing' => 'Creative Writing',
                            'editing' => 'Editing',
                            'proofreading' => 'Proofreading'
                        ],
                        ['id' => 'serviceType', 'class' => 'form-select']
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
                            '50000_plus' => '50000+'
                        ],
                        ['id' => 'wordCountRange', 'class' => 'form-select']
                    ) ?>
                </div>

                <!-- Get Estimated Price Button -->
                <div class="d-grid mb-4">
                    <button type="button" id="getEstimateBtn" class="btn btn-outline-primary">
                        Get Estimated Price
                    </button>
                </div>

                <!-- Estimated Price Field (Initially hidden) -->
                <fieldset id="estimatedPriceFieldset" class="mb-4" style="display: none;">
                    <div class="mb-3">
                        <label for="estimatedPriceDisplay" class="form-label fw-bold">Estimated Price</label>
                        <input type="text" id="estimatedPriceDisplay" class="form-control" readonly>
                    </div>
                </fieldset>

                <!-- Notes Field (Initially hidden) -->
                <div id="notesSection" class="mb-4" style="display: none;">
                    <?= $this->Form->label('notes', 'Notes (maximum 100 characters)', ['class' => 'form-label fw-bold']) ?>
                    <?= $this->Form->textarea('notes', [
                        'maxlength' => 100,
                        'id' => 'notes',
                        'class' => 'form-control',
                        'rows' => 3
                    ]) ?>
                </div>

                <!-- Confirmation Section with Submit Button (Initially hidden) -->
                <div id="confirmationSection" class="text-center" style="display: none;">
                    <p class="mb-3 fw-semibold">Do you want to submit this request to Dianna for a final price?</p>
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

<!-- JS for dynamic interactions -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('serviceType');
        const wordCountSelect = document.getElementById('wordCountRange');
        const getEstimateBtn = document.getElementById('getEstimateBtn');
        const estimatedPriceFieldset = document.getElementById('estimatedPriceFieldset');
        const estimatedPriceDisplay = document.getElementById('estimatedPriceDisplay');
        const confirmationSection = document.getElementById('confirmationSection');
        const notesSection = document.getElementById('notesSection');

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
            notesSection.style.display = 'block';
            confirmationSection.style.display = 'block';
        });
    });
</script>
