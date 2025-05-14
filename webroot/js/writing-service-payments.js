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
            showPaymentSuccessToast();
            
            // Remove the parameter from URL to prevent showing toast on page refresh
            if (window.history && window.history.replaceState) {
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }

            // Find the specific payment container for this payment ID
            const matchingContainer = findPaymentContainer(specificPaymentId);
            if (matchingContainer) {
                console.log(`Found matching payment container for ID: ${specificPaymentId}`);
                checkPaymentStatus(matchingContainer);
            } else {
                console.log(`No matching payment container found for ID: ${specificPaymentId}`);
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
        const allMessages = document.querySelectorAll('#chat-messages .flex');
        if (allMessages && allMessages.length > 0) {
            const lastMessage = allMessages[allMessages.length - 1];
            lastMessageId = lastMessage.dataset.messageId || null;
        }

        // Function to add a new message to the chat
        function addMessageToChat(message) {
            const isAdmin = message.sender === 'admin';
            // Match the extremely tight styling from the template
            const msgClasses = isAdmin
                ? 'bg-gray-200 border-0 ml-0 lg:ml-1'
                : 'bg-blue-600 text-white border-0 mr-0 lg:mr-1';
            const textColor = isAdmin ? 'text-gray-800' : 'text-white';
            const timeColor = isAdmin ? 'text-gray-400' : 'text-blue-100';
            const alignmentClasses = isAdmin ? 'items-start' : 'items-end flex-row-reverse';

            // Format timestamp to match template style
            function formatTime(date) {
                return date.toLocaleTimeString(undefined, {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }).toLowerCase();
            }

            const messageTime = formatTime(new Date(message.created_at));

            const newMessageHtml = `
                <div class="flex ${alignmentClasses}" data-message-id="${message.id}">
                    <div class="max-w-[90%] ${msgClasses} px-2 py-0.5 rounded-xl shadow-sm">
                        <div class="flex flex-col">
                            <div class="${textColor} text-sm break-words whitespace-pre-wrap message-content leading-tight text-center">
                                ${message.content}
                            </div>
                            <div class="text-[8px] ${timeColor} self-end opacity-70">
                                <span class="local-time" data-datetime="${message.created_at}">${messageTime}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // If there are no messages, clear the "no messages" placeholder
            if (allMessages.length === 0) {
                chatMessages.innerHTML = '';
            }

            // Add the new message to the chat
            chatMessages.insertAdjacentHTML('beforeend', newMessageHtml);

            // Update the last message ID
            lastMessageId = message.id;

            // Process any payment buttons in the new message
            processPaymentElements();

            // Scroll to the bottom
            scrollChatToBottom();

            // Play notification sound for admin messages
            if (isAdmin) {
                playNotificationSound();
            }
        }

        // Function to scroll chat to bottom
        function scrollChatToBottom() {
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Function to fetch new messages
        function fetchNewMessages() {
            const baseUrl = getBaseUrl();
            const url = `${baseUrl}/writing-service-requests/fetch-messages/${requestId}${lastMessageId ? '/' + lastMessageId : ''}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            addMessageToChat(message);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
        }

        // Poll for new messages every 3 seconds
        const pollingInterval = setInterval(fetchNewMessages, 3000);

        // Clear interval when page is unloaded
        window.addEventListener('beforeunload', function() {
            clearInterval(pollingInterval);
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
});
