<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ContentBlock> $contentBlocks
 * @var array<string> $parents
 */

$this->assign('title', __('Content Blocks Management'));
$this->Html->script('https://cdn.tailwindcss.com', ['block' => 'script']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Content Blocks Management</h6>
                </div>

                <div class="card-body">
                    <!-- Filter Buttons -->
                    <div class="mb-4">
                        <div class="btn-group" role="group" aria-label="Filter content blocks">
                            <button type="button" class="filter-btn btn btn-primary" data-filter="all">All</button>

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
                                <button type="button" class="filter-btn btn btn-outline-secondary" data-filter="">
                                    Global
                                </button>
                                <?php
                            endif;
                                // Output buttons for the other parents.
                            foreach ($otherParents as $parent) :
                                // Humanize slug: replace hyphens with spaces and uppercase words
                                $displayName = ucwords(str_replace('-', ' ', $parent));
                                ?>
                                <button type="button" class="filter-btn btn btn-outline-secondary" data-filter="<?= h($parent) ?>">
                                    <?= h($displayName) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Grid Display of Cards -->
                    <div class="row">
                        <?php foreach ($contentBlocks as $block) : ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="content-block card shadow h-100" data-parent="<?= h($block->parent) ?>">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary"><?= h($block->label) ?></h6>
                                        <div class="text-muted" title="<?= ucfirst($block->type) ?>">
                                            <?php
                                            echo match ($block->type) {
                                                'text' => '<i class="fas fa-font"></i>',
                                                'url' => '<i class="fas fa-link"></i>',
                                                'image' => '<i class="fas fa-image"></i>',
                                                'html' => '<i class="fas fa-code"></i>',
                                                default => '<i class="fas fa-file"></i>',
                                            }; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text small text-muted mb-3"><?= h($block->description) ?></p>

                                        <!-- Block Value Preview -->
                                        <div class="content-preview cms-content">
                                            <?php if ($block->type === 'image') : ?>
                                                <?= $this->ContentBlock->image($block->slug, ['class' => 'img-fluid rounded mx-auto d-block', 'style' => 'max-height: 150px;', 'alt' => $block->label]) ?>
                                            <?php elseif ($block->type === 'url') : ?>
                                                <?= $this->ContentBlock->url($block->slug, ['class' => 'text-primary']) ?>
                                            <?php elseif ($block->type === 'html') : ?>
                                                <div class="small">
                                                    <?= $this->ContentBlock->html($block->slug) ?>
                                                </div>
                                            <?php elseif ($block->type === 'text') : ?>
                                                <p class="small"><?= $this->ContentBlock->text($block->slug) ?></p>
                                            <?php else : ?>
                                                <p class="small"><?= h($block->value) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex justify-content-end">
                                        <!-- Action Buttons -->
                                        <?= $this->Html->link(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            ['action' => 'edit', $block->content_block_id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn btn-sm btn-outline-primary mx-1',
                                                'title' => 'Edit',
                                            ],
                                        ) ?>
                                        <?php if (!empty($block->previous_value)) : ?>
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-history"></i>',
                                                ['action' => 'revert', $block->content_block_id],
                                                [
                                                    'confirm' => 'Are you sure you want to retrieve the previous value?',
                                                    'escape' => false,
                                                    'class' => 'btn btn-sm btn-outline-danger mx-1',
                                                    'title' => 'Retrieve previous value',
                                                ],
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
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
                // Remove active class from all buttons
                filterButtons.forEach(btn => {
                    btn.classList.remove("btn-primary");
                    btn.classList.remove("active");
                    if (!btn.classList.contains("btn-outline-secondary")) {
                        btn.classList.add("btn-outline-secondary");
                    }
                });

                // Add active class to clicked button
                this.classList.remove("btn-outline-secondary");
                this.classList.add("btn-primary");
                this.classList.add("active");

                const filterValue = this.getAttribute("data-filter");
                contentBlocks.forEach(block => {
                    const blockParent = block.getAttribute("data-parent");
                    const parentElement = block.closest('.col-lg-4');

                    // Show block if filter is "all" or matches
                    if (filterValue === "all" || blockParent === filterValue) {
                        parentElement.style.display = "";
                    } else {
                        parentElement.style.display = "none";
                    }
                });
            });
        });

        // Set "All" filter as active by default
        document.querySelector('.filter-btn[data-filter="all"]').click();
    });
</script>
