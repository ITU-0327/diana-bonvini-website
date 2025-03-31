<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests
 */
/** @var \App\Model\Entity\User $user */
$user = $this->request->getAttribute('identity');
?>

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-semibold text-gray-800">Writing Service Requests</h3>
        <?= $this->Html->link(
            __('New Writing Service Request'),
            ['action' => 'add'],
            ['class' => 'inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition'],
        ) ?>
    </div>

    <!-- Greeting with animation -->
    <div class="mt-2 flex items-center space-x-2 animate-fade-in">
        <span class="text-2xl">👋</span>
        <p class="text-gray-700 text-lg font-medium">Hi, <?= h($user->first_name) ?>!</p>
    </div>

    <!-- Animation styles -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }
    </style>

    <div class="overflow-auto border rounded">
        <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-gray-100 text-xs uppercase text-gray-600">
            <tr>
                <th class="px-4 py-3"><?= $this->Paginator->sort('writing_service_request_id') ?></th>
                <th class="px-4 py-3"><?= $this->Paginator->sort('service_type') ?></th>
                <th class="px-4 py-3"><?= $this->Paginator->sort('word_count_range') ?></th>
                <th class="px-4 py-3"><?= $this->Paginator->sort('notes') ?></th>
                <th class="px-4 py-3"><?= $this->Paginator->sort('final_price') ?></th>
                <th class="px-4 py-3"><?= $this->Paginator->sort('request_status') ?></th>
                <th class="px-4 py-3"><?= __('Document') ?></th>
                <th class="px-4 py-3"><?= __('Actions') ?></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <?php foreach ($writingServiceRequests as $writingServiceRequest) : ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2"><?= h($writingServiceRequest->writing_service_request_id) ?></td>
                    <td class="px-4 py-2"><?= h($writingServiceRequest->service_type) ?></td>
                    <td class="px-4 py-2"><?= h($writingServiceRequest->word_count_range) ?></td>
                    <td class="px-4 py-2"><?= h($writingServiceRequest->notes) ?></td>
                    <td class="px-4 py-2"><?= $writingServiceRequest->final_price === null ? '' : $this->Number->format($writingServiceRequest->final_price) ?></td>
                    <td class="px-4 py-2"><?= h($writingServiceRequest->request_status) ?></td>
                    <td class="px-4 py-2">
                        <?php if (!empty($writingServiceRequest->document)) : ?>
                            <?= $this->Html->link('View Document', '/' . $writingServiceRequest->document, ['target' => '_blank', 'class' => 'text-blue-500 hover:underline']) ?>
                        <?php else : ?>
                            <span class="text-gray-400 italic">No Document</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2 space-x-2 whitespace-nowrap">
                        <?= $this->Html->link('View', ['action' => 'view', $writingServiceRequest->writing_service_request_id], ['class' => 'text-blue-600 hover:underline']) ?>
                        <?= $this->Html->link('Edit', ['action' => 'edit', $writingServiceRequest->writing_service_request_id], ['class' => 'text-yellow-600 hover:underline']) ?>
                        <?= $this->Form->postLink('Delete', ['action' => 'delete', $writingServiceRequest->writing_service_request_id], [
                            'method' => 'post',
                            'confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->writing_service_request_id),
                            'class' => 'text-red-600 hover:underline',
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-6 flex-wrap gap-2">
        <div class="text-sm text-gray-600">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </div>
        <ul class="inline-flex items-center space-x-1 text-sm">
            <?= $this->Paginator->first('<<', ['class' => 'px-2 py-1 border rounded hover:bg-gray-100']) ?>
            <?= $this->Paginator->prev('<', ['class' => 'px-2 py-1 border rounded hover:bg-gray-100']) ?>
            <?= $this->Paginator->numbers(['before' => '', 'after' => '', 'modulus' => 2, 'separator' => '', 'class' => 'px-2 py-1 border rounded hover:bg-gray-100']) ?>
            <?= $this->Paginator->next('>', ['class' => 'px-2 py-1 border rounded hover:bg-gray-100']) ?>
            <?= $this->Paginator->last('>>', ['class' => 'px-2 py-1 border rounded hover:bg-gray-100']) ?>
        </ul>
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
                hour12: false,
            });
        });
    });
</script>
