<?php
/**
 * @var \App\View\AppView $this
 * @var int $month
 * @var int $year
 * @var \DateTime $today
 * @var array $calendarData
 * @var string|null $requestId
 * @var \App\Model\Entity\WritingServiceRequest|null $writingServiceRequest
 */
?>
<div class="container max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Book a Consultation']) ?>

    <?php if (!empty($writingServiceRequest)): ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Scheduling for Writing Service Request</h3>
                <div class="mt-1 text-sm text-blue-700">
                    <p>You're booking a consultation for request <span class="font-semibold"><?= h($writingServiceRequest->writing_service_request_id) ?></span>: <?= h($writingServiceRequest->service_title) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Calendar Section -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Select an Available Date</h2>
                    <div class="flex items-center space-x-2">
                        <?php
                        $prevMonth = $month == 1 ? 12 : $month - 1;
                        $prevYear = $month == 1 ? $year - 1 : $year;
                        $nextMonth = $month == 12 ? 1 : $month + 1;
                        $nextYear = $month == 12 ? $year + 1 : $year;
                        ?>
                        <?= $this->Html->link(
                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>',
                            ['action' => 'availability', $requestId, '?' => ['month' => $prevMonth, 'year' => $prevYear]],
                            ['class' => 'text-white hover:bg-white hover:bg-opacity-20 rounded-full p-1 transition duration-150', 'escape' => false]
                        ) ?>
                        
                        <span class="text-white font-medium"><?= __(date('F Y', strtotime("$year-$month-01"))) ?></span>
                        
                        <?= $this->Html->link(
                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>',
                            ['action' => 'availability', $requestId, '?' => ['month' => $nextMonth, 'year' => $nextYear]],
                            ['class' => 'text-white hover:bg-white hover:bg-opacity-20 rounded-full p-1 transition duration-150', 'escape' => false]
                        ) ?>
                        
                        <?= $this->Html->link(
                            'Today',
                            ['action' => 'availability', $requestId, '?' => ['month' => $today->format('n'), 'year' => $today->format('Y')]],
                            ['class' => 'ml-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm px-3 py-1 rounded-full transition duration-150']
                        ) ?>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="calendar">
                    <div class="calendar-header">
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                        <div>Sun</div>
                    </div>
                    
                    <div class="calendar-grid">
                        <?php foreach ($calendarData as $day): ?>
                            <?php
                            $dayClasses = ['calendar-day'];
                            
                            if (!$day['is_current_month']) {
                                $dayClasses[] = 'other-month';
                            }
                            
                            if ($day['is_today']) {
                                $dayClasses[] = 'today';
                            }
                            
                            if ($day['is_past']) {
                                $dayClasses[] = 'past-day';
                            } elseif ($day['is_within_booking_window']) {
                                if ($day['has_availability']) {
                                    $dayClasses[] = 'available-day';
                                } else {
                                    $dayClasses[] = 'unavailable-day';
                                }
                            }
                            ?>
                            
                            <div class="<?= implode(' ', $dayClasses) ?>" 
                                <?php if ($day['is_within_booking_window'] && $day['has_availability']): ?>
                                data-date="<?= $day['date']->format('Y-m-d') ?>"
                                <?php endif; ?>>
                                
                                <div class="day-number"><?= h($day['day']) ?></div>
                                
                                <?php if ($day['is_within_booking_window'] && $day['has_availability']): ?>
                                    <div class="availability-indicator">
                                        <span class="availability-dot"></span>
                                        Available
                                    </div>
                                <?php elseif ($day['is_within_booking_window']): ?>
                                    <div class="unavailability-indicator">
                                        Full
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-center space-x-6">
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-600">Available</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-gray-300 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-600">Unavailable</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-blue-400 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-600">Today</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Time Slots Section -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-bold text-gray-800">Available Time Slots</h2>
                <p class="text-sm text-gray-600 mt-1">Select a date on the calendar to view available times</p>
            </div>
            
            <div class="p-6">
                <div id="time-slots-empty" class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500">Please select an available date to view time slots</p>
                </div>
                
                <div id="time-slots-loading" class="text-center py-12 hidden">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p class="text-gray-500">Loading available times...</p>
                </div>
                
                <div id="time-slots-list" class="hidden">
                    <div class="selected-date mb-4 text-center">
                        <h3 class="text-lg font-semibold text-gray-800" id="selected-date-display"></h3>
                    </div>
                    
                    <div class="time-slots grid grid-cols-2 gap-2" id="available-time-slots">
                        <!-- Time slots will be loaded here via JavaScript -->
                    </div>
                </div>
                
                <div id="no-time-slots" class="text-center py-12 hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500">No available time slots for this date</p>
                    <p class="text-sm text-gray-400 mt-2">Please select another date</p>
                </div>
            </div>
            
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Booking Information</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Initial consultations are 30 minutes
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Free of charge for customers
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Conducted via Google Meet
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Cancellations must be made 24 hours in advance
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Calendar Styles */
.calendar {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-weight: 600;
    color: #4b5563;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.75rem;
    margin-bottom: 0.5rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    cursor: default;
    position: relative;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.calendar-day.other-month {
    background-color: #f9fafb;
    color: #9ca3af;
}

.calendar-day.past-day {
    background-color: #f3f4f6;
    color: #9ca3af;
}

.calendar-day.today {
    border: 2px solid #60a5fa;
    background-color: #eff6ff;
}

.calendar-day.available-day {
    background-color: #ecfdf5;
    border-color: #10b981;
    cursor: pointer;
}

.calendar-day.available-day:hover {
    background-color: #d1fae5;
    transform: scale(1.05);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.calendar-day.unavailable-day {
    background-color: #f9fafb;
}

.day-number {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.availability-indicator {
    font-size: 0.7rem;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.availability-dot {
    width: 0.5rem;
    height: 0.5rem;
    background-color: #059669;
    border-radius: 50%;
}

.unavailability-indicator {
    font-size: 0.7rem;
    color: #9ca3af;
}

/* Time Slot Styles */
.time-slot-button {
    padding: 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.time-slot-button:hover {
    background-color: #eff6ff;
    border-color: #60a5fa;
}

.time-slot-button.selected {
    background-color: #3b82f6;
    color: white;
    border-color: #2563eb;
}

@media (max-width: 640px) {
    .calendar-header div, .day-number {
        font-size: 0.8rem;
    }
    
    .availability-indicator, .unavailability-indicator {
        font-size: 0.6rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selected date tracking
    let selectedDate = null;
    let selectedTimeSlot = null;
    
    // Click event for available days
    document.querySelectorAll('.calendar-day.available-day').forEach(day => {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            if (date) {
                selectedDate = date;
                loadTimeSlots(date);
                
                // Update visual selection on calendar
                document.querySelectorAll('.calendar-day').forEach(d => {
                    d.classList.remove('border-blue-600', 'border-2');
                });
                this.classList.add('border-blue-600', 'border-2');
                
                // Update date display
                const formattedDate = new Date(date).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                document.getElementById('selected-date-display').textContent = formattedDate;
            }
        });
    });
    
    // Function to load time slots for a selected date
    function loadTimeSlots(date) {
        // Show loading, hide empty and no slots messages
        document.getElementById('time-slots-empty').classList.add('hidden');
        document.getElementById('time-slots-loading').classList.remove('hidden');
        document.getElementById('time-slots-list').classList.add('hidden');
        document.getElementById('no-time-slots').classList.add('hidden');
        
        // Fetch available time slots
        fetch(`/calendar/get-time-slots?date=${date}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('time-slots-loading').classList.add('hidden');
                
                if (data.success && data.timeSlots && data.timeSlots.length > 0) {
                    // Show time slots container
                    document.getElementById('time-slots-list').classList.remove('hidden');
                    
                    // Populate time slots
                    const timeSlotsContainer = document.getElementById('available-time-slots');
                    timeSlotsContainer.innerHTML = '';
                    
                    data.timeSlots.forEach(slot => {
                        const timeSlotButton = document.createElement('a');
                        timeSlotButton.href = `/calendar/book?date=${date}&time=${slot.start}<?= !empty($requestId) ? '&request_id=' . h($requestId) : '' ?>`;
                        timeSlotButton.className = 'time-slot-button hover:bg-blue-50 border border-gray-200 rounded-md py-2 px-3 text-center text-gray-700 text-sm transition-all duration-150 hover:shadow-sm hover:border-blue-300';
                        timeSlotButton.textContent = slot.formatted;
                        timeSlotsContainer.appendChild(timeSlotButton);
                    });
                } else {
                    // Show no time slots message
                    document.getElementById('no-time-slots').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error fetching time slots:', error);
                document.getElementById('time-slots-loading').classList.add('hidden');
                document.getElementById('no-time-slots').classList.remove('hidden');
                document.getElementById('no-time-slots').innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-red-500">Error loading time slots</p>
                    <p class="text-sm text-gray-400 mt-2">Please try again later</p>
                `;
            });
    }
});
</script>