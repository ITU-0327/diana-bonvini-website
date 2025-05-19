<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 */

use Cake\Collection\Collection;

?>

<?php
$user = $this->getRequest()->getAttribute('identity');
$userType = $user?->get('user_type');
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Art Gallery']) ?>

    <!-- Filter Buttons -->
    <div class="flex space-x-4 mb-8">
        <button id="filter-available" class="px-4 py-2 bg-gray-800 text-white rounded-full hover:bg-gray-700 transition">
            Art for Sale
        </button>
        <button id="filter-sold" class="px-4 py-2 bg-gray-500 text-white rounded-full hover:bg-gray-600 transition">
            Art Sold
        </button>

    </div>

    <!-- Artworks Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        <?php foreach ($artworks as $artwork) :
            /** @var \App\Model\Entity\ArtworkVariant $cheapest */
            $cheapest = (new Collection($artwork->artwork_variants))
                ->sortBy('price')
                ->last(); ?>
            <div
                class="relative group artwork-card cursor-pointer aspect-[4/3]"
                data-status="<?= h($artwork->availability_status) ?>"
                data-url="<?= $this->Url->build(['action' => 'view', $artwork->artwork_id]) ?>"
            >
                <!-- Artwork Image -->
                <?= $this->Html->image($artwork->image_url, [
                    'alt' => $artwork->title,
                    'class' => 'w-full h-full object-cover',
                ]) ?>

                <?php if ($artwork->availability_status === 'available') : ?>
                    <!-- Hover Overlay for Available Art -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-70 transition
                            flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100">
                        <h2 class="text-xl font-bold mb-2"><?= h($artwork->title) ?></h2>
                        <p class="mb-4">From $<?= $this->Number->format($cheapest->price) ?></p>
                    </div>
                <?php else : ?>
                    <!-- Sold Overlay with Price -->
                    <div class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center
                            text-white text-2xl font-bold">
                        <div>Sold</div>
                        <div class="mt-2 text-xl">$<?= $this->Number->format($cheapest->price) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function handleArtworkClick(e) {
            // If the clicked element (or its ancestor) doesn't have the no-detail class, redirect.
            if (!e.target.closest('.no-detail')) {
                // Use e.currentTarget (the element the event listener is attached to)
                window.location.href = e.currentTarget.getAttribute('data-url');
            }
        }

        // Attach the event listener to each artwork card
        document.querySelectorAll('.artwork-card').forEach(function(card) {
            card.addEventListener('click', handleArtworkClick);
        });

        // Function to filter cards by status
        function filterCards(status) {
            document.querySelectorAll('.artwork-card').forEach(function(card) {
                card.style.display = (card.getAttribute('data-status') === status) ? 'block' : 'none';
            });
        }
        // Initially show available artworks
        filterCards('available');

        // "Art for Sale" filter
        document.getElementById('filter-available').addEventListener('click', function() {
            filterCards('available');
        });

        // "Art Sold" filter
        document.getElementById('filter-sold').addEventListener('click', function() {
            filterCards('sold');
        });
    });
</script>
