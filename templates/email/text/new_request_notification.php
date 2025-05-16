<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 * @var string $requestDate
 */
?>
Hello <?= $adminName ?>,

A new writing service request has been submitted.

REQUEST DETAILS
------------------
Service: <?= $request->service_title ?>
Request ID: <?= $request->writing_service_request_id ?>
Date: <?= $requestDate ?>
Customer: <?= $customerName ?> (<?= $customerEmail ?>)

<?php if (!empty($request->service_description)): ?>
Description:
<?= $request->service_description ?>
<?php endif; ?>

To view and respond to this request, please log in to the admin dashboard:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 