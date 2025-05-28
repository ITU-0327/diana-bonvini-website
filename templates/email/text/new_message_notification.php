<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 * @var string $messageContent
 * @var string $messageDate
 * @var string $requestId
 */
?>
Hello <?= $adminName ?>,

<?= $customerName ?> has sent a new message regarding writing service request #<?= $requestId ?>.

REQUEST DETAILS
------------------
Service: <?= $request->service_title ?>
Request ID: <?= $requestId ?>
Date of Message: <?= $messageDate ?>
Customer: <?= $customerName ?> (<?= $customerEmail ?>)

MESSAGE CONTENT
------------------
<?= $messageContent ?>

To view and respond to this message, please log in to the admin dashboard:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 