<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Writing Service Request</title>
    <!-- Using Tailwind CSS CDN -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
    >
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-3xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Add Writing Service Request</h1>

        <div class="space-y-6">
            <?= $this->Form->create($writingServiceRequest, ['type' => 'file']) ?>
            <?= $this->Form->hidden('user_id', ['value' => $userId]) ?>

            <!-- Service Type -->
            <div>
                <?= $this->Form->label('service_type_display', 'Service Type', [
                    'class' => 'block font-semibold text-gray-700 mb-1'
                ]) ?>
                <?= $this->Form->select(
                    'service_type_display',
                    [
                        '' => 'Please select a service',
                        'creative_writing' => 'Creative Writing',
                        'editing'          => 'Editing',
                        'proofreading'     => 'Proofreading',
                    ],
                    [
                        'id'    => 'service-type',
                        'class' => 'w-full border-gray-300 rounded shadow-sm'
                    ]
                ) ?>
            </div>

            <!-- Word Count Range -->
            <div>
                <?= $this->Form->label('word_count_range_display', 'Word Count Range', [
                    'class' => 'block font-semibold text-gray-700 mb-1'
                ]) ?>
                <?= $this->Form->select(
                    'word_count_range_display',
                    [
                        ''           => 'Please select a word count range',
                        'under_5000' => 'Under 5000',
                        '5000_20000' => '5000 - 20000',
                        '20000_50000'=> '20000 - 50000',
                        '50000_plus' => '50000+',
                    ],
                    [
                        'id'    => 'word-count',
                        'class' => 'w-full border-gray-300 rounded shadow-sm'
                    ]
                ) ?>
            </div>

            <!-- Notes (max 100 characters) -->
            <div>
                <?= $this->Form->label('notes', 'Notes (maximum 100 characters)', [
                    'class' => 'block font-semibold text-gray-700 mb-1'
                ]) ?>
                <?= $this->Form->textarea('notes', [
                    'maxlength' => 100,
                    'rows'      => 3,
                    'class'     => 'w-full border-gray-300 rounded shadow-sm'
                ]) ?>
            </div>

            <!-- File Upload -->
            <?= $this->Form->file('document', [
                'class'  => 'w-full border-gray-300 rounded shadow-sm',
                'accept' => '.pdf,.jpg,.jpeg,.docx'
            ]) ?>
            <p class="text-sm text-gray-500 mb-1">
                Only PDF, JPG, and DOCX files can be uploaded.
            </p>

            <!-- Estimated Price display (hidden if no price) -->
            <div id="estimated-price-wrapper"
                 style="<?= empty($estimatedPrice) ? 'display:none;' : '' ?>">
                <label class="block font-semibold text-gray-700 mb-1">
                    Estimated Price
                </label>
                <input
                    type="text"
                    id="estimated-price"
                    class="w-full bg-gray-100 border-gray-300 rounded shadow-sm"
                    value="<?= !empty($estimatedPrice) ? ('$' . number_format($estimatedPrice, 2)) : '' ?>"
                    readonly
                >
            </div>

            <!-- Buttons -->
            <div class="text-center">
                <!-- Get Estimate Price button (hidden if price exists) -->
                <?= $this->Form->button('Get Estimate Price', [
                    'type'  => 'submit',
                    'name'  => 'action',
                    'value' => 'get_estimate',
                    'id'    => 'btn-get-estimate',
                    'class' => 'bg-green-600 text-white px-6 py-2 rounded',
                    'style' => !empty($estimatedPrice) ? 'display:none;' : ''
                ]) ?>

                <!-- Submit Request button (hidden if no price) -->
                <?= $this->Form->button('Submit Request', [
                    'type'  => 'submit',
                    'name'  => 'action',
                    'value' => 'submit_request',
                    'id'    => 'btn-submit-request',
                    'class' => 'bg-blue-600 text-white px-6 py-2 rounded',
                    'style' => empty($estimatedPrice) ? 'display:none;' : ''
                ]) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>

        <!-- Link: View user requests -->
        <div class="mt-6 text-center">
            <?= $this->Html->link(
                'View My Requests',
                ['controller' => 'WritingServiceRequests', 'action' => 'index'],
                [
                    'class' => 'inline-block border border-gray-400 text-gray-700 px-5 py-2 rounded hover:bg-gray-100 transition'
                ]
            ) ?>
        </div>
    </div>
</div>

<!-- JS: Listen for dropdown changes -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceTypeSelect = document.getElementById('service-type');
        const wordCountSelect   = document.getElementById('word-count');

        const estimateBtn = document.getElementById('btn-get-estimate');
        const submitBtn   = document.getElementById('btn-submit-request');

        const estimatedPriceWrapper = document.getElementById('estimated-price-wrapper');
        const estimatedPriceInput   = document.getElementById('estimated-price');

        function revertToGetEstimate() {
            // Show "Get Estimate Price" button
            if (estimateBtn) {
                estimateBtn.style.display = 'inline-block';
            }

            // Hide "Submit Request" button
            if (submitBtn) {
                submitBtn.style.display = 'none';
            }

            // Hide and clear the price display
            if (estimatedPriceWrapper) {
                estimatedPriceWrapper.style.display = 'none';
            }
            if (estimatedPriceInput) {
                estimatedPriceInput.value = '';
            }
        }

        // Dropdown change listener
        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', revertToGetEstimate);
        }
        if (wordCountSelect) {
            wordCountSelect.addEventListener('change', revertToGetEstimate);
        }
    });
</script>

</body>
</html>
