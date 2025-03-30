<!-- File: templates/Pages/landingpage.php -->

<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Landing Page Example');
?>

<div style="display: flex; justify-content: center; align-items: center; gap: 2rem; margin: 2rem;">
    <!-- Text block on the left -->
    <div style="flex: 1;">
        <h1>Welcome to Diana Bonvini page</h1>

        <!-- button -->
        <div style="margin-top: 1rem;">
            <?= $this->Html->link(
                'Shop Art',
                ['controller' => 'Artworks', 'action' => 'index'],
                [
                    'class' => 'button-style',
                    'style' => 'margin-right: 1rem; padding: 0.5rem 1rem; background-color: #333; color: #fff; text-decoration: none; border-radius: 4px;'
                ]
            ) ?>
            <?php
            $user = $this->request->getAttribute('identity');
            $userType = $user?->get('user_type');
            ?>

            <?php if ($userType === 'admin'): ?>
                <?= $this->Html->link(
                    'Check All Writing Service Requests',
                    ['controller' => 'WritingServiceRequests', 'action' => 'adminIndex'],
                    [
                        'class' => 'button-style',
                        'style' => 'padding: 0.5rem 1rem; background-color: #333; color: #fff; text-decoration: none; border-radius: 4px;'
                    ]
                ) ?>
            <?php else: ?>
                <?= $this->Html->link(
                    'Get A Writing Service',
                    ['controller' => 'WritingServiceRequests', 'action' => 'add'],
                    [
                        'class' => 'button-style',
                        'style' => 'padding: 0.5rem 1rem; background-color: #333; color: #fff; text-decoration: none; border-radius: 4px;'
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- right side image -->
    <div style="flex: 1;">
        <?= $this->Html->image('Landingpage/wallowing-breeze-main.png', [
            'alt' => 'Wallowing Breeze Image',
            'style' => 'max-width: 100%; height: auto; border: 1px solid #ccc;'
        ]) ?>
    </div>
</div>
