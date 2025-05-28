<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <main class="error-container">
        <div class="flash-messages-container">
            <?= $this->Flash->render() ?>
        </div>
        <div class="error-content">
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <?= $this->Html->link(__('Back'), 'javascript:history.back()') ?>
    
    <script>
        // Flash Message Functionality
        function dismissFlashMessage(element) {
            if (element) {
                element.classList.add('hidden');
                setTimeout(() => {
                    element.remove();
                }, 300);
            }
        }
        
        // Auto-dismiss flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const autoMessages = document.querySelectorAll('.message.auto-dismiss');
            autoMessages.forEach(function(message) {
                setTimeout(() => {
                    if (message && !message.classList.contains('hidden')) {
                        dismissFlashMessage(message);
                    }
                }, 5000);
            });
        });
        
        // Keyboard accessibility - dismiss with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const visibleMessages = document.querySelectorAll('.message:not(.hidden)');
                visibleMessages.forEach(function(message) {
                    dismissFlashMessage(message);
                });
            }
        });
    </script>
</body>
</html>
