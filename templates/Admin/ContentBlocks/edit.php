<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContentBlock $contentBlock
 */
?>

<div class="container mx-auto px-6 py-10">
    <?= $this->element('page_title', ['title' => 'Edit Content Block Value']) ?>

    <div class="max-w-lg mx-auto bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <p class="text-xl font-semibold text-gray-800"><?= h($contentBlock->label) ?></p>
            <p class="mt-1 text-sm text-gray-600"><?= h($contentBlock->description) ?></p>
        </div>

        <?= $this->Form->create($contentBlock, ['class' => 'space-y-6']) ?>
        <?php
        echo $this->Form->control('value', [
            'label' => 'Value',
            'type' => 'textarea',
            'rows' => 4,
            'class' => 'w-full border border-gray-300 rounded p-2',
        ]);
        ?>
        <div class="flex items-center justify-end space-x-4">
            <?= $this->Form->button(__('Save'), [
                'class' => 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700',
            ]) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], [
                'class' => 'text-gray-600 hover:underline',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
