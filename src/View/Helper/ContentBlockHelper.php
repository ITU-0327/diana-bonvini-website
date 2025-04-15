<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\ContentBlock;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Cake\View\Helper\HtmlHelper;
use InvalidArgumentException;

/**
 * ContentBlock helper
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class ContentBlockHelper extends Helper
{
    /**
     * @var array<string>
     */
    public array $helpers = ['Html'];

    /**
     * Explicitly declare the Html helper property so that static analyzers recognize it.
     *
     * @var \Cake\View\Helper\HtmlHelper
     */
    public HtmlHelper $Html;

    /**
     * Fetches a content block by slug and verifies its type.
     *
     * @param string $slug The unique slug of the content block.
     * @param string $expectedType Expected type ('html', 'text', or 'image').
     * @return \App\Model\Entity\ContentBlock
     * @throws \InvalidArgumentException if the block is not found or its type doesn't match.
     */
    private function findOrFail(string $slug, string $expectedType): ContentBlock
    {
        /**
         * @var \App\Model\Table\ContentBlocksTable $contentBlocks
         */
        $contentBlocks = TableRegistry::getTableLocator()->get('ContentBlocks');
        $block = $contentBlocks->find()
            ->where(['slug' => $slug])
            ->first();

        if (!$block) {
            throw new InvalidArgumentException("Content block '{$slug}' not found.");
        }

        if ($expectedType && $block->type !== $expectedType) {
            throw new InvalidArgumentException("Content block '{$slug}' type is '{$block->type}', expected '{$expectedType}'.");
        }

        return $block;
    }

    /**
     * Renders an HTML block.
     *
     * Usage: $this->ContentBlock->html('test');
     *
     * @param string $slug The unique slug of the content block.
     * @return string|null The HTML content or null if not found.
     */
    public function html(string $slug): ?string
    {
        $rawContent = $this->findOrFail($slug, 'html')->value ?? '';

        return $this->processTokens($rawContent, 'html');
    }

    /**
     * Renders a Text block.
     *
     * Usage: $this->ContentBlock->text('test');
     *
     * @param string $slug The unique slug of the content block.
     * @return string|null The escaped text content.
     */
    public function text(string $slug): ?string
    {
        $rawContent = strip_tags($this->findOrFail($slug, 'text')->value ?? '');

        return $this->processTokens($rawContent, 'text');
    }

    /**
     * Renders an Image block.
     *
     * Usage: $this->ContentBlock->image('test', ['alt' => 'Logo']);
     *
     * @param string $slug The unique slug of the content block.
     * @param array<string, mixed> $options Options passed to the Html->image() helper.
     * @return string|null The HTML image tag.
     */
    public function image(string $slug, array $options = []): ?string
    {
        $path = $this->findOrFail($slug, 'image')->value;

        return $path ? $this->Html->image($path, $options) : null;
    }

    /**
     * Replace tokens in the given content with corresponding block values.
     *
     * Tokens are of the form {{slug}}.
     *
     * @param string $content The content to process.
     * @param string $expectedType The expected type ('html', 'text', or 'image') for the tokens.
     * @return string|null The content with tokens replaced.
     */
    public function processTokens(string $content, string $expectedType): ?string
    {
        // Use a regular expression to find tokens (e.g., {{email}})
        return preg_replace_callback('/\{\{(\w+)}}/', function ($matches) use ($expectedType) {
            $tokenSlug = $matches[1];
            // Fetch the block value for this token
            $replacement = $this->findOrFail($tokenSlug, $expectedType)->value;

            return $replacement ?? $matches[0];
        }, $content);
    }
}
