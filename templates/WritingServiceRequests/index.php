<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests
 */
use Cake\Utility\Inflector;

$this->assign('title', __('Writing Service Requests'));
?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Writing Service Requests']) ?>

    <!-- Minimal Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                <th class="py-3 px-6 text-left">Request ID</th>
                <th class="py-3 px-6 text-left">Request Title</th>
                <th class="py-3 px-6 text-left">Service Type</th>
                <th class="py-3 px-6 text-left">Final Price</th>
                <th class="py-3 px-6 text-left">Status</th>
                <th class="py-3 px-6 text-left">Submitted At</th>
                <th class="py-3 px-6 text-center">Details</th>
            </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
            <?php foreach ($writingServiceRequests as $request) : ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6"><?= h($request->writing_service_request_id) ?></td>
                    <td class="py-3 px-6"><?= h($request->service_title) ?></td>
                    <td class="py-3 px-6"><?= h(Inflector::humanize($request->service_type)) ?></td>
                    <td class="py-3 px-6">
                        <?= $request->final_price !== null ? '$' . $this->Number->format($request->final_price) : '-' ?>
                    </td>
                    <td class="py-3 px-6"><?= h(Inflector::humanize($request->request_status)) ?></td>
                    <td class="py-3 px-6">
                        <?php if (!empty($request->created_at)) : ?>
                            <span class="local-time" data-datetime="<?= h($request->created_at->format('c')) ?>"></span>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <?= $this->Html->link(
                            'View',
                            ['action' => 'view', $request->writing_service_request_id],
                            ['class' => 'bg-teal-600 text-white py-1 px-3 rounded hover:bg-teal-700 transition'],
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        <ul class="flex justify-center space-x-2">
            <?= $this->Paginator->first('<<', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->prev('<', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->numbers(['before' => '', 'after' => '', 'class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->next('>', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->last('>>', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
        </ul>
        <p class="text-center text-gray-600 mt-2">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timeElements = document.querySelectorAll('.local-time');
        timeElements.forEach(el => {
            const isoTime = el.dataset.datetime;
            const date = new Date(isoTime);
            el.textContent = date.toLocaleDateString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        });
    });
</script>
