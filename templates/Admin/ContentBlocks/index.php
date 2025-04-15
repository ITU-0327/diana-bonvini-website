<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ContentBlock> $contentBlocks
 * @var array<string> $parents
 */
?>

<div class="container mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Manage Content Blocks']) ?>

    <div class="flex space-x-4 mb-6">
        <button class="filter-btn bg-blue-600 text-white rounded px-4 py-2" data-filter="all">All</button>

        <?php
        // Separate out the global (empty) parent from the rest.
        $globalExists = false;
        $otherParents = [];
        foreach ($parents as $parent) {
            if ($parent === '' || $parent === null) {
                $globalExists = true;
            } else {
                $otherParents[] = $parent;
            }
        }
        // Output the "Global" button if an empty parent exists.
        if ($globalExists) :
            ?>
            <button class="filter-btn bg-gray-200 text-gray-800 rounded px-4 py-2" data-filter="">
                Global
            </button>
            <?php
        endif;
            // Output buttons for the other parents.
        foreach ($otherParents as $parent) :
            $displayName = ucfirst($parent);
            ?>
            <button class="filter-btn bg-gray-200 text-gray-800 rounded px-4 py-2" data-filter="<?= h($parent) ?>">
            <?= h($displayName) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Grid Display of Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($contentBlocks as $block) : ?>
            <div class="content-block relative bg-white shadow rounded-lg p-4 flex flex-col justify-between" data-parent="<?= h($block->parent) ?>">
                <!-- Top Right Type Icon -->
                <div class="absolute top-6 right-5 text-2xl text-gray-500" title="<?= ucfirst($block->type) ?>">
                    <?php
                    echo match ($block->type) {
                        'text' => '<i class="fa-solid fa-font"></i>',
                        'url' => '<i class="fa-solid fa-link"></i>',
                        'image' => '<i class="fa-solid fa-image"></i>',
                        'html' => '<i class="fa-solid fa-code"></i>',
                        default => '<i class="fa-solid fa-file"></i>',
                    }; ?>
                </div>

                <!-- Block Heading -->
                <div>
                    <h2 class="text-xl font-semibold"><?= h($block->label) ?></h2>
                    <p class="text-sm text-gray-600"><?= h($block->description) ?></p>
                </div>
                <!-- Block Value (short preview) -->
                <div class="mt-3">
                    <?php if ($block->type === 'image') : ?>
                        <?= $this->ContentBlock->image($block->slug, ['class' => 'w-full h-48 object-cover rounded']) ?>
                    <?php elseif ($block->type === 'url') : ?>
                        <?= $this->ContentBlock->url($block->slug, ['class' => 'text-blue-600 hover:underline']) ?>
                    <?php else : ?>
                        <p class="text-sm"><?= h($block->value) ?></p>
                    <?php endif; ?>
                </div>
                <!-- Action Buttons -->
                <div class="mt-4 mr-2 flex justify-end items-center space-x-4 text-sm">
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-pencil"></i>',
                        ['action' => 'edit', $block->content_block_id],
                        [
                            'escape' => false,
                            'class' => 'text-blue-600 hover:text-blue-800',
                            'title' => 'Edit',
                        ],
                    ) ?>
                    <?php if (!empty($block->previous_value)) : ?>
                        <?= $this->Form->postLink(
                            '<i class="fa-solid fa-rotate-left"></i>',
                            ['action' => 'revert', $block->content_block_id],
                            [
                                'confirm' => 'Are you sure you want to retrieve the previous value?',
                                'escape' => false,
                                'class' => 'text-blue-600 hover:text-blue-800',
                                'title' => 'Retrieve previous value',
                            ],
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Select all filter buttons and content blocks.
        const filterButtons = document.querySelectorAll(".filter-btn");
        const contentBlocks = document.querySelectorAll(".content-block");

        filterButtons.forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                // Remove an "active" styling from all buttons.
                filterButtons.forEach(btn => btn.classList.remove("active"));
                // Mark this button as active.
                this.classList.add("active");

                const filterValue = this.getAttribute("data-filter");
                contentBlocks.forEach(block => {
                    const blockParent = block.getAttribute("data-parent");
                    // Show block if filter is "all" or matches.
                    if (filterValue === "all" || blockParent === filterValue) {
                        block.style.display = "";
                    } else {
                        block.style.display = "none";
                    }
                });
            });
        });
    });
</script>
