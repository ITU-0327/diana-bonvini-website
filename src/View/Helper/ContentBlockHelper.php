<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\ContentBlock;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
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
            throw new InvalidArgumentException("Content block '$slug' not found.");
        }

        if ($expectedType && $block->type !== $expectedType) {
            throw new InvalidArgumentException("Content block '$slug' type is '$block->type', expected '$expectedType'.");
        }

        return $block;
    }

    /**
     * Renders an HTML block.
     *
     * Usage: $this->ContentBlock->html('test');
     *
     * @param string $slug The unique slug of the content block.
     * @return string The HTML content.
     */
    public function html(string $slug): string
    {
        $rawContent = $this->findOrFail($slug, 'html')->value ?? '';

        return $this->processTokens($rawContent, 'html') ?? '';
    }

    /**
     * Renders a Text block.
     *
     * Usage: $this->ContentBlock->text('test');
     *
     * @param string $slug The unique slug of the content block.
     * @return string The escaped text content.
     */
    public function text(string $slug): string
    {
        $rawContent = strip_tags($this->findOrFail($slug, 'text')->value ?? '');

        return $this->processTokens($rawContent, 'text') ?? '';
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
     * Renders a URL block.
     *
     * Usage: $this->ContentBlock->url('link-slug', ['text' => 'Visit Website', 'class' => 'custom-class']);
     *
     * If the URL value is an email address, it will automatically use the mailto: protocol.
     * If no 'text' option is provided, the link text will be the URL itself.
     *
     * @param string $slug The unique slug of the content block.
     * @param array<string, mixed> $options Options for rendering the link.
     *     Optionally, 'text' can be provided to specify the link text.
     * @return string|null The generated HTML link or null if there is no URL.
     */
    public function url(string $slug, array $options = []): ?string
    {
        $block = $this->findOrFail($slug, 'url');
        $url = $block->value;
        if (!$url) {
            return null;
        }

        // Determine the link text.
        $linkText = $options['text'] ?? $url;
        // Remove the text key as it is not an option for the Html->link() helper.
        unset($options['text']);

        // If the URL is a valid email address, prepend "mailto:".
        if (filter_var($url, FILTER_VALIDATE_EMAIL)) {
            $url = 'mailto:' . $url;
        }

        // Return the generated link using the Html helper.
        return $this->Html->link($linkText, $url, $options);
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
            if ($tokenSlug === 'currentYear') {
                return date('Y');
            }

            // Fetch the block value for this token
            $replacement = $this->findOrFail($tokenSlug, $expectedType)->value;

            return $replacement ?? $matches[0];
        }, $content);
    }
}
