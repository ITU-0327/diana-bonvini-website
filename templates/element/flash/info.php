<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}

// Add auto-dismiss class by default unless explicitly disabled
$autoDismiss = $params['autoDismiss'] ?? true;
$class = 'message';
if ($autoDismiss) {
    $class .= ' auto-dismiss';
}
?>
<div class="<?= h($class) ?>" data-flash-message onclick="dismissFlashMessage(this);">
    <?= $message ?>
</div>
