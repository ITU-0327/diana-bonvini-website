<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

// Get user ID (passed from controller)
?>

<div class="row">
    <div class="column column-80">
        <div class="writingServiceRequests form content">

            <?= $this->Form->create($writingServiceRequest, ['id' => 'serviceRequestForm']) ?>

            <!-- Hidden field for the logged-in user's ID -->
            <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>

            <!-- Hidden fields for form submission -->
            <?= $this->Form->hidden('service_type', ['id' => 'serviceTypeHidden']) ?>
            <?= $this->Form->hidden('word_count_range', ['id' => 'wordCountHidden']) ?>
            <?= $this->Form->hidden('estimated_price', ['id' => 'estimatedPriceHidden']) ?>

            <fieldset id="serviceFieldset">
                <legend><?= __('Add Writing Service Request') ?></legend>

                <!-- Service Type dropdown -->
                <div class="mb-3">
                    <label for="serviceType" class="form-label">Service Type</label>
                    <select id="serviceType" name="service_type_display" class="form-select">
                        <option value="">Please select a service</option>
                        <option value="creative_writing">Creative Writing</option>
                        <option value="editing">Editing</option>
                        <option value="proofreading">Proofreading</option>
                    </select>
                </div>

                <!-- Word Count Range dropdown -->
                <div class="mb-3">
                    <label for="wordCountRange" class="form-label">Word Count Range</label>
                    <select id="wordCountRange" name="word_count_range_display" class="form-select">
                        <option value="">Please select a word count range</option>
                        <option value="under_5000">Under 5000</option>
                        <option value="5000_20000">5000 - 20000</option>
                        <option value="20000_50000">20000 - 50000</option>
                        <option value="50000_plus">50000+</option>
                    </select>
                </div>

                <!-- Button to calculate the estimated price -->
                <button type="button" id="getEstimateBtn" class="btn btn-secondary">
                    Get Estimated Price
                </button>
            </fieldset>

            <!-- Estimated price display (hidden by default) -->
            <fieldset id="estimatedPriceFieldset" disabled class="mb-3" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">Your estimated price is:</label>
                    <input type="text" id="estimatedPriceDisplay" class="form-control" placeholder="Estimated Price" readonly>
                </div>
            </fieldset>

            <!-- Notes field (hidden by default) -->
            <div id="notesSection" class="mb-3" style="display: none;">
                <label for="notes" class="form-label">Notes (maximum 100 characters)</label>
                <?= $this->Form->control('notes', [
                    'maxlength' => 100,
                    'id' => 'notes',
                    'class' => 'form-control',
                    'label' => false
                ]) ?>
            </div>

            <!-- Confirmation section with submit button (hidden by default) -->
            <div id="confirmationSection" style="display: none;" class="mb-3">
                <p>Do you want to submit this request to Dianna for a final price?</p>
                <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            </div>

            <?= $this->Form->end() ?>

        </div>
    </div>
</div>

<!-- JavaScript for handling estimated price calculation and form data population -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('serviceType');
        const wordCountSelect = document.getElementById('wordCountRange');
        const getEstimateBtn = document.getElementById('getEstimateBtn');
        const estimatedPriceFieldset = document.getElementById('estimatedPriceFieldset');
        const estimatedPriceDisplay = document.getElementById('estimatedPriceDisplay');
        const confirmationSection = document.getElementById('confirmationSection');
        const notesSection = document.getElementById('notesSection');

        // Hidden input fields for form submission
        const serviceTypeHidden = document.getElementById('serviceTypeHidden');
        const wordCountHidden = document.getElementById('wordCountHidden');
        const estimatedPriceHidden = document.getElementById('estimatedPriceHidden');

        // Pricing multipliers for each service type
        const multipliers = {
            'creative_writing': 2,
            'editing': 1.5,
            'proofreading': 1.2
        };

        // Function to calculate numeric estimated price
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

        // Event handler for the "Get Estimate" button
        getEstimateBtn.addEventListener('click', function() {
            const serviceType = serviceTypeSelect.value;
            const wordCountRange = wordCountSelect.value;

            if (!serviceType || !wordCountRange) {
                alert("Please select both a service type and a word count range.");
                return;
            }

            // Calculate estimated price
            const numericPrice = calculateNumericPrice(serviceType, wordCountRange);

            // Display price in the read-only field
            estimatedPriceDisplay.value = "$" + numericPrice;

            // Populate hidden fields with form values
            serviceTypeHidden.value = serviceType;
            wordCountHidden.value = wordCountRange;
            estimatedPriceHidden.value = numericPrice;

            // Show additional fields and submit button
            estimatedPriceFieldset.style.display = 'block';
            notesSection.style.display = 'block';
            confirmationSection.style.display = 'block';
        });
    });
</script>
