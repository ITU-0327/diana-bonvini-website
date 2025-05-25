/**
 * Timezone Helper Utility
 * Handles conversion of UTC timestamps to user's local timezone
 * Defaults to Melbourne timezone (Australia/Melbourne) when user timezone is unavailable
 */

class TimezoneHelper {
    /**
     * Get the user's timezone or default to Melbourne
     * @returns {string} Timezone identifier
     */
    static getEffectiveTimezone() {
        try {
            // Try to get user's timezone
            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            
            // Validate the timezone
            if (userTimezone && userTimezone !== 'UTC') {
                return userTimezone;
            }
        } catch (error) {
            console.warn('Could not determine user timezone:', error);
        }
        
        // Default to Melbourne timezone
        return 'Australia/Melbourne';
    }

    /**
     * Format a UTC timestamp to user's local timezone (or Melbourne default)
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
            return date.toLocaleString('en-AU', options); // Use Australian locale for better Melbourne formatting
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
        const timezone = this.getEffectiveTimezone();
        
        const baseOptions = {
            timeZone: timezone
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
            if (!isoString || isoString === 'Loading...' || isoString === 'Unknown') {
                return; // Skip empty or placeholder values
            }
            
            const format = element.getAttribute('data-format') || 'datetime';
            const localTime = this.formatToLocal(isoString, format);
            
            // Update the element content
            if (element.tagName === 'TIME') {
                element.textContent = localTime;
                element.setAttribute('datetime', isoString);
                element.setAttribute('title', `UTC: ${isoString} | Local: ${localTime}`);
            } else {
                element.textContent = localTime;
                element.setAttribute('title', `UTC: ${isoString} | Local: ${localTime}`);
            }
            
            // Add a data attribute to mark as converted
            element.setAttribute('data-timezone-converted', 'true');
        });
        
        // Convert elements with .local-time class
        document.querySelectorAll('.local-time:not([data-timezone-converted])').forEach(element => {
            const isoString = element.getAttribute('data-datetime');
            if (isoString && isoString !== 'Loading...' && isoString !== 'Unknown') {
                const format = element.getAttribute('data-format') || 'datetime';
                const localTime = this.formatToLocal(isoString, format);
                element.textContent = localTime;
                element.setAttribute('title', `UTC: ${isoString} | Local: ${localTime}`);
                element.setAttribute('data-timezone-converted', 'true');
            }
        });
        
        // Convert message timestamps in chat
        document.querySelectorAll('.message-time[data-datetime]:not([data-timezone-converted])').forEach(element => {
            const isoString = element.getAttribute('data-datetime');
            if (isoString && isoString !== 'Loading...' && isoString !== 'Unknown') {
                const localTime = this.formatToLocal(isoString, 'datetime');
                
                // For message times, look for the .local-time span inside
                const localTimeSpan = element.querySelector('.local-time');
                if (localTimeSpan) {
                    localTimeSpan.textContent = localTime;
                } else {
                    element.textContent = localTime;
                }
                
                element.setAttribute('title', `UTC: ${isoString} | Local: ${localTime}`);
                element.setAttribute('data-timezone-converted', 'true');
            }
        });
    }
    
    /**
     * Get user's timezone information
     * @returns {object} Timezone information
     */
    static getUserTimezone() {
        try {
            const effectiveTimeZone = this.getEffectiveTimezone();
            const userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const now = new Date();
            const offset = now.getTimezoneOffset();
            const offsetHours = Math.abs(Math.floor(offset / 60));
            const offsetMinutes = Math.abs(offset % 60);
            const offsetString = `${offset <= 0 ? '+' : '-'}${offsetHours.toString().padStart(2, '0')}:${offsetMinutes.toString().padStart(2, '0')}`;
            
            // Get timezone abbreviation using the effective timezone
            const abbreviation = new Date().toLocaleTimeString('en-AU', { 
                timeZone: effectiveTimeZone, 
                timeZoneName: 'short' 
            }).split(' ').pop() || 'Unknown';
            
            return {
                effectiveTimeZone,
                userTimeZone,
                isUsingDefault: effectiveTimeZone === 'Australia/Melbourne' && userTimeZone !== effectiveTimeZone,
                offset,
                offsetString,
                abbreviation
            };
        } catch (error) {
            console.error('Error getting timezone info:', error);
            return {
                effectiveTimeZone: 'Australia/Melbourne',
                userTimeZone: 'Unknown',
                isUsingDefault: true,
                offset: 0,
                offsetString: '+00:00',
                abbreviation: 'AEDT/AEST'
            };
        }
    }
    
    /**
     * Show timezone info to user (for debugging or user information)
     */
    static showTimezoneInfo() {
        const info = this.getUserTimezone();
        console.log('Timezone Information:', info);
        
        if (info.isUsingDefault) {
            console.log('Using default Melbourne timezone (Australia/Melbourne) because user timezone could not be determined');
        } else {
            console.log(`Using user's timezone: ${info.effectiveTimeZone}`);
        }
    }
    
    /**
     * Initialize timezone conversion when DOM is ready
     */
    static init() {
        // Show timezone info for debugging
        this.showTimezoneInfo();
        
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
                    setTimeout(() => this.convertPageTimestamps(), 100); // Small delay to ensure DOM is ready
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