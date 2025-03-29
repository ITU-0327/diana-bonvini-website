<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
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

    <!-- Content -->
    <div class="w-full lg:w-3/4">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-6"><?= h($writingServiceRequest->service_type) ?></h3>
            <table class="w-full text-left border border-gray-200 rounded overflow-hidden">
                <tbody class="divide-y divide-gray-200">
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Request Id') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->request_id) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('User') ?></th>
                    <td class="p-3">
                        <?= $writingServiceRequest->hasValue('user') ? $this->Html->link($writingServiceRequest->user->first_name, ['controller' => 'Users', 'action' => 'view', $writingServiceRequest->user->user_id], ['class' => 'text-blue-500 hover:underline']) : '' ?>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Service Type') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->service_type) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Word Count Range') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->word_count_range) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Notes') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->notes) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Request Status') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->request_status) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Final Price') ?></th>
                    <td class="p-3"><?= $writingServiceRequest->final_price === null ? '' : $this->Number->format($writingServiceRequest->final_price) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Is Deleted') ?></th>
                    <td class="p-3"><?= $this->Number->format($writingServiceRequest->is_deleted) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700"><?= __('Created At') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->created_at) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700"><?= __('Updated At') ?></th>
                    <td class="p-3"><?= h($writingServiceRequest->updated_at) ?></td>
                </tr>
                <tr>
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
        </div>
    </div>
</div>
