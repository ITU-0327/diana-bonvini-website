<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 * @var \Cake\Collection\CollectionInterface $messages
 */
?>

<div class="flex flex-col lg:flex-row gap-6 p-6">
    <!-- Sidebar -->
    <aside class="w-full lg:w-1/4">
        <div class="bg-white shadow rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-700 mb-4"><?= __('Actions') ?></h4>
            <div class="flex flex-col space-y-2">
                <?= $this->Html->link(__('Edit This Request'), ['action' => 'edit', $writingServiceRequest->request_id], ['class' => 'text-blue-600 hover:underline']) ?>
                <?= $this->Form->postLink(__('Delete This Request'), ['action' => 'delete', $writingServiceRequest->request_id], ['confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->request_id), 'class' => 'text-red-600 hover:underline']) ?>
                <?= $this->Html->link(__('View My Requests'), ['action' => 'index'], ['class' => 'text-blue-600 hover:underline']) ?>
                <?= $this->Html->link(__('Make A New Request'), ['action' => 'add'], ['class' => 'text-blue-600 hover:underline']) ?>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="w-full lg:w-3/4">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-6"><?= h($writingServiceRequest->service_type) ?> Request Details</h3>

            <!-- Request info -->
            <table class="w-full text-left border border-gray-200 rounded overflow-hidden mb-8">
                <tbody class="divide-y divide-gray-200">
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Request ID') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->request_id) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Service Type') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->service_type) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Word Count Range') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->word_count_range) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Notes') ?></th>
                    <td class="p-3"><?= nl2br(h($writingServiceRequest->notes)) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Status') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->request_status) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Final Price') ?></th>
                    <td class="p-3"><?= $writingServiceRequest->final_price === null ? 'N/A' : $this->Number->format($writingServiceRequest->final_price) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Created At') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->created_at) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Updated At') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->updated_at) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Document') ?></th>
                    <td class="p-3">
                        <?php if (!empty($writingServiceRequest->document)): ?>
                            <?= $this->Html->link('View Document', '/' . $writingServiceRequest->document, ['target' => '_blank', 'class' => 'text-blue-500 hover:underline']) ?>
                        <?php else: ?>
                            <span class="text-gray-500 italic">No Document</span>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Message thread -->
            <h4 class="text-xl font-semibold mb-4">Conversation</h4>
            <?php if (!empty($messages) && $messages->count() > 0): ?>
                <div class="space-y-4 mb-6">
                    <?php foreach ($messages as $msg): ?>
                        <div class="p-3 border rounded <?= $msg->sender_type === 'admin' ? 'bg-blue-50' : 'bg-green-50' ?>">
                            <strong><?= $msg->sender_type === 'admin' ? 'Admin' : 'You' ?>:</strong>
                            <p><?= nl2br(h($msg->message)) ?></p>
                            <small class="text-gray-500"><?= h($msg->created_at) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 mb-6">No messages yet.</p>
            <?php endif; ?>

            <!-- User reply -->
            <h4 class="text-xl font-semibold mb-2">Reply to Admin</h4>
            <?= $this->Form->create(null, ['url' => ['action' => 'view', $writingServiceRequest->request_id]]) ?>
            <div class="space-y-4">
                <?= $this->Form->textarea('reply_message', [
                    'label' => false,
                    'placeholder' => 'Type your reply...',
                    'rows' => 4,
                    'class' => 'w-full border border-gray-300 rounded p-2'
                ]) ?>
                <?= $this->Form->button('Send Message', [
                    'class' => 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'
                ]) ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
