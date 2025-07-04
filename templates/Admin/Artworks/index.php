<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 */

use Cake\Collection\Collection;

$this->assign('title', __('Artworks'));
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette mr-2"></i>Artwork Management</h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Artworks') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter mr-1"></i> Filter Artworks
                    </h6>
                    <?= $this->Html->link(
                        '<i class="fas fa-plus-circle mr-2"></i>Add New Artwork',
                        ['action' => 'add'],
                        ['class' => 'btn btn-primary btn-sm', 'escape' => false]
                    ) ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="status-filter" class="form-label">Status</label>
                            <select id="status-filter" class="form-control">
                                <option value="all">All Status</option>
                                <option value="available">Available</option>
                                <option value="sold">Sold</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sort-order" class="form-label">Sort By</label>
                            <select id="sort-order" class="form-control">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="price_high">Price (High to Low)</option>
                                <option value="price_low">Price (Low to High)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="search-input" class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" id="search-input" class="form-control" placeholder="Search artworks...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Artworks Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Artworks</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="artworksTable">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($artworks as $artwork) : ?>
                                <tr class="artwork-row hover-clickable cursor-pointer transition-colors" 
                                    data-status="<?= h($artwork->availability_status) ?>"
                                    data-price="<?php 
                                        $variants = $artwork->artwork_variants ?? [];
                                        $cheapest = (new Collection($variants))->sortBy('price')->first();
                                        echo $cheapest ? $cheapest->price : 0;
                                    ?>"
                                    data-created="<?= $artwork->created_at ? $artwork->created_at->format('Y-m-d H:i:s') : '1970-01-01 00:00:00' ?>"
                                    data-title="<?= h(strtolower($artwork->title)) ?>"
                                    data-href="<?= $this->Url->build(['action' => 'edit', $artwork->artwork_id]) ?>">
                                    <td class="align-middle text-center">
                                        <?php
                                            // Add a random query parameter to prevent caching
                                            $imageUrl = h($artwork->image_url) . '?v=' . time();
                                        ?>
                                        <?= $this->Html->image('Artworks/' . $artwork->artwork_id . '.jpg', [
                                            'alt' => h($artwork->title),
                                            'class' => 'img-thumbnail',
                                            'style' => 'width: 80px; height: 60px; object-fit: cover;',
                                        ]) ?>
                                    </td>
                                    <td class="align-middle"><?= h($artwork->title) ?></td>
                                    <td class="align-middle">
                                        <?php
                                        // Calculate the cheapest variant price
                                        $variants = $artwork->artwork_variants ?? [];
                                        $cheapest = (new Collection($variants))->sortBy('price')->first();
                                        $displayPrice = $cheapest ? $cheapest->price : null;
                                        ?>
                                        <?php if ($displayPrice !== null): ?>
                                            $<?= $this->Number->format($displayPrice, ['precision' => 2]) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php
                                        $statusClass = match ($artwork->availability_status) {
                                            'available' => 'success',
                                            'sold' => 'danger',
                                            default => 'secondary'
                                        }; ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(h($artwork->availability_status)) ?></span>
                                    </td>
                                    <td class="align-middle"><?= $artwork->created_at ? $artwork->created_at->format('M d, Y') : 'N/A' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    $this->Paginator->setTemplates([
                        'prevActive'   => '<a rel="prev" href="{{url}}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left mr-2"></i>Previous</a>',
                        'prevDisabled' => '<span class="btn btn-secondary btn-sm disabled"><i class="fas fa-arrow-left mr-2"></i>Previous</span>',
                        'nextActive'   => '<a rel="next" href="{{url}}" class="btn btn-primary btn-sm">Next<i class="fas fa-arrow-right ml-2"></i></a>',
                        'nextDisabled' => '<span class="btn btn-secondary btn-sm disabled">Next<i class="fas fa-arrow-right ml-2"></i></span>',

                        'number'  => '<li class="page-item"><a href="{{url}}" class="btn btn-outline-primary btn-sm mx-1">{{text}}</a></li>',
                        'current' => '<li class="page-item"><span class="btn btn-primary btn-sm mx-1">{{text}}</span></li>',
                    ]);
                    ?>

                    <div class="mt-3 position-relative" style="min-height:42px;">
                        <div style="position:absolute; left:0; top:0;">
                            <?= $this->Paginator->prev('') ?>
                        </div>
                        <ul class="pagination justify-content-center mt-2 mb-0">
                            <?= $this->Paginator->numbers() ?>
                        </ul>
                        <div style="position:absolute; right:0; top:0;">
                            <?= $this->Paginator->next('') ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            filterArtworks();
        });

        // Sort functionality
        document.getElementById('sort-order').addEventListener('change', function() {
            sortArtworks();
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            filterArtworks();
        });

        // Function to sort artworks
        function sortArtworks() {
            const sortOrder = document.getElementById('sort-order').value;
            const tableBody = document.querySelector('#artworksTable tbody');
            const rows = Array.from(tableBody.querySelectorAll('.artwork-row'));

            rows.sort(function(a, b) {
                switch(sortOrder) {
                    case 'newest':
                        return new Date(b.getAttribute('data-created')) - new Date(a.getAttribute('data-created'));
                    case 'oldest':
                        return new Date(a.getAttribute('data-created')) - new Date(b.getAttribute('data-created'));
                    case 'price_high':
                        return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
                    case 'price_low':
                        return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
                    default:
                        return 0;
                }
            });

            // Clear the table body and append sorted rows
            tableBody.innerHTML = '';
            rows.forEach(function(row) {
                tableBody.appendChild(row);
            });

            // Reapply filters after sorting
            filterArtworks();
        }

        // Function to filter artworks
        function filterArtworks() {
            const statusFilter = document.getElementById('status-filter').value;
            const searchTerm = document.getElementById('search-input').value.toLowerCase();

            document.querySelectorAll('.artwork-row').forEach(function(row) {
                let display = true;

                // Status filtering
                if (statusFilter !== 'all' && row.getAttribute('data-status') !== statusFilter) {
                    display = false;
                }

                // Search filtering
                if (searchTerm !== '') {
                    const title = row.getAttribute('data-title');
                    if (!title.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.style.display = display ? '' : 'none';
            });
        }

        // Handle clickable rows
        const clickableRows = document.querySelectorAll('tr[data-href]');
        clickableRows.forEach(row => {
            // Add keyboard accessibility
            row.setAttribute('tabindex', '0');
            row.setAttribute('role', 'button');
            row.setAttribute('aria-label', 'Edit artwork details');

            // Handle keyboard navigation
            row.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.location.href = this.dataset.href;
                }
            });

            // Add visual feedback for focus
            row.addEventListener('focus', function() {
                this.style.outline = '2px solid #007bff';
                this.style.outlineOffset = '-2px';
            });

            row.addEventListener('blur', function() {
                this.style.outline = '';
                this.style.outlineOffset = '';
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

<style>
    /* Clickable row styles */
    .hover-clickable {
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .hover-clickable:hover {
        background-color: #f8f9fa !important;
    }

    .hover-clickable:focus {
        background-color: #e3f2fd !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .transition-colors {
        transition: background-color 0.2s ease, color 0.2s ease;
    }
</style>
