<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 */
?>
<div class="artworks index content">
    <?= $this->Html->link(__('New Artwork'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Artworks') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('artwork_id') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('image_path') ?></th>
                    <th><?= $this->Paginator->sort('price') ?></th>
                    <th><?= $this->Paginator->sort('availability_status') ?></th>
                    <th><?= $this->Paginator->sort('is_deleted') ?></th>
                    <th><?= $this->Paginator->sort('created_at') ?></th>
                    <th><?= $this->Paginator->sort('updated_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($artworks as $artwork): ?>
                <tr>
                    <td><?= h($artwork->artwork_id) ?></td>
                    <td><?= h($artwork->title) ?></td>
                    <td><?= h($artwork->image_path) ?></td>
                    <td><?= $this->Number->format($artwork->price) ?></td>
                    <td><?= h($artwork->availability_status) ?></td>
                    <td><?= $this->Number->format($artwork->is_deleted) ?></td>
                    <td><?= h($artwork->created_at) ?></td>
                    <td><?= h($artwork->updated_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $artwork->artwork_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $artwork->artwork_id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $artwork->artwork_id], ['confirm' => __('Are you sure you want to delete # {0}?', $artwork->artwork_id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>