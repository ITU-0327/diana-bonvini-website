/**
 * Local Time Converter Utility
 * Converts server timestamps to user's local time
 */

class LocalTimeConverter {
    constructor() {
        this.userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        this.debugMode = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1'; // Enable debug on deployed sites
        this.log('LocalTimeConverter initialized', {
            userTimezone: this.userTimezone,
            hostname: window.location.hostname,
            debugMode: this.debugMode
        });
        this.init();
    }

    /**
     * Debug logging helper
     */
    log(message, data = null) {
        if (this.debugMode) {
            console.log('[LocalTimeConverter]', message, data || '');
        }
    }

    /**
     * Initialize the converter and process all time elements on page load
     */
    init() {
        this.log('Initializing LocalTimeConverter');
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            this.log('DOM still loading, waiting for DOMContentLoaded');
            document.addEventListener('DOMContentLoaded', () => {
                this.log('DOMContentLoaded fired, converting times');
                this.convertAllTimes();
            });
        } else {
            this.log('DOM ready, converting times immediately');
            this.convertAllTimes();
        }
        
        // Also set up observer for dynamically added content
        this.setupMutationObserver();
    }

    /**
     * Convert all timestamps on the page to local time
     */
    convertAllTimes() {
        this.log('Starting convertAllTimes');
        
        // Find all elements with data-server-time attribute
        const timeElements = document.querySelectorAll('[data-server-time]');
        this.log('Found elements with data-server-time:', timeElements.length);
        
        timeElements.forEach((element, index) => {
            const serverTime = element.getAttribute('data-server-time');
            const format = element.getAttribute('data-time-format') || 'datetime';
            const originalText = element.textContent;
            
            this.log(`Processing element ${index + 1}:`, {
                serverTime,
                format,
                originalText,
                element: element.outerHTML.substring(0, 100) + '...'
            });
            
            try {
                const localTime = this.convertToLocalTime(serverTime, format);
                element.textContent = localTime;
                element.classList.add('local-time-converted');
                this.log(`Converted successfully: "${originalText}" -> "${localTime}"`);
            } catch (error) {
                this.log('Failed to convert time:', {
                    serverTime,
                    error: error.message,
                    stack: error.stack
                });
                console.warn('Failed to convert time:', serverTime, error);
            }
        });

        // Also handle elements with specific classes for backwards compatibility
        this.convertElementsByClass('server-timestamp');
        this.convertElementsByClass('message-timestamp');
        this.convertElementsByClass('last-login-time');
        this.convertElementsByClass('payment-date');
        this.convertElementsByClass('created-date');
        
        this.log('Finished convertAllTimes');
    }

    /**
     * Convert timestamps in elements with specific class names
     */
    convertElementsByClass(className) {
        const elements = document.querySelectorAll('.' + className);
        this.log(`Processing class "${className}":`, elements.length + ' elements found');
        
        elements.forEach((element, index) => {
            if (element.classList.contains('local-time-converted')) {
                this.log(`Element ${index + 1} already converted, skipping`);
                return; // Already converted
            }

            const originalText = element.textContent.trim();
            if (!originalText) {
                this.log(`Element ${index + 1} has no text content, skipping`);
                return;
            }

            this.log(`Processing class element ${index + 1}:`, {
                className,
                originalText,
                element: element.outerHTML.substring(0, 100) + '...'
            });

            try {
                // Try to parse various date formats
                const localTime = this.parseAndConvert(originalText);
                if (localTime) {
                    element.textContent = localTime;
                    element.classList.add('local-time-converted');
                    this.log(`Class conversion successful: "${originalText}" -> "${localTime}"`);
                } else {
                    this.log(`Could not parse date: "${originalText}"`);
                }
            } catch (error) {
                this.log('Failed to convert time for class', {
                    className,
                    originalText,
                    error: error.message
                });
                console.warn('Failed to convert time for class', className, ':', originalText, error);
            }
        });
    }

    /**
     * Convert server time to local time
     */
    convertToLocalTime(serverTimeString, format = 'datetime') {
        this.log('convertToLocalTime called:', { serverTimeString, format });
        
        const date = new Date(serverTimeString);
        
        if (isNaN(date.getTime())) {
            this.log('Invalid date created from:', serverTimeString);
            throw new Error('Invalid date string: ' + serverTimeString);
        }

        const options = this.getFormatOptions(format);
        const result = new Intl.DateTimeFormat('en-US', options).format(date);
        
        this.log('Conversion result:', {
            input: serverTimeString,
            parsed: date.toISOString(),
            format,
            options,
            result
        });
        
        return result;
    }

    /**
     * Parse various date formats and convert to local time
     */
    parseAndConvert(dateString) {
        // Common patterns to try
        const patterns = [
            // ISO format: 2023-12-25T10:30:00
            /(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})/,
            // Date with time: Dec 25, 2023 10:30 AM
            /([A-Za-z]{3}\s+\d{1,2},\s+\d{4}\s+\d{1,2}:\d{2}\s+[AP]M)/,
            // Date with at: December 25, 2023 at 10:30 AM
            /([A-Za-z]+\s+\d{1,2},\s+\d{4}\s+at\s+\d{1,2}:\d{2}\s+[AP]M)/,
            // Simple date: 2023-12-25 10:30:00
            /(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/,
            // Slash format: 12/25/2023 10:30 AM
            /(\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2}\s+[AP]M)/
        ];

        for (let pattern of patterns) {
            const match = dateString.match(pattern);
            if (match) {
                try {
                    const date = new Date(match[1]);
                    if (!isNaN(date.getTime())) {
                        return this.convertToLocalTime(date.toISOString(), 'datetime');
                    }
                } catch (e) {
                    continue;
                }
            }
        }

        // Try direct parsing as last resort
        try {
            const date = new Date(dateString);
            if (!isNaN(date.getTime())) {
                return this.convertToLocalTime(date.toISOString(), 'datetime');
            }
        } catch (e) {
            // Failed to parse
        }

        return null;
    }

    /**
     * Get formatting options based on format type
     */
    getFormatOptions(format) {
        const baseOptions = {
            timeZone: this.userTimezone
        };

        switch (format) {
            case 'date':
                return {
                    ...baseOptions,
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                };
            
            case 'time':
                return {
                    ...baseOptions,
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };
            
            case 'datetime':
            default:
                return {
                    ...baseOptions,
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };
            
            case 'full':
                return {
                    ...baseOptions,
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };
            
            case 'relative':
                // For relative time (like "2 hours ago"), we'll use a different approach
                return this.getRelativeTime(new Date(serverTimeString));
        }
    }

    /**
     * Get relative time (e.g., "2 hours ago")
     */
    getRelativeTime(date) {
        const now = new Date();
        const diffMs = now - date;
        const diffSecs = Math.round(diffMs / 1000);
        const diffMins = Math.round(diffSecs / 60);
        const diffHours = Math.round(diffMins / 60);
        const diffDays = Math.round(diffHours / 24);

        if (diffSecs < 60) {
            return 'Just now';
        } else if (diffMins < 60) {
            return `${diffMins} minute${diffMins === 1 ? '' : 's'} ago`;
        } else if (diffHours < 24) {
            return `${diffHours} hour${diffHours === 1 ? '' : 's'} ago`;
        } else if (diffDays < 7) {
            return `${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
        } else {
            // Fall back to formatted date for older items
            return this.convertToLocalTime(date.toISOString(), 'datetime');
        }
    }

    /**
     * Set up mutation observer to handle dynamically added content
     */
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if the added node or its children have timestamps
                        const timeElements = node.querySelectorAll ? 
                            node.querySelectorAll('[data-server-time], .server-timestamp, .message-timestamp, .last-login-time, .payment-date, .created-date') : 
                            [];
                        
                        timeElements.forEach(element => {
                            if (!element.classList.contains('local-time-converted')) {
                                if (element.hasAttribute('data-server-time')) {
                                    const serverTime = element.getAttribute('data-server-time');
                                    const format = element.getAttribute('data-time-format') || 'datetime';
                                    try {
                                        const localTime = this.convertToLocalTime(serverTime, format);
                                        element.textContent = localTime;
                                        element.classList.add('local-time-converted');
                                    } catch (error) {
                                        console.warn('Failed to convert dynamic time:', serverTime, error);
                                    }
                                } else {
                                    // Handle class-based conversion
                                    const originalText = element.textContent.trim();
                                    if (originalText) {
                                        try {
                                            const localTime = this.parseAndConvert(originalText);
                                            if (localTime) {
                                                element.textContent = localTime;
                                                element.classList.add('local-time-converted');
                                            }
                                        } catch (error) {
                                            console.warn('Failed to convert dynamic time:', originalText, error);
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Manual conversion function for specific elements
     */
    convertElement(element, format = 'datetime') {
        if (element.hasAttribute('data-server-time')) {
            const serverTime = element.getAttribute('data-server-time');
            try {
                const localTime = this.convertToLocalTime(serverTime, format);
                element.textContent = localTime;
                element.classList.add('local-time-converted');
                return true;
            } catch (error) {
                console.warn('Failed to convert element time:', serverTime, error);
                return false;
            }
        }
        return false;
    }

    /**
     * Get user's timezone for display
     */
    getUserTimezone() {
        return this.userTimezone;
    }
}

// Initialize the converter when the script loads
const localTimeConverter = new LocalTimeConverter();

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocalTimeConverter;
}

// Global access
window.LocalTimeConverter = LocalTimeConverter;
window.localTimeConverter = localTimeConverter; 