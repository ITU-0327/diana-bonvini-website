<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */
?>

<div class="container mx-auto px-6 py-10">
    <?= $this->element('page_title', ['title' => 'Edit Content Block']) ?>

    <div class="w-full sm:w-3/4 md:w-2/3 lg:w-1/2 max-w-3xl mx-auto bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <p class="text-xl font-semibold text-gray-800"><?= h($contentBlock->label) ?></p>
            <p class="mt-1 text-sm text-gray-600"><?= h($contentBlock->description) ?></p>
        </div>

        <?= $this->Form->create($contentBlock, [
            'type' => 'file',
            'class' => 'space-y-6',
        ]) ?>

        <?php
        switch ($contentBlock->type) {
            case 'text':
                // Simple textarea
                echo $this->Form->control('value', [
                    'label' => 'Text Value',
                    'type' => 'textarea',
                    'rows' => 6,
                    'class' => 'w-full border border-gray-300 rounded p-2',
                ]);
                break;

            case 'html':
                echo $this->Form->control('value', [
                    'label' => 'HTML Content',
                    'type' => 'textarea',
                    'rows' => 10,
                    'class' => 'w-full border border-gray-300 rounded p-2 ckeditor',
                ]);
                break;

            case 'image':
                // Show current image preview if exists
                if ($contentBlock->value) {
                    echo '<div class="mb-4 text-center">';
                    echo $this->Html->image($contentBlock->value, [
                        'class' => 'max-w-full object-contain rounded mx-auto',
                        'alt' => $contentBlock->label,
                    ]);
                    echo '</div>';
                }
                // File input
                echo $this->Form->control('value', [
                    'label' => 'Upload New Image',
                    'type' => 'file',
                    'class' => 'w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                ]);
                break;

            case 'url':
                // Single-line URL input
                echo $this->Form->control('value', [
                    'label' => 'URL',
                    'type' => 'url',
                    'class' => 'w-full border border-gray-300 rounded p-2',
                ]);
                break;

            default:
                // Fallback to textarea
                echo $this->Form->control('value', [
                    'label' => 'Value',
                    'type' => 'textarea',
                    'rows' => 6,
                    'class' => 'w-full border border-gray-300 rounded p-2',
                ]);
                break;
        }
        ?>

        <div class="flex items-center justify-end space-x-4">
            <?= $this->Form->button(__('Save'), [
                'class' => 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700',
            ]) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], [
                'class' => 'text-gray-600 hover:underline',
            ]) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<?php if ($contentBlock->type === 'html') : ?>
    <!-- CKEditor 5 Classic build via CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editors = document.querySelectorAll('textarea.ckeditor');
            editors.forEach(textarea => {
                ClassicEditor
                    .create(textarea, {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                            'insertTable', 'blockQuote', '|',
                            'undo', 'redo'
                        ],
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                            ]
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            });
        });
    </script>

<?php endif; ?>
