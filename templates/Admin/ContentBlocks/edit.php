<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */
use Cake\ORM\TableRegistry;

// Build maps for JS
$allValues = TableRegistry::getTableLocator()
    ->get('ContentBlocks')
    ->find('list', ['keyField' => 'slug','valueField' => 'value'])
    ->toArray();
$allTypes = TableRegistry::getTableLocator()
    ->get('ContentBlocks')
    ->find('list', ['keyField' => 'slug','valueField' => 'type'])
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
                        <?php foreach ($this->ContentBlock->getAvailableTokens() as $type => $list) : ?>
                            <?php foreach ($list as $token) : ?>
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
                    $this->Form->control('value', [
                        'label' => 'URL',
                        'type' => 'url',
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
                <div class="token-preview bg-gray-50 border border-gray-200 rounded p-4 font-mono text-sm space-y-2">
                    <div class="text-gray-600 text-xs">Live Preview:</div>
                    <div class="preview-content whitespace-pre-wrap text-gray-800"></div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <?= $this->Form->button(__('Save'), ['class' => 'bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'text-gray-600 hover:underline']) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<!-- CKEditor for HTML -->
<?php if ($contentBlock->type === 'html') : ?>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('textarea.ckeditor').forEach(el => {
                ClassicEditor.create(el, {
                    toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','insertTable','blockQuote','|','undo','redo']
                }).catch(console.error);
            });
        });
    </script>
<?php endif; ?>

<!-- Token JS (copy & live replace) -->
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

        // Live preview
        document.querySelectorAll('.token-input').forEach(textarea => {
            const controlDiv = textarea.closest('.input') || textarea.parentElement;
            const previewBox = controlDiv.nextElementSibling;
            if (!previewBox?.classList.contains('token-preview')) return;

            const previewContent = previewBox.querySelector('.preview-content');
            if (!previewContent) return;

            function updatePreview() {
                const html = textarea.value.replace(
                    /\{\{([\w-]+)}}/g,
                    (whole, slug) => {
                        const val = tokenMapping[slug] ?? whole;
                        const cls = ({
                            text:  'token-text',
                            html:  'token-html',
                            url:   'token-url',
                            system:'token-system',
                        })[ tokenTypes[slug] ] || 'token-highlight';
                        return `<span class="${cls}">${val}</span>`;
                    }
                );
                previewContent.innerHTML = html || '<span class="text-gray-400">No tokens yet…</span>';
            }

            textarea.addEventListener('input', updatePreview);
            updatePreview();
        });
    });
</script>
