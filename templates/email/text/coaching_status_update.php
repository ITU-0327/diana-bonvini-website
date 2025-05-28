<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $client_name
 * @var string $old_status
 * @var string $new_status
 */
?>
Hello <?= $client_name ?>,

The status of your coaching service request has been updated.

REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Date: <?= date('F j, Y \a\t g:i A') ?>

STATUS UPDATE
------------------
Previous Status: <?= $old_status ?>
New Status: <?= $new_status ?>

<?php if ($new_status === 'Completed'): ?>
Thank you for using our coaching services! If you have any feedback or questions, please let us know.
<?php elseif ($new_status === 'Canceled' || $new_status === 'Cancelled'): ?>
If you have any questions about this cancellation, please contact us.
<?php endif; ?>

To view your request and any new messages, please log in to your account:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 