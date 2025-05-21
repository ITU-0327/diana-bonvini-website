<?php
/**
 * Coaching Payment Request Email Template (Text Version)
 *
 * Variables:
 * - $client_name: The client's name
 * - $coaching_service_request: The coaching service request entity
 * - $payment: The payment entity
 * - $amount: The formatted payment amount
 */
?>
PAYMENT REQUEST
For Coaching Service #<?= $coaching_service_request->coaching_service_request_id ?>

Dear <?= $client_name ?>,

We hope this email finds you well. Your coaching service is ready to proceed, and we require payment to continue.

Please find the payment details below:

PAYMENT DETAILS
Service Title: <?= $coaching_service_request->service_title ?>
Service Type: <?= $coaching_service_request->service_type ?>
Amount Due: $<?= $amount ?>

To process your payment, please log in to our website and navigate to your coaching service request.
<?= $this->Url->build([
    'controller' => 'CoachingServiceRequests',
    'action' => 'view',
    $coaching_service_request->coaching_service_request_id
], ['fullBase' => true]) ?>

If you have any questions about this payment or your coaching service, please don't hesitate to reply to this message or contact us directly.

Thank you for choosing our coaching services.

Best regards,
Diana Bonvini

---
This is an automated email. Please do not reply directly to this message.
© <?= date('Y') ?> Diana Bonvini. All rights reserved. 