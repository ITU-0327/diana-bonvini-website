<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|array<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests
 */

use Cake\Utility\Inflector;

?>
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
    <?= $this->element('page_title', ['title' => 'Writing Service Requests']) ?>

    <?= $this->Form->create(null, ['type' => 'get']) ?>
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 border-b pb-4 mb-4">
        <!-- Search Input -->
        <div class="w-full md:w-1/3">
            <label for="q" class="block text-sm font-medium text-gray-700">Search keywords</label>
            <input type="text" name="q" id="q"
                   value="<?= h($this->request->getQuery('q')) ?>"
                   placeholder="e.g. Title or Name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        </div>

        <!-- Filter Dropdowns -->
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-2/3">
            <div class="flex-1">
                <label for="service_type" class="block text-sm font-medium text-gray-700">Service type</label>
                <?= $this->Form->select('service_type', [
                    'creative_writing' => 'Creative Writing',
                    'editing' => 'Editing',
                    'proofreading' => 'Proofreading',
                ], [
                    'empty' => 'All',
                    'default' => $this->request->getQuery('service_type'),
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]) ?>
            </div>

            <div class="flex-1">
                <label for="request_status" class="block text-sm font-medium text-gray-700">Request status</label>
                <?= $this->Form->select('request_status', [
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'expired' => 'Expired',
                ], [
                    'empty' => 'All',
                    'default' => $this->request->getQuery('request_status'),
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]) ?>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2 mt-4 md:mt-0">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                See Result
            </button>
            <a href="<?= $this->Url->build(['action' => 'adminIndex']) ?>"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Reset
            </a>
        </div>
    </div>
    <?= $this->Form->end() ?>

    <div class="overflow-auto border rounded">
        <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-gray-100 text-xs uppercase text-gray-600">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Service Type</th>
                <th class="px-4 py-3">Final Price</th>
                <th class="px-4 py-3">Request Status</th>
                <th class="px-4 py-3">Created</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <?php foreach ($writingServiceRequests as $req) : ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2"><?= h($req->writing_service_request_id) ?></td>
                    <td class="px-4 py-2"><?= h($req->service_title) ?></td>
                    <td class="px-4 py-2">
                        <?php if (!empty($req->user)) : ?>
                            <?= h($req->user->first_name . ' ' . $req->user->last_name) ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2"><?= h(Inflector::humanize($req->service_type)) ?></td>
                    <td class="px-4 py-2"><?= $req->final_price === null ? 'N/A' : $this->Number->format($req->final_price) ?></td>
                    <td class="px-4 py-2"><?= h(Inflector::humanize($req->request_status)) ?></td>
                    <td class="px-4 py-2">
                        <span class="local-time" data-datetime="<?= h($req->created_at->format('c')) ?>"></span>
                    </td>
                    <td class="px-4 py-2 space-x-2 whitespace-nowrap">
                        <?= $this->Html->link(
                            'View',
                            ['action' => 'adminView', $req->writing_service_request_id],
                            ['class' => 'text-blue-600 hover:underline'],
                        ) ?>
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
