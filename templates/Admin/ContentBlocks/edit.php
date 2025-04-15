<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $contentBlock->content_block_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $contentBlock->content_block_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Content Blocks'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="contentBlocks form content">
            <?= $this->Form->create($contentBlock) ?>
            <fieldset>
                <legend><?= __('Edit Content Block') ?></legend>
                <?php
                    echo $this->Form->control('parent');
                    echo $this->Form->control('slug');
                    echo $this->Form->control('label');
                    echo $this->Form->control('description');
                    echo $this->Form->control('type');
                    echo $this->Form->control('value');
                    echo $this->Form->control('previous_value');
                    echo $this->Form->control('created_at');
                    echo $this->Form->control('updated_at');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
