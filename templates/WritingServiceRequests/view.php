<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

use Cake\Utility\Inflector;

?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header with left underline -->
    <div class="flex flex-col items-start mb-8">
        <h1 class="text-3xl uppercase text-gray-800">Writing Service Request Details</h1>
        <div class="mt-1 w-16 h-[2px] bg-gray-800"></div>
    </div>

    <!-- Request Details Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <table class="min-w-full">
            <tbody class="divide-y divide-gray-200">
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Request ID</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h($writingServiceRequest->writing_service_request_id) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Request Title</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h($writingServiceRequest->service_title) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Service Type</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h(Inflector::humanize($writingServiceRequest->service_type)) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Status</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h(Inflector::humanize($writingServiceRequest->request_status)) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Final Price</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800">
                    <?= $writingServiceRequest->final_price === null ? 'N/A' : $this->Number->format($writingServiceRequest->final_price) ?>
                </td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Submitted At</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800">
                    <?php if (!empty($writingServiceRequest->created_at)) : ?>
                        <span class="local-time" data-datetime="<?= h($writingServiceRequest->created_at->format('c')) ?>"></span>
                    <?php else: ?>
                        <span>N/A</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Document</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800">
                    <?php if (!empty($writingServiceRequest->document)) : ?>
                        <?= $this->Html->link(
                            'View Document',
                            '/' . $writingServiceRequest->document,
                            ['target' => '_blank', 'class' => 'text-blue-500 hover:underline']
                        ) ?>
                    <?php else : ?>
                        <span class="italic text-gray-500">No Document</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($writingServiceRequest->notes)) : ?>
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Notes</th>
                    <td class="py-3 px-6 text-left text-sm text-gray-800"><?= nl2br(h($writingServiceRequest->notes)) ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Conversation Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Conversation</h2>
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
            <p class="text-gray-500">No messages yet.</p>
        <?php endif; ?>
    </div>

    <!-- Reply Form Card -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Reply to Admin</h2>
        <?= $this->Form->create(null, ['url' => ['action' => 'view', $writingServiceRequest->writing_service_request_id]]) ?>
        <div class="space-y-4">
            <?= $this->Form->textarea('reply_message', [
                'label' => false,
                'placeholder' => 'Type your reply...',
                'rows' => 4,
                'class' => 'w-full border border-gray-300 rounded p-2',
            ]) ?>
            <?= $this->Form->button('Send Message', [
                'class' => 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>

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
