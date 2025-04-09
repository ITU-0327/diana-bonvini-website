<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

use Cake\Utility\Inflector;

?>
<div class="flex flex-col lg:flex-row gap-6 p-6">
    <!-- Sidebar -->
    <aside class="w-full lg:w-1/4">
        <div class="bg-white shadow rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-700 mb-4">Admin Actions</h4>
            <div class="flex flex-col space-y-2">
                <?= $this->Form->postLink(
                    'Delete This Request',
                    ['action' => 'delete', $writingServiceRequest->writing_service_request_id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->writing_service_request_id),
                        'class' => 'text-red-600 hover:underline',
                    ],
                ) ?>
                <?= $this->Html->link(
                    'Back to All Requests',
                    ['action' => 'adminIndex'],
                    ['class' => 'text-blue-600 hover:underline'],
                ) ?>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="w-full lg:w-3/4">
        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">
                Request #<?= h($writingServiceRequest->service_title) ?> (Admin View)
            </h3>

            <!-- Request Details -->
            <table class="w-full text-left border border-gray-200 rounded overflow-hidden">
                <tbody class="divide-y divide-gray-200">
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700">ID</th>
                    <td class="p-3"><?= h($writingServiceRequest->writing_service_request_id) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700">User</th>
                    <td class="p-3">
                        <?= h($writingServiceRequest->user->first_name . ' ' . $writingServiceRequest->user->last_name) ?>
                    </td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700">Service Type</th>
                    <td class="p-3"><?= h(Inflector::humanize($writingServiceRequest->service_type)) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700">Notes</th>
                    <td class="p-3"><?= nl2br(h($writingServiceRequest->notes)) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700">Status</th>
                    <td class="p-3"><?= h(Inflector::humanize($writingServiceRequest->request_status)) ?></td>
                </tr>
                <tr>
                    <th class="p-3 font-semibold text-gray-700">Final Price</th>
                    <td class="p-3">
                        <?= $writingServiceRequest->final_price === null ? 'N/A' : $this->Number->format($writingServiceRequest->final_price) ?>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700">Created At</th>
                    <td class="p-3">
                        <span class="local-time" data-datetime="<?= h($writingServiceRequest->created_at->format('c')) ?>"></span>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <th class="p-3 font-semibold text-gray-700">Document</th>
                    <td class="p-3">
                        <?php if (!empty($writingServiceRequest->document)) : ?>
                            <?= $this->Html->link('View Document', '/' . $writingServiceRequest->document, [
                                'target' => '_blank',
                                'class' => 'text-blue-500 hover:underline',
                            ]) ?>
                        <?php else : ?>
                            <span class="text-gray-500 italic">No Document</span>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Conversation -->
            <h4 class="text-xl font-semibold mt-6">Conversation</h4>
            <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                <div class="space-y-4">
                    <?php foreach ($writingServiceRequest->request_messages as $msg) : ?>
                        <?php
                        $isAdmin = isset($msg->user) && strtolower($msg->user->user_type) === 'admin';
                        ?>
                        <div class="p-4 rounded-lg <?= $isAdmin ? 'bg-blue-100 rounded-bl-none' : 'bg-green-100 rounded-br-none' ?>">
                            <div class="mb-1 font-bold text-sm text-gray-700">
                                <?= h($msg->user->first_name . ' ' . $msg->user->last_name . ($isAdmin ? ' (Admin)' : '')) ?>
                            </div>
                            <div class="text-gray-800 text-sm">
                                <?= nl2br(h($msg->message)) ?>
                            </div>
                            <?php if (!empty($msg->created_at)) : ?>
                                <div class="mt-1 text-xs text-gray-500">
                                    <span class="local-time" data-datetime="<?= h($msg->created_at->format('c')) ?>"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="text-gray-500">No conversation yet.</p>
            <?php endif; ?>

            <!-- Admin Reply -->
            <h4 class="text-xl font-semibold mt-6">Admin Reply</h4>
            <?= $this->Form->create($writingServiceRequest, [
                'url' => ['action' => 'adminView', $writingServiceRequest->writing_service_request_id],
            ]) ?>
            <div class="space-y-4">
                <?= $this->Form->control('final_price', [
                    'label' => 'Final Price',
                    'class' => 'w-full border-gray-300 rounded',
                ]) ?>
                <?= $this->Form->control('request_status', [
                    'type' => 'select',
                    'options' => [
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'expired' => 'Expired',
                    ],
                    'default' => $writingServiceRequest->request_status,
                    'label' => 'Request Status',
                    'class' => 'w-full border-gray-300 rounded',
                ]) ?>
                <?= $this->Form->control('reply_message', [
                    'type' => 'textarea',
                    'label' => 'Your Message',
                    'class' => 'w-full border-gray-300 rounded',
                ]) ?>
            </div>
            <div class="mt-4">
                <?= $this->Form->button('Send Message', [
                    'class' => 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700',
                ]) ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<!-- JavaScript for time localization -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timeElements = document.querySelectorAll('.local-time');
        timeElements.forEach(el => {
            const isoTime = el.dataset.datetime;
            const date = new Date(isoTime);
            el.textContent = date.toLocaleString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            });
        });
    });
</script>
