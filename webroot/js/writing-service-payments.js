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
    console.log('Writing service payments module initialized');
    
    // Set a flag to prevent automatic payment completion on page load
    window.initialPageLoad = true;
    
    // Process any existing payment buttons on page load
    processPaymentElements();

    // Check for payment success in URL (only when returning from Stripe)
    processPaymentSuccess();

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

        // Save scroll position before page unloads/refreshes
        window.addEventListener('beforeunload', function() {
            saveChatScrollPosition(chatContainer);
        });
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
                    // Look for a payment ID in the confirmation text
                    const confirmationText = match[1];
                    // Try to extract the payment ID using a pattern like "Payment ID: WSR_XYZ"
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
            return;
        }

        const paymentId = container.dataset.paymentContainer;
        const paymentButton = container.querySelector('.payment-button');
        const statusContainer = container.querySelector('.payment-status');

        if (!statusContainer || !paymentButton) {
            return;
        }

        // Skip already completed payments
        if (statusContainer.classList.contains('payment-completed')) {
            return;
        }

        // Skip payments that are already being processed
        if (container.dataset.checkingStatus === 'true') {
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
        const statusUrl = `${baseUrl}/writing-service-requests/check-payment-status?id=${requestId}&paymentId=${encodeURIComponent(paymentId)}`;

        console.log(`Checking payment status for ID: ${paymentId}`);

        // Make the API call
        fetch(statusUrl)
            .then(response => response.json())
            .then(data => {
                container.dataset.checkingStatus = 'false';
                
                console.log(`Payment status response:`, data);
                
                if (data.success) {
                    // Only consider the payment completed if the server explicitly says it's paid
                    // AND it has success = true
                    if ((data.paid === true || data.status === 'paid') && data.success === true) {
                        console.log(`Payment ${paymentId} is confirmed paid`);
                        
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
                                month: 'long',
                                day: 'numeric'
                            });
                        }
                    } else {
                        // Payment is not yet completed
                        console.log(`Payment ${paymentId} is not paid yet`);
                        
                        // Ensure the button is active for payment
                        if (paymentButton.style.pointerEvents === 'none') {
                            paymentButton.style.pointerEvents = '';
                        }
                        
                        // Update status text to pending
                        statusIcon.textContent = '⏳';
                        statusText.textContent = 'Payment pending';
                        
                        // Make sure the payment button is still clickable
                        paymentButton.classList.remove('bg-green-600', 'cursor-default');
                        paymentButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        
                        // Update button text if needed
                        if (paymentButton.textContent.trim() === 'Payment Completed') {
                            paymentButton.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Make Payment
                            `;
                        }
                    }
                } else {
                    // Error checking payment status
                    console.log('Error checking payment status:', data.message || 'Unknown error');
                    
                    // Update status text to error
                    statusIcon.textContent = '❓';
                    statusText.textContent = 'Status unknown';
                }
            })
            .catch(error => {
                container.dataset.checkingStatus = 'false';
                console.error('Error checking payment status:', error);
                
                // Update status text to error
                statusIcon.textContent = '❌';
                statusText.textContent = 'Error checking status';
            });
    }

    /**
     * Force payment status to completed state (only used by server-side callbacks)
     * @param {HTMLElement} container - The payment container element
     * @param {boolean} isServerConfirmed - Whether this is confirmed by the server
     */
    function forcePaymentStatusComplete(container, isServerConfirmed) {
        if (!container) return;
        
        // Only allow this function to be called from server-confirmed payment callbacks
        if (!isServerConfirmed) {
            console.warn('Attempted to force payment complete without server confirmation');
            return;
        }
        
        console.log('Server confirming payment completed');
        
        const paymentButton = container.querySelector('.payment-button');
        const statusContainer = container.querySelector('.payment-status');
        
        if (!statusContainer || !paymentButton) return;
        
        // Mark this container as complete
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
        const statusIcon = statusContainer.querySelector('.status-icon');
        const statusText = statusContainer.querySelector('.status-text');
        
        statusIcon.textContent = '✅';
        statusText.textContent = 'Payment confirmed';
        statusText.classList.add('text-green-600', 'font-medium');
        
        // Show success toast
        showPaymentSuccessToast();
    }

    /**
     * Check if payment confirmation message exists in chat for a specific payment ID
     * @param {string} paymentId - The payment ID to check for
     * @returns {boolean} True if confirmation message is found
     */
    function checkForPaymentConfirmationMessage(paymentId) {
        // Do not check for payment confirmation messages on initial page load
        // This prevents automatically marking payments as paid without user interaction
        if (window.initialPageLoad === undefined) {
            window.initialPageLoad = true;
            return false;
        }
        
        if (!paymentId) return false;
        
        console.log(`Checking payment confirmation for ID: ${paymentId}`);
        
        // Only consider exact matches for payment IDs from confirmation messages
        const confirmationMessages = document.querySelectorAll('[data-confirmed-payment-id]');
        for (let i = 0; i < confirmationMessages.length; i++) {
            const confirmedId = confirmationMessages[i].dataset.confirmedPaymentId;
            // Must be an exact match and must have come from server (not client-side detected)
            if (confirmedId && confirmedId === paymentId) {
                console.log(`Found payment confirmation match: ${confirmedId}`);
                
                // Additional check: payment confirmation must come from a system message
                const messageContainer = confirmationMessages[i].closest('.flex');
                if (messageContainer && messageContainer.classList.contains('payment-confirmation-message')) {
                    // Check that the message has a server timestamp (more evidence it came from server)
                    if (messageContainer.querySelector('.local-time')) {
                        return true;
                    }
                }
            }
        }
        
        // DO NOT check for confirmation in message content at all - only trust server-generated confirmation data attributes
        
        // No specific payment confirmation found for this ID
        return false;
    }

    /**
     * Process payment success parameters in URL
     * This is called when returning from the Stripe payment gateway
     */
    function processPaymentSuccess() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Only show payment success toast when explicitly indicated in URL
        // The payment_success=true will only be added when returning from a successful Stripe payment
        if (urlParams.get('payment_success') === 'true') {
            console.log('Payment success detected in URL');
            
            // Check if there's a specific payment ID in the URL
            const specificPaymentId = urlParams.get('paymentId');
            if (!specificPaymentId) {
                console.log('No payment ID found in URL, cannot process payment success');
                return;
            }
            
            console.log(`Processing payment success for ID: ${specificPaymentId}`);
            
            // Process payment through server-side to ensure it's properly recorded
            const requestId = getRequestId();
            if (requestId) {
                const baseUrl = getBaseUrl();
                const paymentSuccessUrl = `${baseUrl}/writing-service-requests/paymentSuccess/${requestId}/${specificPaymentId}`;
                
                // Remove the parameter from URL to prevent showing toast on page refresh
                // Do this immediately before making the request
                if (window.history && window.history.replaceState) {
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }
                
                // Make a server request to confirm the payment
                fetch(paymentSuccessUrl)
                    .then(response => response.json())
                    .catch(err => console.error('Error confirming payment:', err))
                    .finally(() => {
                        // Show success toast
                        showPaymentSuccessToast();
                        
                        // Find the specific payment container for this payment ID
                        const matchingContainer = findPaymentContainer(specificPaymentId);
                        if (matchingContainer) {
                            console.log(`Found matching payment container for ID: ${specificPaymentId}`);
                            checkPaymentStatus(matchingContainer);
                        } else {
                            console.log(`No matching payment container found for ID: ${specificPaymentId}`);
                        }
                    });
            }
        }
    }
    
    /**
     * Find the payment container element for a specific payment ID
     * @param {string} paymentId - The payment ID to find
     * @returns {HTMLElement|null} The payment container element if found, null otherwise
     */
    function findPaymentContainer(paymentId) {
        if (!paymentId) return null;
        
        const paymentContainers = document.querySelectorAll('[data-payment-container]');
        for (let i = 0; i < paymentContainers.length; i++) {
            const containerId = paymentContainers[i].dataset.paymentContainer;
            
            // Check for exact match or payment IDs with pipe separator (ID|dbID format)
            if (containerId === paymentId || 
                (paymentId.includes('|') && paymentId.split('|')[0] === containerId)) {
                return paymentContainers[i];
            }
        }
        
        return null;
    }

    /**
     * Set up real-time message fetching with AJAX
     */
    function setupRealtimeMessageFetching() {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;

        const requestId = getRequestId();
        if (!requestId) return;

        // Track the last message ID we've seen
        let lastMessageId = null;

        // Get the ID of the last message in the chat initially
        const allMessages = document.querySelectorAll('#chat-messages [data-message-id]');
        if (allMessages && allMessages.length > 0) {
            const lastMessage = allMessages[allMessages.length - 1];
            lastMessageId = lastMessage.dataset.messageId || null;
            console.log('Starting with last message ID:', lastMessageId);
        }

        // Function to fetch new messages
        function fetchNewMessages() {
            const baseUrl = getBaseUrl();
            
            // When we're doing a full refresh, don't include the lastMessageId
            const includeLastId = window.fullRefreshRequested ? false : Boolean(lastMessageId);
            
            // Build URL with or without lastMessageId
            const url = `${baseUrl}/writing-service-requests/fetch-messages/${requestId}${includeLastId ? '/' + lastMessageId : ''}`;
            
            // Show loading indicator for full refreshes or if specifically requested
            if (window.fullRefreshRequested || window.showLoadingRequested) {
                showLoadingIndicator();
                window.showLoadingRequested = false;
            }
            
            // Clear the full refresh flag
            if (window.fullRefreshRequested) {
                window.fullRefreshRequested = false;
            }
            
            // Make the fetch request with a timeout
            const fetchTimeout = setTimeout(() => {
                console.warn('Fetch timed out, retrying...');
                hideLoadingIndicator();
                // Try again soon
                setTimeout(fetchNewMessages, 2000);
            }, 8000); // 8 second timeout
            
            fetch(url)
                .then(response => {
                    clearTimeout(fetchTimeout);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    // Check if we received any messages
                    if (data.success && data.messages && data.messages.length > 0) {
                        console.log(`Received ${data.messages.length} new messages from server`);
                        
                        // Flag to track if we've added any new messages
                        let newMessagesAdded = 0;
                        
                        // Process each message
                        data.messages.forEach(message => {
                            if (addMessageToChat(message)) {
                                newMessagesAdded++;
                                
                                // Update the last message ID to the highest ID we've seen
                                if (!lastMessageId || message.id > lastMessageId) {
                                    lastMessageId = message.id;
                                }
                            }
                        });
                        
                        // Always scroll to bottom when new messages are added
                        if (newMessagesAdded > 0) {
                            console.log(`Added ${newMessagesAdded} new messages to chat`);
                            
                            // Scroll immediately and then again after a short delay to ensure it works
                            scrollChatToBottom();
                            setTimeout(scrollChatToBottom, 100);
                            
                            // Show visible notification 
                            showNewMessageNotification(newMessagesAdded);
                            
                            // Play notification sound for new messages
                            playNotificationSound();
                        }
                    }
                    
                    // Hide loading indicator after processing
                    hideLoadingIndicator();
                })
                .catch(error => {
                    clearTimeout(fetchTimeout);
                    console.error('Error fetching messages:', error);
                    
                    // Hide loading indicator on error
                    hideLoadingIndicator();
                    
                    // Try again after a short delay if there was an error
                    setTimeout(fetchNewMessages, 5000);
                });
        }

        // Initial fetch with loading indicator
        window.showLoadingRequested = true;
        fetchNewMessages();

        // Make the fetchNewMessages function available globally so it can be triggered manually
        window.fetchNewMessages = fetchNewMessages;
        
        // Function to request a full refresh
        window.refreshChat = function() {
            console.log('Manual chat refresh requested');
            lastMessageId = null;
            window.fullRefreshRequested = true;
            window.showLoadingRequested = true;
            fetchNewMessages();
        };
        
        // Poll for new messages more frequently (every 2 seconds)
        const pollingInterval = setInterval(fetchNewMessages, 2000);
        
        // Also set a timer to refresh the entire chat content occasionally to ensure sync
        const fullRefreshInterval = setInterval(function() {
            // Reset lastMessageId to get all messages
            lastMessageId = null;
            window.fullRefreshRequested = true;
            fetchNewMessages();
            console.log('Performing full chat refresh');
        }, 30000); // Every 30 seconds
        
        // Clear intervals when page is unloaded
        window.addEventListener('beforeunload', function() {
            clearInterval(pollingInterval);
            clearInterval(fullRefreshInterval);
        });
    }

    /**
     * Show payment success toast notification
     */
    function showPaymentSuccessToast() {
        const toast = document.getElementById('payment-success-toast');
        if (toast) {
            toast.classList.remove('hidden', 'translate-y-20', 'opacity-0');

            // Hide toast after 5 seconds
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

    /**
     * Check status of all pending payment buttons
     */
    function checkPendingPayments() {
        const paymentContainers = document.querySelectorAll('[data-payment-container]');
        paymentContainers.forEach(container => {
            const paymentId = container.dataset.paymentContainer;
            const statusContainer = container.querySelector('.payment-status');
            const paymentButton = container.querySelector('.payment-button');

            // Only check status for buttons that are still pending and have not been completed
            if (statusContainer && paymentButton && 
                !statusContainer.classList.contains('payment-completed') && 
                !paymentButton.classList.contains('bg-green-600')) {
                
                // Additional check: make sure we're not checking too frequently
                const lastCheck = parseInt(container.dataset.lastChecked || '0');
                const now = Date.now();
                
                // Only check if it's been at least 5 seconds since the last check
                if (now - lastCheck >= 5000) {
                    container.dataset.lastChecked = now.toString();
                    checkPaymentStatus(container);
                }
            }
        });
    }

    /**
     * Save the chat scroll position to sessionStorage
     * @param {HTMLElement} chatContainer - The chat container element
     */
    function saveChatScrollPosition(chatContainer) {
        if (!chatContainer) return;
        
        // Save both scroll position and scroll height to calculate relative position
        const scrollPosition = chatContainer.scrollTop;
        const scrollHeight = chatContainer.scrollHeight;
        const clientHeight = chatContainer.clientHeight;
        
        // Save data to sessionStorage
        try {
            sessionStorage.setItem('chatScrollPosition', scrollPosition);
            sessionStorage.setItem('chatScrollHeight', scrollHeight);
            sessionStorage.setItem('chatClientHeight', clientHeight);
            
            // Calculate if we're at the bottom (or close to it)
            const isAtBottom = (scrollHeight - scrollPosition - clientHeight) < 50;
            sessionStorage.setItem('chatIsAtBottom', isAtBottom ? 'true' : 'false');
            
            console.log(`Saved chat scroll position: ${scrollPosition}, scroll height: ${scrollHeight}, isAtBottom: ${isAtBottom}`);
        } catch (e) {
            console.error('Error saving chat scroll position:', e);
        }
    }
    
    /**
     * Restore the chat scroll position from sessionStorage
     */
    function restoreChatScrollPosition() {
        const chatContainer = document.getElementById('chat-messages');
        if (!chatContainer) return;
        
        try {
            // Always scroll to bottom by default
            chatContainer.scrollTop = chatContainer.scrollHeight;
            console.log('Scrolled chat to bottom');
            
            // Set a small delay to ensure scrolling works after DOM is fully rendered
            setTimeout(function() {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }, 100);
        } catch (e) {
            console.error('Error scrolling chat to bottom:', e);
        }
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} - Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Format timestamp to match template style (fallback function)
     * @param {Date} date - The date to format
     * @returns {string} - Formatted time string
     */
    function formatTime(date) {
        try {
            // Use TimezoneHelper if available, otherwise fall back to local formatting
            if (window.TimezoneHelper) {
                return window.TimezoneHelper.formatToLocal(date.toISOString(), 'time');
            } else {
                // Fallback - try to use Melbourne timezone or user's local time
                const timezone = 'Australia/Melbourne';
                return date.toLocaleTimeString('en-AU', {
                    timeZone: timezone,
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
        } catch (error) {
            // Ultimate fallback
            return date.toLocaleTimeString(undefined, {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            }).toLowerCase();
        }
    }

    /**
     * Add a new message to the chat
     * @param {Object} message - The message data from the server
     * @returns {boolean} - Whether the message was added
     */
    function addMessageToChat(message) {
        // Skip if this message already exists in the DOM
        if (document.querySelector(`[data-message-id="${message.id}"]`)) {
            return false;
        }
        
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return false;
        
        const isAdmin = message.sender === 'admin';
        // Match the template styling more closely
        const msgClasses = isAdmin
            ? 'bg-gray-200 border-0 ml-0 lg:ml-1 text-gray-800'
            : 'bg-blue-600 text-white border-0 mr-0 lg:mr-1';
        
        const alignmentClasses = isAdmin ? 'items-start' : 'items-end flex-row-reverse';
        
        // Create message element with proper timezone handling
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${alignmentClasses} chat-message new-message`;
        messageDiv.setAttribute('data-message-id', message.id);
        
        // Use ISO timestamp for proper timezone conversion
        const isoTimestamp = message.timestamp || message.created_at;
        
        messageDiv.innerHTML = `
            <div class="max-w-[90%] ${msgClasses} px-2 py-0.5 rounded-xl shadow-sm">
                <div class="flex flex-col">
                    <div class="text-sm break-words whitespace-pre-wrap message-content leading-tight text-center">
                        ${escapeHtml(message.content)}
                    </div>
                    <div class="text-[8px] ${isAdmin ? 'text-gray-400' : 'text-blue-100'} self-end opacity-70">
                        <span class="local-time" data-datetime="${isoTimestamp}">Loading...</span>
                    </div>
                </div>
            </div>
        `;
        
        // Clear placeholder if needed
        const emptyChat = chatMessages.querySelector('.empty-chat');
        if (emptyChat) {
            emptyChat.remove();
        }
        
        // Append to chat
        chatMessages.appendChild(messageDiv);
        
        // Process any payment buttons in the new message
        processPaymentElements();
        
        // Convert timestamp using TimezoneHelper if available
        if (window.TimezoneHelper) {
            setTimeout(() => {
                window.TimezoneHelper.convertPageTimestamps();
            }, 10);
        } else {
            // Fallback timestamp formatting
            try {
                const date = new Date(isoTimestamp);
                const timeElement = messageDiv.querySelector('.local-time');
                if (timeElement && !isNaN(date.getTime())) {
                    timeElement.textContent = formatTime(date);
                }
            } catch (error) {
                console.warn('Error formatting message timestamp:', error);
            }
        }
        
        // Play notification sound for admin messages
        if (isAdmin) {
            playNotificationSound();
        }
        
        return true;
    }

    /**
     * Show the loading indicator
     */
    function showLoadingIndicator() {
        const loadingIndicator = document.getElementById('chat-loading');
        if (loadingIndicator) {
            // Add visible class
            loadingIndicator.classList.add('visible');
            
            // Auto-hide after 3 seconds in case the hide call is missed
            setTimeout(() => {
                loadingIndicator.classList.remove('visible');
            }, 3000);
        }
    }

    /**
     * Hide the loading indicator
     */
    function hideLoadingIndicator() {
        const loadingIndicator = document.getElementById('chat-loading');
        if (loadingIndicator) {
            // Add a small delay to avoid flickering
            setTimeout(() => {
                loadingIndicator.classList.remove('visible');
            }, 500);
        }
    }

    /**
     * Show a visual notification that new messages have been received
     * @param {number} count - Number of new messages
     */
    function showNewMessageNotification(count) {
        // Check if notification already exists
        let notification = document.getElementById('new-message-notification');
        
        // If not, create it
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'new-message-notification';
            notification.className = 'new-message-notification';
            document.body.appendChild(notification);
        }
        
        // Set the notification text
        notification.textContent = `${count} new message${count > 1 ? 's' : ''}`;
        
        // Show the notification
        notification.classList.add('visible');
        
        // Hide the notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('visible');
        }, 3000);
    }
});
