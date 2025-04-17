<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */
?>
<div class="container mx-auto px-6 py-12">
    <?= $this->element('page_title', ['title' => 'Add Content Block']) ?>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-8 py-6 space-y-6">

            <?= $this->Form->create($contentBlock, [
                'type' => 'file',
                'class' => 'space-y-6',
            ]) ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?= $this->Form->control('parent', [
                    'label' => 'Parent (leave blank for global)',
                    'class' => 'w-full border-gray-300 rounded p-2',
                    'empty' => '-- none --',
                ]) ?>

                <?= $this->Form->control('slug', [
                    'label' => 'Slug',
                    'class' => 'w-full border-gray-300 rounded p-2',
                ]) ?>

                <?= $this->Form->control('label', [
                    'label' => 'Label',
                    'class' => 'w-full border-gray-300 rounded p-2',
                ]) ?>

                <?= $this->Form->control('description', [
                    'label' => 'Description',
                    'type' => 'textarea',
                    'rows' => 2,
                    'class' => 'w-full border-gray-300 rounded p-2',
                ]) ?>

                <div class="md:col-span-2">
                    <?= $this->Form->control('type', [
                        'label' => 'Type',
                        'type' => 'select',
                        'options' => [
                            'text' => 'Text',
                            'html' => 'HTML',
                            'url' => 'URL',
                            'image' => 'Image',
                        ],
                        'empty' => '-- Select type --',
                        'class' => 'w-full border-gray-300 rounded p-2 token-type-selector',
                    ]) ?>
                </div>
            </div>

            <!-- Dynamic Value Input -->
            <div id="value-sections" class="space-y-4">
                <!-- Text -->
                <div data-type="text" class="hidden">
                    <?= $this->Form->control('value', [
                        'label'    => 'Text Value',
                        'type'     => 'textarea',
                        'rows'     => 6,
                        'class'    => 'w-full border-gray-300 rounded p-2 token-input',
                        'disabled' => true,
                    ]) ?>
                </div>

                <!-- HTML -->
                <div data-type="html" class="hidden">
                    <?= $this->Form->control('value', [
                        'label'    => 'HTML Content',
                        'type'     => 'textarea',
                        'rows'     => 8,
                        'class'    => 'w-full border-gray-300 rounded p-2 ckeditor token-input',
                        'disabled' => true,
                    ]) ?>
                </div>

                <!-- URL -->
                <div data-type="url" class="hidden">
                    <?= $this->Form->control('value', [
                        'label'    => 'URL',
                        'type'     => 'url',
                        'class'    => 'w-full border-gray-300 rounded p-2',
                        'disabled' => true,
                    ]) ?>
                </div>

                <!-- Image -->
                <div data-type="image" class="hidden">
                    <?= $this->Form->control('value', [
                        'label'    => 'Upload Image',
                        'type'     => 'file',
                        'class'    => '...your file‑input classes...',
                        'disabled' => true,
                    ]) ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <?= $this->Form->button(__('Save'), ['class' => 'bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn-secondary']) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selector = document.querySelector('.token-type-selector');
        const sections = document.querySelectorAll('#value-sections > div');

        function showSection(type) {
            sections.forEach(sec => {
                const active = sec.getAttribute('data-type') === type;
                sec.classList.toggle('hidden', !active);

                // Enable/disable all form elements inside
                sec.querySelectorAll('input,textarea,select').forEach(el => {
                    el.disabled = !active;
                });
            });
        }

        // On load, if a type is pre-selected:
        if (selector.value) {
            showSection(selector.value);
        }

        // On change:
        selector.addEventListener('change', () => {
            showSection(selector.value);
        });

        selector.addEventListener('change', () => {
            if (selector.value === 'html') {
                document.querySelectorAll('textarea.ckeditor').forEach(el => {
                    if (!el._ckeditorInitialized) {
                        ClassicEditor.create(el, {
                            toolbar: [
                                'heading','|','bold','italic','link',
                                'bulletedList','numberedList','|',
                                'insertTable','blockQuote','|','undo','redo'
                            ]
                        }).then(editor => {
                                el._ckeditorInitialized = true
                                editor.ui.view.editable.element.style.minHeight = '300px';
                            })
                            .catch(console.error);
                    }
                });
            }
        });
    });
</script>
