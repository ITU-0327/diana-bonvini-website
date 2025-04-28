<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 */
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Artwork Management</h1>
        <a href="<?= $this->Url->build(['action' => 'add']) ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Add New Artwork
        </a>
    </div>

    <!-- Filter Controls -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Artworks</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <select id="status-filter" class="form-control">
                        <option value="all">All Status</option>
                        <option value="available">Available</option>
                        <option value="sold">Sold</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <select id="category-filter" class="form-control">
                        <option value="all">All Categories</option>
                        <option value="painting">Paintings</option>
                        <option value="photography">Photography</option>
                        <option value="digital">Digital Art</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <select id="sort-order" class="form-control">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price_high">Price (High to Low)</option>
                        <option value="price_low">Price (Low to High)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="input-group">
                        <label for="search-input"></label><input type="text" id="search-input" class="form-control" placeholder="Search artworks...">
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

    <!-- Artworks Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Artworks</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="artworksTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($artworks as $artwork) : ?>
                        <tr class="artwork-row" data-status="<?= h($artwork->availability_status) ?>" data-category="<?= h($artwork->category) ?>">
                            <td class="align-middle text-center">
                                <img src="<?= h($artwork->image_url) ?>" alt="<?= h($artwork->title) ?>" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                            </td>
                            <td class="align-middle"><?= h($artwork->title) ?></td>
                            <td class="align-middle"><?= h($artwork->category) ?></td>
                            <td class="align-middle">$<?= $this->Number->format($artwork->price) ?></td>
                            <td class="align-middle">
                                <?php if ($artwork->availability_status === 'available') : ?>
                                    <span class="badge badge-success">Available</span>
                                <?php else : ?>
                                    <span class="badge badge-secondary">Sold</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle"><?= $artwork->created->format('M d, Y') ?></td>
                            <td class="align-middle">
                                <div class="btn-group">
                                    <a href="<?= $this->Url->build(['action' => 'view', $artwork->artwork_id]) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= $this->Url->build(['action' => 'edit', $artwork->artwork_id]) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            filterArtworks();
        });

        // Category filter
        document.getElementById('category-filter').addEventListener('change', function() {
            filterArtworks();
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            filterArtworks();
        });

        // Function to filter artworks
        function filterArtworks() {
            const statusFilter = document.getElementById('status-filter').value;
            const categoryFilter = document.getElementById('category-filter').value;
            const searchTerm = document.getElementById('search-input').value.toLowerCase();

            document.querySelectorAll('.artwork-row').forEach(function(row) {
                let display = true;

                // Status filtering
                if (statusFilter !== 'all' && row.getAttribute('data-status') !== statusFilter) {
                    display = false;
                }

                // Category filtering
                if (categoryFilter !== 'all' && row.getAttribute('data-category') !== categoryFilter) {
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
