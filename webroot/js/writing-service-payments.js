/**
 * Writing Service Payment Handling
 *
 * This script handles payment status checks and UI updates for the writing service
 * payment system. It provides functions to:
 * - Check payment status
 * - Update payment UI based on status
 * - Add payment confirmation messages to the chat
 */

document.addEventListener('DOMContentLoaded', function () {
    // Process any existing payment buttons on page load
    processPaymentElements();

    // Check for payment success in URL and process it
    processPaymentSuccess();

    // Set up a recurring check for all payment buttons (every 5 seconds)
    setInterval(function () {
        const paymentContainers = document.querySelectorAll('[data-payment-container]');
        paymentContainers.forEach(container => {
            const paymentId = container.dataset.paymentContainer;
            const statusContainer = container.querySelector('.payment-status');

            // Only check status for buttons that are still pending
            if (statusContainer && !statusContainer.classList.contains('payment-completed')) {
                checkPaymentStatus(container);
            }
        });
    }, 5000); // Check every 5 seconds

    // Listen for any new payment buttons added to the DOM
    const chatContainer = document.getElementById('chat-messages');
    if (chatContainer) {
        // Use MutationObserver to detect when new messages are added
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length) {
                    // Check if new payment buttons were added
                    processPaymentElements();
                }
            });
        });

        // Start observing the chat container
        observer.observe(chatContainer, { childList: true, subtree: true });
    }

    /**
     * Process payment buttons and confirmation messages in the chat
     */
    function processPaymentElements() {
        const messageContents = document.querySelectorAll('.message-content');

        messageContents.forEach(content => {
            const text = content.innerHTML;

            // Process payment buttons
            if (text.includes('[PAYMENT_BUTTON]')) {
                // Extract payment ID from the message
                const buttonPattern = /\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/;
                const match = text.match(buttonPattern);

                if (match && match[1]) {
                    const paymentId = match[1];
                    const requestId = getRequestId();
                    const baseUrl = getBaseUrl();

                    // Create payment container HTML with the correct URL format
                    // Use payDirect with query parameters instead of URL segments
                    const paymentHtml = `
                        <div class="payment-container mt-3" data-payment-container="${paymentId}">
                            <!-- Payment button -->
                            <div class="payment-button-container">
                                <a href="${baseUrl}/writing-service-requests/payDirect?id=${requestId}&paymentId=${encodeURIComponent(paymentId)}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center text-sm payment-button"
                                   data-payment-id="${paymentId}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Make Payment
                                </a>
                            </div>
                            <!-- Payment status indicator (hidden initially) -->
                            <div class="payment-status hidden mt-2 text-sm flex items-center">
                                <span class="status-icon mr-1">⏳</span>
                                <span class="status-text">Checking payment status...</span>
                                <span class="status-date ml-2"></span>
                            </div>
                        </div>
                    `;

                    // Replace the button tag with payment container
                    content.innerHTML = text.replace(buttonPattern, paymentHtml);

                    // Initialize payment status check
                    const container = content.querySelector(`[data-payment-container="${paymentId}"]`);
                    if (container) {
                        checkPaymentStatus(container);
                    }
                }
            }

            // Process payment confirmation messages
            if (text.includes('[PAYMENT_CONFIRMATION]')) {
                const confirmPattern = /\[PAYMENT_CONFIRMATION\](.*?)\[\/PAYMENT_CONFIRMATION\]/;
                const match = text.match(confirmPattern);

                if (match) {
                    // Format markdown-style bold text
                    let newContent = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                    // Remove the confirmation tag (it's just a marker)
                    newContent = newContent.replace(confirmPattern, '');

                    // Wrap the entire message in a special payment confirmation style
                    const wrapperDiv = document.createElement('div');
                    wrapperDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg';

                    // Add a success icon header
                    wrapperDiv.innerHTML = `
                        <div class="flex items-center mb-2">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="font-bold text-green-700">Payment Confirmed</span>
                        </div>
                        ${newContent}
                    `;

                    // Replace the content
                    content.innerHTML = '';
                    content.appendChild(wrapperDiv);

                    // Add a special class to the message container
                    const messageContainer = content.closest('.flex');
                    if (messageContainer) {
                        messageContainer.classList.add('payment-confirmation-message');
                    }
                }
            }
        });
    }

    /**
     * Check payment status and update UI accordingly
     * @param {HTMLElement} container - The payment container element
     */
    function checkPaymentStatus(container) {
        if (!container) {
            return;
        }

        const paymentId = container.dataset.paymentContainer;
        const paymentButton = container.querySelector('.payment-button');
        const statusContainer = container.querySelector('.payment-status');

        if (!statusContainer || !paymentButton) {
            return;
        }

        const statusIcon = statusContainer.querySelector('.status-icon');
        const statusText = statusContainer.querySelector('.status-text');
        const statusDate = statusContainer.querySelector('.status-date');

        // Show checking status immediately
        statusContainer.classList.remove('hidden');

        // Build the status check URL
        const requestId = getRequestId();
        const baseUrl = getBaseUrl();
        const statusUrl = `${baseUrl}/writing-service-requests/check-payment-status/${requestId}/${encodeURIComponent(paymentId)}`;

        // Make the API call
        fetch(statusUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.paid === true || data.status === 'paid') {
                        // Mark this container as complete to avoid redundant checks
                        statusContainer.classList.add('payment-completed');

                        // Update button to show completed state
                        paymentButton.innerHTML = `
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Payment Completed
                        `;
                        paymentButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        paymentButton.classList.add('bg-green-600', 'cursor-default');
                        paymentButton.removeAttribute('href');
                        paymentButton.style.pointerEvents = 'none';

                        // Update status text
                        statusIcon.textContent = '✅';
                        statusText.textContent = 'Payment completed';
                        statusText.classList.add('text-green-600', 'font-medium');

                        // Add payment date if available
                        if (data.details && data.details.date) {
                            const paymentDate = new Date(data.details.date * 1000);
                            statusDate.textContent = 'on ' + paymentDate.toLocaleDateString(undefined, {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }

                        // Show payment success toast and update global payment status
                        showPaymentSuccessToast();
                        updateGlobalPaymentStatus(data.details);
                    } else {
                        // Payment is pending
                        statusIcon.textContent = '⏳';
                        statusText.textContent = 'Payment pending';
                        statusText.classList.add('text-yellow-600');

                        // Check again in 3 seconds for fast feedback
                        setTimeout(() => checkPaymentStatus(container), 3000);
                    }
                } else {
                    // Error checking payment
                    statusContainer.classList.add('hidden');
                }
            })
            .catch(err => {
                console.error('Error checking payment status:', err);
                statusContainer.classList.add('hidden');
            });
    }

    /**
     * Process payment success parameters in URL
     */
    function processPaymentSuccess() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('payment_success') === 'true' || urlParams.get('payment_already_completed') === 'true') {
            showPaymentSuccessToast();

            // Check all payment buttons and update their status
            const paymentContainers = document.querySelectorAll('[data-payment-container]');
            paymentContainers.forEach(container => {
                checkPaymentStatus(container);
            });
        }
    }

    /**
     * Show payment success toast notification
     */
    function showPaymentSuccessToast() {
        const toast = document.getElementById('payment-success-toast');
        if (!toast) {
            // Create toast if it doesn't exist
            const toastDiv = document.createElement('div');
            toastDiv.id = 'payment-success-toast';
            toastDiv.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center';
            toastDiv.innerHTML = `
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <div class="font-bold">Payment Successful!</div>
                    <div class="text-sm">Your payment has been processed.</div>
                </div>
            `;
            document.body.appendChild(toastDiv);

            // Animate in
            setTimeout(() => {
                toastDiv.style.transform = 'translateY(0)';
                toastDiv.style.opacity = '1';
            }, 100);

            // Animate out after 5 seconds
            setTimeout(() => {
                toastDiv.style.transform = 'translateY(20px)';
                toastDiv.style.opacity = '0';
                setTimeout(() => {
                    toastDiv.remove();
                }, 500);
            }, 5000);
        } else {
            // Show existing toast
            toast.classList.remove('hidden', 'translate-y-20', 'opacity-0');

            // Hide after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 500);
            }, 5000);
        }
    }

    /**
     * Update global payment status card
     * @param {Object} details - Payment details
     */
    function updateGlobalPaymentStatus(details) {
        const statusCard = document.getElementById('payment-status-card');
        const statusContent = document.getElementById('payment-status-content');

        if (statusCard && statusContent && details) {
            // Show the card
            statusCard.classList.remove('hidden');

            // Format payment date
            let dateStr = 'Recently';
            if (details.date) {
                const paymentDate = new Date(details.date * 1000);
                dateStr = paymentDate.toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            // Format amount
            const amount = details.amount ? `$${parseFloat(details.amount).toFixed(2)}` : 'Paid';

            // Update content
            statusContent.innerHTML = `
                <div class="flex items-center mb-3">
                    <div class="bg-green-100 p-2 rounded-full mr-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Payment Complete</h4>
                        <p class="text-sm text-gray-500">Processed on ${dateStr}</p>
                    </div>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-2">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-medium text-gray-900">${amount}</span>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-2">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium text-green-600">Completed</span>
                </div>
                ${details.transaction_id ? `
                <div class="flex justify-between border-t border-gray-100 pt-2">
                    <span class="text-gray-600">Transaction ID:</span>
                    <span class="font-medium text-gray-900">${details.transaction_id}</span>
                </div>` : ''}
            `;
        }
    }

    /**
     * Get the writing service request ID from the page
     * @returns {string} The request ID
     */
    function getRequestId() {
        // Try to get from data attribute first
        const requestContainer = document.querySelector('[data-request-id]');
        if (requestContainer && requestContainer.dataset.requestId) {
            return requestContainer.dataset.requestId;
        }

        // Fallback to URL parsing
        const pathParts = window.location.pathname.split('/');
        // Look for the ID in the URL (usually the last segment)
        for (let i = pathParts.length - 1; i >= 0; i--) {
            if (pathParts[i] && pathParts[i].length > 10) {
                // IDs are typically long strings
                return pathParts[i];
            }
        }

        return '';
    }

    /**
     * Get base URL for API calls
     * @returns {string} The base URL
     */
    function getBaseUrl() {
        return window.location.origin;
    }

    /**
     * Play notification sound for payment events
     */
    function playNotificationSound() {
        try {
            const audio = new Audio('/webroot/sounds/notification.mp3');
            audio.play().catch(e => console.log('Audio playback failed:', e));
        } catch (e) {
            console.log('Audio playback not supported');
        }
    }
});
