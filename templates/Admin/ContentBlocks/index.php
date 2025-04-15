<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ContentBlock> $contentBlocks
 */
?>
<div class="contentBlocks index content">
    <?= $this->Html->link(__('New Content Block'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Content Blocks') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('parent') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('label') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('updated_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contentBlocks as $contentBlock): ?>
                <tr>
                    <td><?= h($contentBlock->parent) ?></td>
                    <td><?= h($contentBlock->slug) ?></td>
                    <td><?= h($contentBlock->label) ?></td>
                    <td><?= h($contentBlock->type) ?></td>
                    <td><?= h($contentBlock->updated_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $contentBlock->content_block_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $contentBlock->content_block_id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $contentBlock->content_block_id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $contentBlock->content_block_id),
                            ]
                        ) ?>
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
