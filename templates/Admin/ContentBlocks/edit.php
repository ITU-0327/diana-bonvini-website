<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */

$this->assign('title', __('Edit Content Block'));
$this->Html->script('https://cdn.tailwindcss.com', ['block' => 'script']);

use Cake\ORM\TableRegistry;

// Build maps for JS
$allValues = TableRegistry::getTableLocator()
    ->get('ContentBlocks')
    ->find('list', keyField: 'slug', valueField: 'value')
    ->toArray();
$allTypes = TableRegistry::getTableLocator()
    ->get('ContentBlocks')
    ->find('list', keyField: 'slug', valueField: 'type')
    ->toArray();
?>

<div class="container mx-auto px-6 py-12">
    <?= $this->element('page_title', ['title' => 'Edit Content Block']) ?>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-8 py-6 space-y-6">

            <!-- Label & Description -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= h($contentBlock->label) ?></h2>
                <p class="mt-2 text-gray-600"><?= h($contentBlock->description) ?></p>
            </div>

            <?= $this->Form->create($contentBlock, ['type' => 'file','class' => 'space-y-6']) ?>

            <!-- Token Instructions (collapsible) -->
            <?php if (in_array($contentBlock->type, ['text','html'])) : ?>
                <details class="group bg-teal-50 border-l-4 border-teal-400 rounded p-4">
                    <summary class="cursor-pointer font-semibold text-teal-700 group-open:text-teal-900">
                        Tokens &mdash; click to expand
                    </summary>
                    <p class="mt-2 text-teal-600 text-sm">
                        Embed dynamic content using tokens like <code class="bg-teal-100 px-1 rounded">{{token-name}}</code>.
                        Click any pill to copy.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <?php foreach ($this->ContentBlock->getAvailableTokens() as $_list) : ?>
                            <?php foreach ($_list as $token) : ?>
                                <span
                                    class="token-example cursor-pointer select-none bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-xs font-medium"
                                    data-token="{{<?= h($token['slug']) ?>}}"
                                    title="Click to copy"><?= h($token['slug']) ?></span>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>

            <?php
            switch ($contentBlock->type) {
                case 'text':
                    echo $this->Form->control('value', [
                        'label' => 'Text Value',
                        'type' => 'textarea',
                        'rows' => 6,
                        'class' => 'w-full border border-gray-300 rounded p-3 token-input',
                    ]);
                    break;

                case 'html':
                    echo $this->Form->control('value', [
                        'label' => 'HTML Content',
                        'type' => 'textarea',
                        'rows' => 10,
                        'class' => 'w-full border border-gray-300 rounded p-3 ckeditor token-input',
                    ]);
                    break;

                case 'image':
                    if ($contentBlock->value) {
                        echo '<div class="text-center mb-4">';
                        echo $this->Html->image($contentBlock->value, [
                            'class' => 'inline-block max-w-full max-h-96 object-contain rounded',
                            'alt' => $contentBlock->label,
                        ]);
                        echo '</div>';
                    }

                    echo $this->Form->control('value', [
                        'label' => 'Upload New Image',
                        'type' => 'file',
                        'class' => 'block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-teal-100 file:text-teal-800 hover:file:bg-teal-200',
                    ]);
                    break;

                case 'url':
                    echo $this->Form->control('value', [
                        'label' => 'URL',
                        'type' => 'text',
                        'class' => 'w-full border border-gray-300 rounded p-3',
                    ]);
                    break;

                default:
                    echo $this->Form->control('value', [
                        'label' => 'Value',
                        'type' => 'textarea',
                        'rows' => 6,
                        'class' => 'w-full border border-gray-300 rounded p-3',
                    ]);
                    break;
            }
            ?>

            <!-- Live Preview -->
            <?php if (in_array($contentBlock->type, ['text','html'])) : ?>
                <div class="live-preview-wrapper hidden space-y-2">
                    <div>
                        <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            Live Preview
                        </span>
                    </div>
                    <div class="token-preview bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <div class="cms-content preview-content whitespace-pre-wrap text-gray-800 space-y-4"></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <?= $this->Form->button(__('Save'), ['class' => 'bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn-secondary']) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<!-- Token JS (copy & live replace) -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
<script>
    const tokenMapping = <?= json_encode($allValues) ?>;
    const tokenTypes   = <?= json_encode($allTypes) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        // Copy on click
        document.querySelectorAll('.token-example').forEach(el => {
            el.addEventListener('click', () => {
                navigator.clipboard.writeText(el.dataset.token);
                el.classList.add('bg-teal-200');
                setTimeout(() => el.classList.remove('bg-teal-200'), 1000);
            });
        });

        // Live preview for plain <textarea>
        document.querySelectorAll('.token-input:not(.ckeditor)').forEach(textarea => {
            // start at the wrapper around the textarea
            const controlDiv = textarea.closest('.input') || textarea.parentElement;
            // The next sibling is our live-preview-wrapper
            const wrapper    = controlDiv.nextElementSibling;
            if (!wrapper || !wrapper.classList.contains('live-preview-wrapper')) return;
            const previewContent = wrapper.querySelector('.preview-content');

            function updatePlainPreview() {
                const text = textarea.value;
                const hasToken = /\{\{[\w-]+}}/.test(text);
                if (!hasToken) {
                    wrapper.classList.add('hidden');
                } else {
                    wrapper.classList.remove('hidden');
                    previewContent.innerHTML = text.replace(
                        /\{\{([\w-]+)}}/g,
                        (m, slug) => {
                            const val = tokenMapping[slug] ?? m;
                            const cls = {
                                text: 'token-text',
                                html: 'token-html',
                                url:  'token-url',
                            }[tokenTypes[slug]] || 'token-system';
                            return `<span class="${cls}">${val}</span>`;
                        }
                    );
                }
            }

            textarea.addEventListener('input', updatePlainPreview);
            updatePlainPreview();
        });

        // Live preview for CKEditor instances
        document.querySelectorAll('textarea.ckeditor').forEach(textarea => {
            const controlDiv = textarea.closest('.input') || textarea.parentElement;
            const wrapper    = controlDiv.nextElementSibling;
            if (!wrapper || !wrapper.classList.contains('live-preview-wrapper')) return;
            const previewContent = wrapper.querySelector('.preview-content');

            ClassicEditor
                .create(textarea, {
                    toolbar: [
                        'heading','|','bold','italic','link',
                        'bulletedList','numberedList','|',
                        'insertTable','blockQuote','|','undo','redo'
                    ]
                })
                .then(editor => {
                    function updateHtmlPreview() {
                        const html = editor.getData();
                        const hasToken = /\{\{[\w-]+}}/.test(html);
                        if (!hasToken) {
                            wrapper.classList.add('hidden');
                        } else {
                            wrapper.classList.remove('hidden');
                            previewContent.innerHTML = html.replace(
                                /\{\{([\w-]+)}}/g,
                                (m, slug) => {
                                    const val = tokenMapping[slug] ?? m;
                                    const cls = {
                                        text: 'token-text',
                                        html: 'token-html',
                                        url:  'token-url',
                                    }[tokenTypes[slug]] || 'token-system';
                                    return `<span class="${cls}">${val}</span>`;
                                }
                            );
                        }
                    }

                    updateHtmlPreview();
                    editor.model.document.on('change:data', updateHtmlPreview);
                })
                .catch(console.error);
        });
    });
</script>
