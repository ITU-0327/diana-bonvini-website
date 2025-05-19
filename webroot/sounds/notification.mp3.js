document.addEventListener('DOMContentLoaded', function () {
    // Scroll to bottom of chat on page load
    const chatContainer = document.querySelector('.chat-container');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Focus on message input when clicking reply button
    document.getElementById('messageText').focus();

    // Animate button on form submit
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function () {
            const button = document.getElementById('sendButton');
            button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';
            button.disabled = true;
        });
    }

    // Handle URL hash for navigating to specific sections
    if (window.location.hash) {
        const targetElement = document.querySelector(window.location.hash);
        if (targetElement) {
            setTimeout(() => {
                window.scrollTo({
                    top: targetElement.offsetTop - 70,
                    behavior: 'smooth'
                });
            }, 100);
        }
    }

    // Poll for new messages
    const requestId = "<?= h($writingServiceRequest->writing_service_request_id) ?>";
    let lastMessageId = null;

    // Get the ID of the last message in the chat
    const allMessages = document.querySelectorAll('.chat-messages .chat-message');
    if (allMessages && allMessages.length > 0) {
        const lastMessage = allMessages[allMessages.length - 1];
        lastMessageId = lastMessage.dataset.messageId || null;
    }

    // Function to add a new message to the chat
    function addMessageToChat(message)
    {
        const isAdmin = message.sender === 'admin';
        const messageHtml = `
            < div class = "chat-message mb-3 ${isAdmin ? 'admin-message' : 'client-message'}" data - message - id = "${message.id}" >
                < div class = "message-header d-flex align-items-center mb-1" >
                    < div class = "message-avatar mr-2" >
                        ${isAdmin
            ? '<div class="avatar bg-primary text-white">A</div>'
            : ` < div class = "avatar bg-success text-white" > ${message.senderName.substr(0, 1)} < / div > `
                    }
                    <  / div >
                    < div class = "message-info" >
                        < span class = "message-sender font-weight-bold" >
                            ${isAdmin ? 'You (Admin)' : message.senderName}
                        <  / span >
                        < span class = "message-time text-muted ml-2" >
                            < i class = "far fa-clock" > < / i > ${message.timestamp}
                        <  / span >
                        ${!isAdmin && !message.is_read ? '<span class="badge badge-warning ml-2">New</span>' : ''}
                    <  / div >
                    <  / div >
                    < div class = "message-content" >
                    < div class = "message-bubble p-3 rounded" >
                        ${message.content}
                    <  / div >
                    <  / div >
                    <  / div >
                    `;

        // If there are no messages, clear the "no messages" placeholder
                    if (allMessages.length === 0) {
                        document.querySelector('.chat-messages').innerHTML = '';
                    }

        // Add the new message to the chat
                    document.querySelector('.chat-messages').insertAdjacentHTML('beforeend', messageHtml);

        // Update the message count badge
                    const countBadge = document.querySelector('.card-header .badge');
                    if (countBadge) {
                        const currentCount = parseInt(countBadge.textContent) || 0;
                        countBadge.textContent = (currentCount + 1) + " Messages";
                    }

        // Update the last message ID
                    lastMessageId = message.id;

        // Scroll to bottom
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }

        // Play notification sound for client messages
                    if (!isAdmin) {
                        playNotificationSound();
                    }
    }

    // Create audio element for notification sound
    const notificationSound = new Audio('/sounds/notification.mp3');
    function playNotificationSound()
    {
        notificationSound.play().catch(e => {
            console.log('Audio playback failed:', e);
        });
    }

    // Function to fetch new messages
    function fetchNewMessages()
    {
        const url = ` / writing - service - requests / fetch - messages / ${requestId}${lastMessageId ? '/' + lastMessageId : ''}`;

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

    // Poll for new messages every 5 seconds
    const pollingInterval = setInterval(fetchNewMessages, 5000);

    // Clear interval when page is unloaded
    window.addEventListener('beforeunload', function () {
        clearInterval(pollingInterval);
    });
});

// Template insertion function for quick replies
function insertTemplate(text)
{
    const textarea = document.getElementById('messageText');
    textarea.value = text;
    textarea.focus();
}
