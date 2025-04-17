<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */
use Cake\ORM\TableRegistry;

$allValues = TableRegistry::getTableLocator()
    ->get('ContentBlocks')
    ->find('list', ['keyField' => 'slug', 'valueField' => 'value'])
    ->toArray();

// JSON‐encode it for your JS
$jsTokenMap = json_encode($allValues, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT);
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

        <?php if (in_array($contentBlock->type, ['text', 'html'])) : ?>
        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-sm font-medium text-blue-700 mb-2">Content Tokens</h3>
            <p class="text-sm text-blue-600 mb-2">
                You can embed dynamic content using tokens in the format <code class="bg-blue-100 px-1 rounded">{{token-name}}</code>
            </p>

            <?php
            $tokens = $this->ContentBlock->getAvailableTokens(5);
            if (!empty($tokens)) :
                ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                <?php foreach ($tokens as $type => $typeTokens) : ?>
                <div>
                    <h4 class="text-xs font-medium text-blue-800 mb-1"><?= ucfirst($type) ?> Tokens:</h4>
                    <ul class="text-xs space-y-1">
                        <?php foreach ($typeTokens as $token) : ?>
                        <li>
                            <code class="bg-blue-100 px-1 rounded cursor-pointer token-example"
                                  data-token="{{<?= $token['slug'] ?>}}"
                                  title="Click to copy"><?= $token['slug'] ?></code>
                            <span class="text-xs text-gray-500"><?= $token['label'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php
        switch ($contentBlock->type) {
            case 'text':
                echo $this->Form->control('value', [
                    'label' => 'Text Value',
                    'type' => 'textarea',
                    'rows' => 6,
                    'class' => 'w-full border border-gray-300 rounded p-2 token-input',
                ]);
                // Always show the preview box:
                echo '<div class="token-preview p-4 bg-gray-50 border border-gray-200 rounded mt-2">';
                echo '<div class="text-xs text-gray-600 mb-1">Live Token Preview:</div>';
                echo '<div class="preview-content text-sm text-gray-700"></div>';
                echo '</div>';
                break;

            case 'html':
                echo $this->Form->control('value', [
                    'label' => 'HTML Content',
                    'type' => 'textarea',
                    'rows' => 10,
                    'class' => 'w-full border border-gray-300 rounded p-2 ckeditor token-input',
                ]);
                echo '<div class="token-preview p-4 bg-gray-50 border border-gray-200 rounded mt-2">';
                echo '<div class="text-xs text-gray-600 mb-1">Live Token Preview:</div>';
                echo '<div class="preview-content text-sm text-gray-700"></div>';
                echo '</div>';
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

<!-- Add styles for token highlights -->
<style>
    .token-highlight {
        display: inline-block;
        padding: 0 4px;
        border-radius: 4px;
        background-color: #eef2ff;
        border: 1px solid #c7d2fe;
        font-family: monospace;
        cursor: pointer;
    }
    .token-url {
        background-color: #ecfdf5;
        border-color: #a7f3d0;
    }
    .token-html {
        background-color: #fef3c7;
        border-color: #fde68a;
    }
    .token-text {
        background-color: #e0f2fe;
        border-color: #bae6fd;
    }
    .token-image {
        background-color: #fce7f3;
        border-color: #fbcfe8;
    }
    .token-system {
        background-color: #f3e8ff;
        border-color: #e9d5ff;
    }
    .token-invalid {
        background-color: #fee2e2;
        border-color: #fecaca;
    }
    .token-preview {
        max-width: 100%;
        overflow-x: auto;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        margin-top: 8px;
        margin-bottom: 8px;
        background-color: #f9fafb;
    }
</style>

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

<!-- Token copy and preview functionality -->
<script>
    const tokenMapping = <?= $jsTokenMap ?>;

    document.addEventListener('DOMContentLoaded', () => {
        // Set up token copying
        document.querySelectorAll('.token-example').forEach(token => {
            token.addEventListener('click', function() {
                const tokenText = this.getAttribute('data-token');
                navigator.clipboard.writeText(tokenText).then(() => {
                    // Show a temporary "copied" tooltip
                    const originalTitle = this.title;
                    this.title = 'Copied!';
                    this.style.backgroundColor = '#d1fae5';

                    setTimeout(() => {
                        this.title = originalTitle;
                        this.style.backgroundColor = '';
                    }, 1500);
                });
            });
        });

        document.querySelectorAll(".token-input").forEach(textarea => {
            // find the corresponding preview-content in the same .token-preview wrapper
            const previewContent = textarea
                .closest("div")           // step up from the textarea's wrapper
                .nextElementSibling       // skip past Form control wrapper
                .querySelector(".preview-content");

            function updatePreview() {
                const text = textarea.value;
                // First, replace any {{slug}} with its mapped value (or leave it alone if not found)
                previewContent.innerHTML = text.replace(/\{\{([\w-]+)}}/g, (__, slug) => {
                    return tokenMapping[slug] ?? `{{${slug}}}`;
                });
            }

            // run once on load
            updatePreview();
            // run on every keystroke
            textarea.addEventListener("input", updatePreview);
        });
    });
</script>
