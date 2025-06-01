<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests
 */
use Cake\Utility\Inflector;

$this->assign('title', __('Writing Service Requests'));

// Include local time converter for proper local time display
echo $this->Html->script('local-time-converter', ['block' => false]);
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'My Writing Service Requests']) ?>

    <!-- Action card -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Actions</h2>
                <p class="text-gray-600 text-sm mt-1">Create a new writing service request or manage existing ones.</p>
            </div>
            <div>
                <?= $this->Html->link(__('Request New Writing Service'),
                    ['action' => 'add'],
                    ['class' => 'inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 transition']
                ) ?>
            </div>
        </div>
    </div>

    <?php if (count($writingServiceRequests) > 0): ?>
        <!-- Table of requests -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($writingServiceRequests as $request): ?>
                        <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-200" 
                            data-href="<?= $this->Url->build(['action' => 'view', $request->writing_service_request_id]) ?>"
                            onclick="window.location.href = this.dataset.href">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                <?= h($request->writing_service_request_id) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="font-medium"><?= h($request->service_title) ?></div>
                                <div class="text-xs text-gray-500"><?= h(Inflector::humanize($request->service_type)) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'pending_quote' => 'bg-yellow-100 text-yellow-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    'canceled' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusClass = $statusClasses[$request->request_status] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', h($request->request_status))) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if (!empty($request->created_at)) : ?>
                                    <span class="created-date" data-server-time="<?= $request->created_at->jsonSerialize() ?>" data-time-format="datetime">
                                        <?= $request->created_at->format('Y-m-d H:i') ?>
                                    </span>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <div class="paginator">
                <ul class="pagination flex space-x-2 justify-center mt-4">
                    <?= $this->Paginator->first('<< ' . __('First'), ['class' => 'px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300']) ?>
                    <?= $this->Paginator->prev('< ' . __('Previous'), ['class' => 'px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300']) ?>
                    <?= $this->Paginator->numbers(['class' => 'px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300', 'current' => 'px-2 py-1 bg-blue-500 text-white rounded']) ?>
                    <?= $this->Paginator->next(__('Next') . ' >', ['class' => 'px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300']) ?>
                    <?= $this->Paginator->last(__('Last') . ' >>', ['class' => 'px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300']) ?>
                </ul>
                <p class="text-center text-gray-600 mt-2"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <div class="text-gray-600 mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No writing service requests</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new writing service request.</p>
            </div>
            <?= $this->Html->link(__('Create New Request'),
                ['action' => 'add'],
                ['class' => 'inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 transition']
            ) ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // The local time converter will automatically handle all timestamp conversions
        
        // Handle clickable rows
        const clickableRows = document.querySelectorAll('tr[data-href]');
        clickableRows.forEach(row => {
            // Add keyboard accessibility
            row.setAttribute('tabindex', '0');
            row.setAttribute('role', 'button');
            row.setAttribute('aria-label', 'View request details');

            // Handle keyboard navigation
            row.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.location.href = this.dataset.href;
                }
            });

            // Add visual feedback for focus
            row.addEventListener('focus', function() {
                this.classList.add('ring-2', 'ring-blue-500', 'ring-inset');
            });

            row.addEventListener('blur', function() {
                this.classList.remove('ring-2', 'ring-blue-500', 'ring-inset');
            });

            // Handle mouse clicks (including middle-click for new tabs)
            row.addEventListener('click', function(e) {
                if (e.ctrlKey || e.metaKey || e.button === 1) {
                    // Ctrl/Cmd+click or middle click - open in new tab
                    window.open(this.dataset.href, '_blank');
                } else {
                    // Regular click - navigate in same tab
                    window.location.href = this.dataset.href;
                }
            });

            // Prevent text selection when clicking
            row.addEventListener('selectstart', function(e) {
                e.preventDefault();
            });
        });
    });
</script>
