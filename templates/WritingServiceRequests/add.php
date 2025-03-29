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

            <!-- Submit button -->
            <div class="text-center">
                <?= $this->Form->button('Submit Request', [
                    'type'  => 'submit',
                    'class' => 'bg-blue-600 text-white px-6 py-2 rounded'
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

</body>
</html>
