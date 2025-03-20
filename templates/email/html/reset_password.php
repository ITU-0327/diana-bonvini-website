<?php
/**
 * @var \App\Model\Entity\User $user
 * @var string $resetLink
 */
?>

<p>Hello <?= h($user->first_name)?> <?= h($user->last_name) ?>,</p>
<p>Please click the following link to reset your password:</p>
<p><a href="<?= h($resetLink) ?>"><?= h($resetLink) ?></a></p>
<p>This link will expire in 1 hour.</p>
