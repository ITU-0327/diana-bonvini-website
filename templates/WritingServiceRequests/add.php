<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 * @var \Cake\Collection\CollectionInterface|string[] $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Writing Service Requests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="writingServiceRequests form content">
            <?= $this->Form->create($writingServiceRequest) ?>
            <fieldset>
                <legend><?= __('Add Writing Service Request') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('service_type');
                    echo $this->Form->control('word_count_range');
                    echo $this->Form->control('notes');
                    echo $this->Form->control('estimated_price');
                    echo $this->Form->control('final_price');
                    echo $this->Form->control('request_status');
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
