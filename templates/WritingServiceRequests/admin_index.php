<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest[]|\Cake\Datasource\ResultSetInterface $writingServiceRequests
 */
?>
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
    <h3 class="text-2xl font-semibold text-gray-800">All Writing Service Requests (Admin)</h3>

    <div class="overflow-auto border rounded">
        <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-gray-100 text-xs uppercase text-gray-600">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Service Type</th>
                <th class="px-4 py-3">Word Count Range</th>
                <th class="px-4 py-3">Notes</th>
                <th class="px-4 py-3">Final Price</th>
                <th class="px-4 py-3">Request Status</th>
                <th class="px-4 py-3">Created</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <?php foreach ($writingServiceRequests as $req): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2"><?= h($req->request_id) ?></td>
                    <td class="px-4 py-2">
                        <?php if (!empty($req->user)) : ?>
                            <?= h($req->user->first_name . ' ' . $req->user->last_name) ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2"><?= h($req->service_type) ?></td>
                    <td class="px-4 py-2"><?= h($req->word_count_range) ?></td>
                    <td class="px-4 py-2"><?= h($req->notes) ?></td>
                    <td class="px-4 py-2"><?= $req->final_price === null ? '' : $this->Number->format($req->final_price) ?></td>
                    <td class="px-4 py-2"><?= h($req->request_status) ?></td>
                    <td class="px-4 py-2">
                        <span class="local-time" data-datetime="<?= h($req->created_at->format('c')) ?>"></span>
                    </td>
                    <td class="px-4 py-2 space-x-2 whitespace-nowrap">
                        <?= $this->Html->link('View',
                            ['action' => 'adminView', $req->request_id],
                            ['class' => 'text-blue-600 hover:underline']
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginator if you want it -->
    <div class="flex justify-between items-center mt-6 flex-wrap gap-2">
        <div class="text-sm text-gray-600">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </div>
        <ul class="inline-flex items-center space-x-1 text-sm">
            <?= $this->Paginator->first('<<') ?>
            <?= $this->Paginator->prev('<') ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next('>') ?>
            <?= $this->Paginator->last('>>') ?>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timeElements = document.querySelectorAll('.local-time');

        timeElements.forEach(el => {
            const isoTime = el.dataset.datetime;
            const date = new Date(isoTime);

            const formatted = date.toLocaleString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });

            el.textContent = formatted;
        });
    });
</script>
