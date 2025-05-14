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
                                <option value="pending">Pending</option>
                                <option value="reserved">Reserved</option>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($artworks as $artwork) : ?>
                                <tr class="artwork-row" data-status="<?= h($artwork->availability_status) ?>">
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
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <?= $this->Html->link(
                                                '<i class="fas fa-edit"></i>',
                                                ['action' => 'edit', $artwork->artwork_id],
                                                ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                                            ) ?>
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-trash"></i>',
                                                ['action' => 'delete', $artwork->artwork_id],
                                                [
                                                    'confirm' => 'Are you sure you want to delete this artwork?',
                                                    'class' => 'btn btn-sm btn-danger',
                                                    'escape' => false,
                                                ],
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?= $this->Paginator->prev('« Previous') ?>
                                <?= $this->Paginator->numbers() ?>
                                <?= $this->Paginator->next('Next »') ?>
                            </ul>
                        </nav>
                        <p class="text-center">
                            <?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total') ?>
                        </p>
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

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            filterArtworks();
        });

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
                    const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    if (!title.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.style.display = display ? '' : 'none';
            });
        }
    });
</script>
