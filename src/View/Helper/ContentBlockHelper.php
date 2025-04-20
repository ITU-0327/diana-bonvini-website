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
    private function _findOrFail(string $slug, string $expectedType): ContentBlock
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
     * Finds a content block by slug without type validation.
     *
     * @param string $slug The unique slug of the content block.
     * @return \App\Model\Entity\ContentBlock
     * @throws \InvalidArgumentException if the block is not found.
     */
    private function _findAny(string $slug): ContentBlock
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
        $rawContent = $this->_findOrFail($slug, 'html')->value ?? '';

        return $this->_processTokens($rawContent) ?? '';
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
        $rawContent = strip_tags($this->_findOrFail($slug, 'text')->value ?? '');

        return $this->_processTokens($rawContent) ?? '';
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
        $path = $this->_findOrFail($slug, 'image')->value;

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
        $block = $this->_findOrFail($slug, 'url');
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
     * Tokens are of the form {{slug}} or {{slug-with-hyphens}}.
     * Supports content blocks of any type (html, text, url, image).
     *
     * @param string $content The content to process.
     * @return string|null The content with tokens replaced.
     */
    private function _processTokens(string $content): ?string
    {
        // Use a regular expression to find tokens (e.g., {{email}} or {{linkedin-link}})
        return preg_replace_callback('/\{\{([\w-]+)}}/', function ($matches) {
            $tokenSlug = $matches[1];
            if ($tokenSlug === 'currentYear') {
                return date('Y');
            }

            try {
                // Find the content block without type restriction
                $block = $this->_findAny($tokenSlug);

                // Handle different block types
                switch ($block->type) {
                    case 'url':
                        // For URL blocks, create a link with the URL as both href and text
                        $url = $block->value;
                        if (!$url) {
                            return $matches[0];
                        }

                        // If the URL is an email address, keep email as text but use mailto: link
                        if (filter_var($url, FILTER_VALIDATE_EMAIL)) {
                            return $this->Html->link($url, 'mailto:' . $url);
                        } else {
                            // For regular URLs, use the URL as both text and link
                            return $this->Html->link($url, $url);
                        }

                    default:
                        // For text and html blocks, just return the value
                        return $block->value ?? $matches[0];
                }
            } catch (InvalidArgumentException) {
                // If the token can't be resolved, return the original token
                return $matches[0];
            }
        }, $content);
    }

    /**
     * Gets a list of available tokens for display in instructions.
     *
     * @param int  $limit  Maximum number of tokens to display per type
     * @return array<string, list<array{slug:string,label:string,type:string}>>
     *         Keys are token‐types (`text`, `html`, `url`, `system`), values are lists of
     *         arrays each with `slug`, `label`, and `type` strings.
     */
    public function getAvailableTokens(int $limit = 10): array
    {
        $contentBlocks = TableRegistry::getTableLocator()->get('ContentBlocks');
        $tokens = [];
        foreach (['text', 'html', 'url'] as $type) {
            $rows = $contentBlocks->find()
                ->select(['slug', 'label', 'type'])
                ->where(['type' => $type, 'parent' => ''])
                ->limit($limit)
                ->enableHydration(false)
                ->toArray();
            if ($rows) {
                $tokens[$type] = $rows;
            }
        }
        // built‐in
        $tokens['system'] = [
            ['slug' => 'currentYear', 'label' => 'Current Year', 'type' => 'system'],
        ];

        return $tokens;
    }
}
