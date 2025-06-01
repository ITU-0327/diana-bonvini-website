/**
 * Coaching Service Payment Handling
 *
 * This script handles payment status checks and UI updates for the coaching service
 * payment system. It provides functions to:
 * - Check payment status
 * - Update payment UI based on status
 * - Add payment confirmation messages to the chat
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Coaching service payments module initialized');
    
    // Set a flag to prevent automatic payment completion on page load
    window.initialPageLoad = true;
    
    // Process any existing payment buttons on page load
    processPaymentElements();

    // Check for payment success in URL (only when returning from Stripe)
    processPaymentSuccess();

    // Initialize payment request form handling for admin
    initPaymentRequestForm();

    // Add click handler for payment buttons to handle errors
    document.addEventListener('click', function(e) {
        const paymentButton = e.target.closest('.payment-button');
        if (paymentButton && paymentButton.dataset.fallbackUrl) {
            e.preventDefault();
            console.log('Payment button clicked, attempting primary URL first');
            
            // Try the primary URL first
            const primaryUrl = paymentButton.getAttribute('href');
            const fallbackUrl = paymentButton.dataset.fallbackUrl;
            
            // Navigate to the primary URL but add an error handler
            window.location.href = primaryUrl;
            
            // Set a small timeout to check if navigation failed
            setTimeout(function() {
                // If we're still here, try the fallback URL
                console.log('Primary URL navigation might have failed, trying fallback URL');
                window.location.href = fallbackUrl;
            }, 500);
        }
    });

    // Set up a recurring check for pending payment buttons (every 5 seconds)
    // But delay the first check to prevent immediately marking payments as completed
    setTimeout(function() {
        // Clear the initial page load flag after a delay
        window.initialPageLoad = false;
        
        // Set up the recurring check
        setInterval(checkPendingPayments, 5000);
    }, 3000);

    // Set up real-time message fetching with AJAX (poll every 3 seconds)
    setupRealtimeMessageFetching();

    // Restore chat scroll position on page load
    restoreChatScrollPosition();

    // Listen for any new payment buttons added to the DOM
    const chatContainer = document.getElementById('chat-messages');
    if (chatContainer) {
        console.log('Chat container found, setting up MutationObserver');
        // Use MutationObserver to detect when new messages are added
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length) {
                    console.log('Mutation observed in chat container');
                    // Check if new payment buttons were added
                    processPaymentElements();
                }
            });
        });

        // Start observing the chat container
        observer.observe(chatContainer, { childList: true, subtree: true });

        // Save scroll position before page unloads/refreshes
        window.addEventListener('beforeunload', function() {
            saveChatScrollPosition(chatContainer);
        });
    }

    /**
     * Process payment buttons and confirmation messages in the chat
     */
    function processPaymentElements() {
        console.log('Processing payment elements in the chat');
        const messageContents = document.querySelectorAll('.message-content');
        console.log(`Found ${messageContents.length} message contents to process`);

        messageContents.forEach(content => {
            const text = content.innerHTML;
            
                                // Process payment buttons
            if (text.includes('[PAYMENT_BUTTON]')) {
                console.log('Found [PAYMENT_BUTTON] tag in message content');
                // Extract payment ID from the message
                const buttonPattern = /\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/;
                const match = text.match(buttonPattern);

                if (match && match[1]) {
                    const paymentId = match[1];
                    console.log(`Payment ID extracted: ${paymentId}`);
                    const requestId = getRequestId();
                    const baseUrl = getBaseUrl();
                    console.log(`Request ID: ${requestId}, Base URL: ${baseUrl}`);

                    // Use the same payment URL format as writing service for consistency
                    // This will use the standard URL path structure that the controller expects
                    // But we have a fallback to payDirect with query parameters if path-based URL doesn't work
                    const paymentHtml = `
                        <div class="payment-container mt-3" data-payment-container="${paymentId}" data-request-id="${requestId}">
                            <!-- Payment button -->
                            <div class="payment-button-container">
                                <a href="${baseUrl}/coaching-service-requests/pay/${requestId}/${encodeURIComponent(paymentId)}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center text-sm payment-button"
                                   data-payment-id="${paymentId}" data-fallback-url="${baseUrl}/coaching-service-requests/payDirect?id=${requestId}&paymentId=${encodeURIComponent(paymentId)}">
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
                    console.log('Replacing [PAYMENT_BUTTON] tag with HTML payment button');
                    content.innerHTML = text.replace(buttonPattern, paymentHtml);

                    // Initialize payment status check
                    const container = content.querySelector(`[data-payment-container="${paymentId}"]`);
                    if (container) {
                        console.log('Container found, initializing payment status check');
                        checkPaymentStatus(container);
                    } else {
                        console.log('Payment container not found after replacement');
                    }
                }
            } else {
                console.log('No [PAYMENT_BUTTON] tag found in this message');
            }

            // Process payment confirmation messages
            if (text.includes('[PAYMENT_CONFIRMATION]')) {
                console.log('Found [PAYMENT_CONFIRMATION] tag in message');
                const confirmPattern = /\[PAYMENT_CONFIRMATION\](.*?)\[\/PAYMENT_CONFIRMATION\]/;
                const match = text.match(confirmPattern);

                if (match) {
                    // Look for a payment ID in the confirmation text
                    const confirmationText = match[1];
                    // Try to extract the payment ID using a pattern like "Payment ID: CSR_XYZ"
                    const paymentIdMatch = confirmationText.match(/Payment ID:\s*([A-Za-z0-9_|-]+)/i);
                    const confirmedPaymentId = paymentIdMatch ? paymentIdMatch[1] : null;

                    // Format markdown-style bold text
                    let newContent = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                    // Remove the confirmation tag (it's just a marker)
                    newContent = newContent.replace(/\[PAYMENT_CONFIRMATION\]|\[\/PAYMENT_CONFIRMATION\]/g, '');

                    // Wrap the entire message in a special payment confirmation style
                    const wrapperDiv = document.createElement('div');
                    wrapperDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg';

                    // Store the payment ID in the confirmation element's data attribute
                    if (confirmedPaymentId) {
                        wrapperDiv.dataset.confirmedPaymentId = confirmedPaymentId;
                    }

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
                        // Also add payment ID to the message container if available
                        if (confirmedPaymentId) {
                            messageContainer.dataset.confirmedPaymentId = confirmedPaymentId;
                        }
                    }
                    
                    // If we have a confirmed payment ID, only update matching payment buttons
                    if (confirmedPaymentId) {
                        const paymentContainers = document.querySelectorAll(`[data-payment-container="${confirmedPaymentId}"]`);
                        paymentContainers.forEach(container => {
                            const statusContainer = container.querySelector('.payment-status');
                            if (statusContainer && !statusContainer.classList.contains('payment-completed')) {
                                forcePaymentStatusComplete(container, true);
                            }
                        });
                    }
                    
                    // Also update global payment status card
                    updateGlobalPaymentStatus({
                        date: Date.now() / 1000,
                        amount: document.querySelector('.text-green-600')?.textContent || 'Paid',
                        transaction_id: confirmedPaymentId || ('CONFIRMED-' + Math.random().toString(36).substr(2, 8).toUpperCase())
                    });
                    
                    // Show success toast to indicate payment is verified
                    showPaymentSuccessToast();
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
            console.error('checkPaymentStatus called with null container');
            return;
        }

        const paymentId = container.dataset.paymentContainer;
        const paymentButton = container.querySelector('.payment-button');
        const statusContainer = container.querySelector('.payment-status');

        console.log(`Checking payment status for container with ID: ${paymentId}`);

        if (!statusContainer || !paymentButton) {
            console.error('Missing status container or payment button elements');
            return;
        }

        // Skip already completed payments
        if (statusContainer.classList.contains('payment-completed')) {
            console.log('Payment already marked as completed, skipping status check');
            return;
        }

        // Skip payments that are already being processed
        if (container.dataset.checkingStatus === 'true') {
            console.log('Payment status check already in progress, skipping');
            return;
        }
        
        container.dataset.checkingStatus = 'true';

        const statusIcon = statusContainer.querySelector('.status-icon');
        const statusText = statusContainer.querySelector('.status-text');
        const statusDate = statusContainer.querySelector('.status-date');

        // Show checking status immediately
        statusContainer.classList.remove('hidden');
        
        // Set initial status text
        statusIcon.textContent = '⏳';
        statusText.textContent = 'Checking payment status...';
        if (statusText.classList.contains('text-green-600')) {
            statusText.classList.remove('text-green-600', 'font-medium');
        }

        // Build the status check URL
        const requestId = getRequestId();
        const baseUrl = getBaseUrl();
        
        // Use POST request with JSON body for payment status checks
        const statusUrl = `${baseUrl}/coaching-service-requests/check-payment-status/${requestId}`;

        console.log(`Making API call to: ${statusUrl} with payment ID: ${paymentId}`);

        // Make the API call with POST method and JSON body
        fetch(statusUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({
                paymentIds: [paymentId]
            })
        })
            .then(response => {
                console.log(`Response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                container.dataset.checkingStatus = 'false';
                
                console.log(`Payment status response:`, data);
                
                // Check if payment is completed based on response format
                if (data.success === true && data.payments && data.payments.length > 0) {
                    // Find the payment that matches our ID
                    const paymentData = data.payments.find(p => p.id === paymentId);
                    
                    if (paymentData && paymentData.isPaid === true) {
                        console.log('Payment marked as paid, updating UI');
                        
                        // Update payment date if available
                        if (paymentData.paidDate) {
                            const date = new Date(paymentData.paidDate);
                            statusDate.textContent = `(${date.toLocaleString()})`;
                        }
                        
                        forcePaymentStatusComplete(container, true);
                    } else {
                        // Payment not completed yet, show status
                        console.log('Payment still pending');
                        statusIcon.textContent = '⏳';
                        statusText.textContent = 'Payment pending';
                    }
                } else {
                    // Payment not completed yet, show status
                    console.log('Payment still pending or error in response');
                    statusIcon.textContent = '⏳';
                    statusText.textContent = 'Payment pending';
                }
            })
            .catch(error => {
                container.dataset.checkingStatus = 'false';
                console.error('Error checking payment status:', error);
                
                // Show error status
                statusIcon.textContent = '⚠️';
                statusText.textContent = 'Status check failed';
            });
    }

    /**
     * Force update payment status to completed
     * @param {HTMLElement} container - The payment container element
     * @param {boolean} isServerConfirmed - Whether the completion is confirmed by server
     */
    function forcePaymentStatusComplete(container, isServerConfirmed) {
        if (!container) {
            return;
        }

        const statusContainer = container.querySelector('.payment-status');
        const paymentButton = container.querySelector('.payment-button');
        
        if (!statusContainer || !paymentButton) {
            return;
        }
        
        // Skip if already marked as completed
        if (statusContainer.classList.contains('payment-completed')) {
            return;
        }

        console.log(`Marking payment as completed: ${container.dataset.paymentContainer}`);

        // Update button and status
        paymentButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        paymentButton.classList.add('bg-green-600', 'hover:bg-green-700', 'cursor-default');
        paymentButton.innerHTML = `
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Payment Complete
        `;
        
        // Remove href to make it non-clickable
        paymentButton.removeAttribute('href');
        
        // Update status
        const statusIcon = statusContainer.querySelector('.status-icon');
        const statusText = statusContainer.querySelector('.status-text');
        
        statusIcon.textContent = '✅';
        statusText.textContent = 'Payment confirmed';
        statusText.classList.add('text-green-600', 'font-medium');
        
        // Mark as completed
        statusContainer.classList.add('payment-completed');
        
        // Add extra verification if server-confirmed
        if (isServerConfirmed) {
            statusContainer.classList.add('server-confirmed');
            
            // Look for payment confirmation message and highlight if found
            const paymentId = container.dataset.paymentContainer;
            checkForPaymentConfirmationMessage(paymentId);
        }
    }

    /**
     * Check if there's already a payment confirmation message for this payment ID
     * @param {string} paymentId - The payment ID to look for
     */
    function checkForPaymentConfirmationMessage(paymentId) {
        if (!paymentId) return;
        
        // Look for confirmation messages with this payment ID
        const confirmationMessages = document.querySelectorAll('[data-confirmed-payment-id]');
        
        confirmationMessages.forEach(element => {
            const confirmedId = element.dataset.confirmedPaymentId;
            
            // If this message confirms our payment ID or contains it
            if (confirmedId === paymentId || paymentId.includes(confirmedId) || confirmedId.includes(paymentId)) {
                // Highlight the message
                element.classList.add('highlighted-confirmation');
                
                // If it's a message container, scroll to it
                if (element.classList.contains('flex')) {
                    setTimeout(() => {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 1000);
                }
            }
        });
    }

    /**
     * Process payment success from URL parameters (when returning from payment gateway)
     */
    function processPaymentSuccess() {
        const urlParams = new URLSearchParams(window.location.search);
        const paymentSuccess = urlParams.get('payment_success');
        const paymentId = urlParams.get('paymentId');
        
        if (paymentSuccess === 'true' && paymentId) {
            console.log(`Payment success detected for ID: ${paymentId}`);
            
            // Find and update the payment container
            const container = findPaymentContainer(paymentId);
            
            if (container) {
                forcePaymentStatusComplete(container, true);
                
                // Show success toast
                showPaymentSuccessToast();
                
                // Remove params from URL without refreshing
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
                window.history.replaceState({}, document.title, cleanUrl);
                
                // If we have a global payment status section, update it
                updateGlobalPaymentStatus({
                    date: Date.now() / 1000,
                    amount: container.querySelector('.payment-button')?.textContent || 'Paid',
                    transaction_id: paymentId
                });
            } else {
                console.log(`Could not find payment container for ID: ${paymentId}`);
            }
        }
    }

    /**
     * Find a payment container by payment ID
     * @param {string} paymentId - The payment ID to look for
     * @returns {HTMLElement|null} - The container element or null if not found
     */
    function findPaymentContainer(paymentId) {
        const containers = document.querySelectorAll('[data-payment-container]');
        
        for (let i = 0; i < containers.length; i++) {
            const container = containers[i];
            const containerId = container.dataset.paymentContainer;
            
            if (containerId === paymentId || containerId.includes(paymentId) || paymentId.includes(containerId)) {
                return container;
            }
        }
        
        return null;
    }

    /**
     * Set up real-time message fetching
     */
    function setupRealtimeMessageFetching() {
        // Only set up if we're on a service request view page
        if (!document.getElementById('chat-messages')) {
            return;
        }

        // Start polling for new messages
        const pollInterval = 3000; // 3 seconds
        setInterval(fetchNewMessages, pollInterval);

        // Function to fetch new messages
        function fetchNewMessages() {
            const chatContainer = document.getElementById('chat-messages');
            if (!chatContainer) return;
            
            // Get request ID from URL
            const requestId = getRequestId();
            if (!requestId) return;
            
            // Get the last message ID to use as a reference
            const messages = document.querySelectorAll('[data-message-id]');
            let lastMessageId = null;
            
            if (messages.length > 0) {
                lastMessageId = messages[messages.length - 1].dataset.messageId;
            }
            
            // Skip if we don't have a last message ID
            if (!lastMessageId) return;
            
            const baseUrl = getBaseUrl();
            const fetchUrl = `${baseUrl}/coaching-service-requests/fetch-messages?id=${requestId}&lastMessageId=${lastMessageId}`;
            
            // Don't show loading indicator for automatic polling
            // Only show it for manual refresh or initial load
            
            fetch(fetchUrl, {
                headers: {
                    'X-CSRF-Token': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        console.log(`Received ${data.messages.length} new messages`);
                        
                        // Hide loading indicator if visible
                        hideLoadingIndicator();
                        
                        // Add each message to the chat
                        data.messages.forEach(message => {
                            addMessageToChat(message);
                        });
                        
                        // Process any new payment elements
                        processPaymentElements();
                        
                        // Play notification sound
                        playNotificationSound();
                        
                        // Show notification if not viewing the tab
                        if (document.hidden) {
                            showNewMessageNotification(data.messages.length);
                        }
                        
                        // Scroll to bottom if we're already at the bottom
                        const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer.clientHeight + 100;
                        if (isAtBottom) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching new messages:', error);
                    hideLoadingIndicator();
                });
        }
    }

    /**
     * Show success toast notification
     */
    function showPaymentSuccessToast() {
        // Create toast element if it doesn't exist
        let toast = document.getElementById('payment-success-toast');
        
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'payment-success-toast';
            toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white p-4 rounded-lg shadow-lg z-50 transform transition-transform duration-300 ease-out translate-y-20 opacity-0';
            toast.innerHTML = `
                <div class="flex items-center">
                    <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <div class="font-bold">Payment Successful</div>
                        <div class="text-sm">Your payment has been confirmed.</div>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
        }
        
        // Show the toast
        setTimeout(() => {
            toast.classList.remove('translate-y-20', 'opacity-0');
        }, 100);
        
        // Hide the toast after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 5000);
    }

    /**
     * Update global payment status card if it exists
     * @param {Object} details - Payment details
     */
    function updateGlobalPaymentStatus(details) {
        const paymentStatusCard = document.querySelector('.payment-status-card');
        if (!paymentStatusCard) return;
        
        const statusBadge = paymentStatusCard.querySelector('.status-badge');
        const dateElement = paymentStatusCard.querySelector('.payment-date');
        const amountElement = paymentStatusCard.querySelector('.payment-amount');
        const transactionElement = paymentStatusCard.querySelector('.transaction-id');
        
        // Update status badge
        if (statusBadge) {
            statusBadge.textContent = 'Paid';
            statusBadge.classList.remove('bg-yellow-100', 'text-yellow-800');
            statusBadge.classList.add('bg-green-100', 'text-green-800');
        }
        
        // Update date
        if (dateElement && details.date) {
            const date = new Date(details.date * 1000);
            dateElement.textContent = date.toLocaleString();
        }
        
        // Update amount
        if (amountElement && details.amount) {
            amountElement.textContent = details.amount;
        }
        
        // Update transaction ID
        if (transactionElement && details.transaction_id) {
            transactionElement.textContent = details.transaction_id;
        }
        
        // Add paid class to entire card
        paymentStatusCard.classList.add('payment-paid');
        
        // Remove any pending classes or indicators
        const pendingIndicator = paymentStatusCard.querySelector('.pending-indicator');
        if (pendingIndicator) {
            pendingIndicator.remove();
        }
    }

    /**
     * Get the coaching service request ID from the current URL
     * @returns {string|null} The request ID or null if not found
     */
    function getRequestId() {
        // Try to get from data attribute first (most reliable)
        const requestContainer = document.querySelector('[data-request-id]');
        if (requestContainer && requestContainer.dataset.requestId) {
            console.log(`Request ID extracted from data attribute: ${requestContainer.dataset.requestId}`);
            return requestContainer.dataset.requestId;
        }
        
        // Try to extract from URL path
        const pathMatch = window.location.pathname.match(/coaching-service-requests\/(?:view|pay|payDirect)\/([a-zA-Z0-9_-]+)/);
        if (pathMatch && pathMatch[1]) {
            console.log(`Request ID extracted from path: ${pathMatch[1]}`);
            return pathMatch[1];
        }
        
        // Try to extract from query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const idParam = urlParams.get('id');
        if (idParam) {
            console.log(`Request ID extracted from query param: ${idParam}`);
            return idParam;
        }
        
        // As a last resort, try to find the ID in a hidden field
        const hiddenIdField = document.querySelector('input[name="id"], input[name="coaching_service_request_id"]');
        if (hiddenIdField && hiddenIdField.value) {
            console.log(`Request ID extracted from hidden field: ${hiddenIdField.value}`);
            return hiddenIdField.value;
        }
        
        console.warn('Could not find request ID in URL or data attributes');
        return null;
    }

    /**
     * Get the base URL for the application
     * @returns {string} The base URL
     */
    function getBaseUrl() {
        const url = window.location.origin;
        console.log(`Base URL: ${url}`);
        return url;
    }

    /**
     * Get CSRF token from the page
     * @returns {string} CSRF token or empty string if not found
     */
    function getCsrfToken() {
        const csrfElement = document.querySelector('meta[name="csrfToken"]') || document.querySelector('input[name="_csrfToken"]');
        if (csrfElement) {
            const token = csrfElement.content || csrfElement.value;
            console.log('Found CSRF token:', token ? token.substring(0, 10) + '...' : 'empty');
            return token || '';
        }
        console.warn('CSRF token not found in page');
        return '';
    }

    /**
     * Play a notification sound for new messages
     */
    function playNotificationSound() {
        const audio = new Audio('/sound/notification.mp3');
        audio.volume = 0.5;
        
        try {
            audio.play().catch(e => {
                console.log('Auto-play prevented: ', e);
                // This is expected if the user hasn't interacted with the page yet
            });
        } catch (e) {
            console.log('Error playing notification sound: ', e);
        }
    }

    /**
     * Check for any pending payments and update their status
     */
    function checkPendingPayments() {
        // Skip if we're in the initial page load phase
        if (window.initialPageLoad) {
            return;
        }
        
        const paymentContainers = document.querySelectorAll('[data-payment-container]');
        console.log(`Checking ${paymentContainers.length} payment containers for pending payments`);
        
        paymentContainers.forEach(container => {
            // Skip containers that are already marked as completed
            const statusContainer = container.querySelector('.payment-status');
            if (statusContainer && statusContainer.classList.contains('payment-completed')) {
                console.log(`Payment ${container.dataset.paymentContainer} already marked as completed, skipping`);
                return;
            }
            
            // Check payment status
            console.log(`Checking payment status for container: ${container.dataset.paymentContainer}`);
            checkPaymentStatus(container);
        });
    }

    /**
     * Save the chat container scroll position to session storage
     * @param {HTMLElement} chatContainer - The chat container element
     */
    function saveChatScrollPosition(chatContainer) {
        if (!chatContainer) return;
        
        const scrollPosition = chatContainer.scrollTop;
        const scrollHeight = chatContainer.scrollHeight;
        
        // Calculate scroll percentage (0 = top, 1 = bottom)
        const scrollPercentage = scrollPosition / (scrollHeight - chatContainer.clientHeight);
        
        // Only save position if we have scrolled down
        if (scrollPosition > 0) {
            const requestId = getRequestId();
            if (requestId) {
                sessionStorage.setItem(`chat_scroll_${requestId}`, scrollPercentage.toString());
                console.log(`Saved scroll position: ${scrollPercentage.toFixed(2)} for request ${requestId}`);
            }
        }
    }

    /**
     * Restore chat container scroll position from session storage
     */
    function restoreChatScrollPosition() {
        const chatContainer = document.getElementById('chat-messages');
        if (!chatContainer) return;
        
        const requestId = getRequestId();
        if (!requestId) return;
        
        const savedPosition = sessionStorage.getItem(`chat_scroll_${requestId}`);
        if (savedPosition) {
            // Convert back to a number
            const scrollPercentage = parseFloat(savedPosition);
            
            // Calculate the actual scroll position
            const targetPosition = scrollPercentage * (chatContainer.scrollHeight - chatContainer.clientHeight);
            
            // Set the scroll position
            chatContainer.scrollTop = targetPosition;
            console.log(`Restored scroll position: ${scrollPercentage.toFixed(2)} for request ${requestId}`);
        } else {
            // Default to scrolling to bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    /**
     * Add a new message to the chat
     * @param {Object} message - The message object to add
     */
    function addMessageToChat(message) {
        const chatContainer = document.getElementById('chat-messages');
        if (!chatContainer) return;

        const isAdmin = message.sender && message.sender.type === 'admin';
        const senderName = message.sender && message.sender.name ? message.sender.name : (isAdmin ? 'Admin' : 'Client');
        const timeText = message.formattedTime || (message.timestamp ? new Date(message.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '');

        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isAdmin ? 'admin-message' : 'client-message'}`;
        messageDiv.setAttribute('data-message-id', message.id);

        messageDiv.innerHTML = `
            <div class="message-header">
                <span class="message-sender">${senderName}</span>
                <span class="message-time">${timeText}</span>
            </div>
            <div class="message-content">
                ${message.content}
            </div>
        `;

        chatContainer.appendChild(messageDiv);
    }

    /**
     * Show loading indicator in the chat container
     */
    function showLoadingIndicator() {
        const loadingIndicator = document.getElementById('chat-loading');
        if (loadingIndicator) {
            loadingIndicator.classList.add('active');
        }
    }

    /**
     * Hide loading indicator in the chat container
     */
    function hideLoadingIndicator() {
        const loadingIndicator = document.getElementById('chat-loading');
        if (loadingIndicator) {
            loadingIndicator.classList.remove('active');
        }
    }

    /**
     * Show a notification for new messages
     * @param {number} count - The number of new messages
     */
    function showNewMessageNotification(count) {
        // Try to use the Notification API if available and permitted
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('New Message', {
                body: `You have ${count} new message${count > 1 ? 's' : ''}`,
                icon: '/img/logo.png'
            });
        }
        
        // Also update page title
        const originalTitle = document.title;
        const newTitle = `(${count}) New Message${count > 1 ? 's' : ''} - ${originalTitle.replace(/^\(\d+\) New Messages? - /, '')}`;
        document.title = newTitle;
        
        // Restore title when user comes back to the page
        window.addEventListener('focus', function restoreTitle() {
            document.title = originalTitle;
            window.removeEventListener('focus', restoreTitle);
        });
    }

    /**
     * Initialize payment request form handling for admin
     */
    function initPaymentRequestForm() {
        // This function handles the payment request form in the admin interface
        const paymentRequestForm = document.getElementById('paymentRequestForm');
        const sendPaymentRequestBtn = document.getElementById('sendPaymentRequestBtn');

        console.log('Checking for payment request form:', paymentRequestForm ? 'Found' : 'Not Found');

        if (paymentRequestForm && sendPaymentRequestBtn) {
            console.log('Setting up improved payment request form handler');
            
            // Prevent double initialization
            if (paymentRequestForm.dataset.initialized === 'true') {
                return;
            }
            paymentRequestForm.dataset.initialized = 'true';
            
            // Validate the amount input to ensure it only contains valid currency formats
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                amountInput.addEventListener('input', function(e) {
                    // Allow numeric input, decimal point, and currency symbols
                    let value = e.target.value;
                    
                    // Log the current input for debugging
                    console.log('Amount input value:', value);
                    
                    // Highlight the input in green when valid, red when invalid
                    if (value) {
                        // Strip currency symbols and commas for validation
                        const numericValue = value.replace(/[$,]/g, '').trim();
                        const isValid = !isNaN(parseFloat(numericValue)) && parseFloat(numericValue) > 0;
                        
                        if (isValid) {
                            e.target.classList.remove('is-invalid');
                            e.target.classList.add('is-valid');
                        } else {
                            e.target.classList.remove('is-valid');
                            e.target.classList.add('is-invalid');
                        }
                    }
                });
            }
            
            // Add submit handler with improved validation
            sendPaymentRequestBtn.addEventListener('click', function(event) {
                console.log('Send payment request button clicked');
                
                // Get form inputs
                const amountInput = document.getElementById('amount');
                const descriptionInput = document.getElementById('description');
                
                if (!amountInput || !descriptionInput) {
                    console.error('Amount or description input elements not found');
                    return;
                }
                
                // Log form values
                console.log('Form input values - Amount:', amountInput.value, 'Description:', descriptionInput.value);
                
                // Get the amount value, strip currency symbols and commas
                let amountValue = amountInput.value.replace(/[$,]/g, '').trim();
                console.log('Cleaned amount value:', amountValue);
                
                // Validate amount
                if (!amountValue || isNaN(parseFloat(amountValue)) || parseFloat(amountValue) <= 0) {
                    console.error('Amount validation failed');
                    
                    // Show validation error
                    amountInput.classList.add('is-invalid');
                    
                    // Create or update error message
                    let errorContainer = amountInput.parentNode.querySelector('.invalid-feedback');
                    if (!errorContainer) {
                        errorContainer = document.createElement('div');
                        errorContainer.className = 'invalid-feedback';
                        amountInput.parentNode.appendChild(errorContainer);
                    }
                    errorContainer.textContent = 'Please enter a valid payment amount greater than 0.';
                    
                    return;
                }
                
                // Validate description
                if (!descriptionInput.value.trim()) {
                    console.error('Description validation failed');
                    
                    // Show validation error
                    descriptionInput.classList.add('is-invalid');
                    
                    // Create or update error message
                    let errorContainer = descriptionInput.parentNode.querySelector('.invalid-feedback');
                    if (!errorContainer) {
                        errorContainer = document.createElement('div');
                        errorContainer.className = 'invalid-feedback';
                        descriptionInput.parentNode.appendChild(errorContainer);
                    }
                    errorContainer.textContent = 'Please enter a payment description.';
                    
                    return;
                }
                
                console.log('Validation passed, submitting payment request form');
                
                // Clear any previous validation errors
                amountInput.classList.remove('is-invalid');
                descriptionInput.classList.remove('is-invalid');
                
                // Show loading state
                const originalButtonContent = sendPaymentRequestBtn.innerHTML;
                sendPaymentRequestBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';
                sendPaymentRequestBtn.disabled = true;
                
                // Submit the form
                console.log('Submitting form:', paymentRequestForm.action);
                
                // Create hidden field with cleaned amount if needed
                let cleanedAmountField = paymentRequestForm.querySelector('input[name="cleaned_amount"]');
                if (!cleanedAmountField) {
                    cleanedAmountField = document.createElement('input');
                    cleanedAmountField.type = 'hidden';
                    cleanedAmountField.name = 'cleaned_amount';
                    paymentRequestForm.appendChild(cleanedAmountField);
                }
                cleanedAmountField.value = amountValue;
                
                // Submit the form
                paymentRequestForm.submit();
            });
        }
    }

}); 