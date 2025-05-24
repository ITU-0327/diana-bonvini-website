<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $client_name
 * @var string $document_name
 */
?>
Hello <?= $client_name ?>,

A new document has been uploaded for your coaching service request.

REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Date: <?= date('F j, Y \a\t g:i A') ?>

DOCUMENT DETAILS
------------------
Document Name: <?= $document_name ?>
Uploaded By: Diana Bonvini

Please log in to your account to view and download the document:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 