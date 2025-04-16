<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class PaymentsController extends AppController
{
    /**
     * Payment success callback.
     *
     * When Stripe calls back on success, redirect the customer to their order confirmation page.
     */
    public function success(): ?Response
    {
        // Retrieve the order_id from the query parameters.
        $orderId = $this->request->getQuery('order_id');
        if ($orderId) {
            // Redirect to the Orders confirmation page.
            return $this->redirect(['controller' => 'Orders', 'action' => 'confirmation', $orderId]);
        } else {
            // Fallback redirection if the order_id isn't available.
            $this->Flash->success(__('Your payment was successful.'));

            return $this->redirect(['controller' => 'Orders', 'action' => 'confirmation']);
        }
    }

    /**
     * Payment cancellation callback.
     */
    public function cancel(): ?Response
    {
        // Retrieve the order_id from the query parameters.
        $orderId = $this->request->getQuery('order_id');

        $this->Flash->error(__('Your payment was cancelled.'));

        // Redirect to resumeCheckout with the order ID if available
        if ($orderId) {
            return $this->redirect(['controller' => 'Orders', 'action' => 'resumeCheckout', $orderId]);
        }

        // Fallback to regular checkout if order_id isn't available
        return $this->redirect(['controller' => 'Orders', 'action' => 'checkout']);
    }
}
