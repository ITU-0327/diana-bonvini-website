<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $artwork->artwork_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $artwork->artwork_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Artworks'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="artworks form content">
            <?= $this->Form->create($artwork) ?>
            <fieldset>
                <legend><?= __('Edit Artwork') ?></legend>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('description');
                    echo $this->Form->control('image_path');
                    echo $this->Form->control('price');
                    echo $this->Form->control('availability_status');
                    echo $this->Form->control('is_deleted');
                    echo $this->Form->control('created_at');
                    echo $this->Form->control('updated_at');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
