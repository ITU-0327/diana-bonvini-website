/**
 * Timezone Helper Utility
 * Handles conversion of UTC timestamps to user's local timezone
 */

class TimezoneHelper {
    /**
     * Format a UTC timestamp to user's local timezone
     * @param {string} isoString - ISO 8601 timestamp string (UTC)
     * @param {string} format - Format type: 'datetime', 'date', 'time'
     * @returns {string} Formatted timestamp in user's local timezone
     */
    static formatToLocal(isoString, format = 'datetime') {
        if (!isoString) return 'Unknown';
        
        try {
            const date = new Date(isoString);
            
            // Check if date is valid
            if (isNaN(date.getTime())) {
                console.warn('Invalid date string:', isoString);
                return 'Invalid Date';
            }
            
            const options = this.getFormatOptions(format);
            return date.toLocaleString('en-US', options);
        } catch (error) {
            console.error('Error formatting timestamp:', error, isoString);
            return 'Error';
        }
    }
    
    /**
     * Get format options for different display types
     * @param {string} format - Format type
     * @returns {object} Intl.DateTimeFormat options
     */
    static getFormatOptions(format) {
        const baseOptions = {
            timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
        };
        
        switch (format) {
            case 'datetime':
                return {
                    ...baseOptions,
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };
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
            case 'full':
                return {
                    ...baseOptions,
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };
            default:
                return baseOptions;
        }
    }
    
    /**
     * Convert all timestamps on the page to local timezone
     * This function scans for elements with specific data attributes or classes
     */
    static convertPageTimestamps() {
        // Convert elements with data-datetime attribute
        document.querySelectorAll('[data-datetime]').forEach(element => {
            const isoString = element.getAttribute('data-datetime');
            const format = element.getAttribute('data-format') || 'datetime';
            const localTime = this.formatToLocal(isoString, format);
            
            // Update the element content
            if (element.tagName === 'TIME') {
                element.textContent = localTime;
                element.setAttribute('datetime', isoString);
                element.setAttribute('title', `Local time: ${localTime}`);
            } else {
                element.textContent = localTime;
                element.setAttribute('title', `Local time: ${localTime}`);
            }
        });
        
        // Convert elements with .local-time class
        document.querySelectorAll('.local-time').forEach(element => {
            const isoString = element.getAttribute('data-datetime');
            if (isoString) {
                const format = element.getAttribute('data-format') || 'datetime';
                const localTime = this.formatToLocal(isoString, format);
                element.textContent = localTime;
                element.setAttribute('title', `Local time: ${localTime}`);
            }
        });
        
        // Convert message timestamps in chat
        document.querySelectorAll('.message-time[data-datetime]').forEach(element => {
            const isoString = element.getAttribute('data-datetime');
            const localTime = this.formatToLocal(isoString, 'datetime');
            element.textContent = localTime;
            element.setAttribute('title', `Local time: ${localTime}`);
        });
    }
    
    /**
     * Get user's timezone information
     * @returns {object} Timezone information
     */
    static getUserTimezone() {
        try {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const now = new Date();
            const offset = now.getTimezoneOffset();
            const offsetHours = Math.abs(Math.floor(offset / 60));
            const offsetMinutes = Math.abs(offset % 60);
            const offsetString = `${offset <= 0 ? '+' : '-'}${offsetHours.toString().padStart(2, '0')}:${offsetMinutes.toString().padStart(2, '0')}`;
            
            return {
                timeZone,
                offset,
                offsetString,
                abbreviation: now.toLocaleTimeString('en', { timeZoneName: 'short' }).split(' ')[2]
            };
        } catch (error) {
            console.error('Error getting timezone info:', error);
            return {
                timeZone: 'Unknown',
                offset: 0,
                offsetString: '+00:00',
                abbreviation: 'UTC'
            };
        }
    }
    
    /**
     * Initialize timezone conversion when DOM is ready
     */
    static init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.convertPageTimestamps());
        } else {
            this.convertPageTimestamps();
        }
        
        // Also set up a MutationObserver to handle dynamically added content
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver((mutations) => {
                let shouldUpdate = false;
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1) { // Element node
                                if (node.hasAttribute('data-datetime') || 
                                    node.classList.contains('local-time') ||
                                    node.querySelector('[data-datetime], .local-time, .message-time[data-datetime]')) {
                                    shouldUpdate = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldUpdate) {
                    this.convertPageTimestamps();
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }
}

// Auto-initialize when script loads
TimezoneHelper.init();

// Make it globally available
window.TimezoneHelper = TimezoneHelper; 