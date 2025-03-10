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
            <?= $this->Html->link(__('Edit Artwork'), ['action' => 'edit', $artwork->artwork_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Artwork'), ['action' => 'delete', $artwork->artwork_id], ['confirm' => __('Are you sure you want to delete # {0}?', $artwork->artwork_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Artworks'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Artwork'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="artworks view content">
            <h3><?= h($artwork->title) ?></h3>
            <table>
                <tr>
                    <th><?= __('Artwork Id') ?></th>
                    <td><?= h($artwork->artwork_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Title') ?></th>
                    <td><?= h($artwork->title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Image Path') ?></th>
                    <td><?= h($artwork->image_path) ?></td>
                </tr>
                <tr>
                    <th><?= __('Availability Status') ?></th>
                    <td><?= h($artwork->availability_status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Price') ?></th>
                    <td><?= $this->Number->format($artwork->price) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Deleted') ?></th>
                    <td><?= $this->Number->format($artwork->is_deleted) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created At') ?></th>
                    <td><?= h($artwork->created_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Updated At') ?></th>
                    <td><?= h($artwork->updated_at) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($artwork->description)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>