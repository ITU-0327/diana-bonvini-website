<?php
/**
 * @var \App\Model\Entity\User $user
 * @var string $code
 */
?>

<p>Hello <?= h($user->first_name)?> <?= h($user->last_name) ?>,</p>
<p>Your verification code to sign in is:</p>
<p style="font-size: 24px; font-weight: bold; letter-spacing: 5px; text-align: center; padding: 15px; background-color: #f5f5f5; border-radius: 5px;"><?= h($code) ?></p>
<p>This code will expire in 10 minutes.</p>
<p>If you did not request this code, please ignore this email or contact support if you have any concerns.</p>