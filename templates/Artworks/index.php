<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 * @var string|null $status
 */

use Cake\Collection\Collection;

// Set page title based on filter status
$pageTitle = match($status ?? '') {
    'available' => 'Art For Sale',
    'sold' => 'Art Sold',
    default => 'Art Gallery'
};

$this->assign('title', __($pageTitle));
?>

<?php
$user = $this->getRequest()->getAttribute('identity');
$userType = $user?->get('user_type');
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => $pageTitle]) ?>

    <!-- Filter Buttons -->
    <div class="flex space-x-4 mb-8">
        <?= $this->Html->link(
            'All Artwork',
            ['action' => 'index'],
            [
                'class' => 'filter-button px-4 py-2 rounded-full transition ' . 
                          (empty($status) ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300')
            ]
        ) ?>
        <?= $this->Html->link(
            'Art For Sale',
            ['action' => 'index', '?' => ['status' => 'available']],
            [
                'class' => 'filter-button px-4 py-2 rounded-full transition ' . 
                          ($status === 'available' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300')
            ]
        ) ?>
        <?= $this->Html->link(
            'Art Sold',
            ['action' => 'index', '?' => ['status' => 'sold']],
            [
                'class' => 'filter-button px-4 py-2 rounded-full transition ' . 
                          ($status === 'sold' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300')
            ]
        ) ?>
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

<style>
    /* Remove text selection and purple highlighting on filter buttons */
    .filter-button {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
        -webkit-tap-highlight-color: transparent;
    }
    
    .filter-button:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
    }
    
    .filter-button::-moz-focus-inner {
        border: 0;
    }
</style>

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
    });
</script>
