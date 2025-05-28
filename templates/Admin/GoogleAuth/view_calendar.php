<?php
/**
 * @var \App\View\AppView $this
 * @var bool $isConnected
 * @var bool $useDemoMode
 * @var array $calendarEvents
 * @var int $month
 * @var int $year
 * @var \DateTime $today
 */
$this->Html->css('https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css', ['block' => true]);
$this->Html->script('https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js', ['block' => true]);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-2"></i><?= __('Google Calendar') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Google Calendar'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('View Calendar') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= __('Calendar View') ?>
                        <?php if ($useDemoMode): ?>
                        <span class="badge badge-warning ml-2"><?= __('Demo Mode') ?></span>
                        <?php endif; ?>
                    </h6>
                    <div>
                        <?= $this->Html->link(
                            '<i class="fas fa-sync-alt mr-1"></i>' . __('Refresh'),
                            ['action' => 'viewCalendar'],
                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="fas fa-cog mr-1"></i>' . __('Settings'),
                            ['action' => 'index'],
                            ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false]
                        ) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" role="dialog" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="eventDetails">
                        <h4 id="eventTitle" class="mb-3"></h4>
                        <div class="row mb-2">
                            <div class="col-md-4 font-weight-bold"><?= __('Start Time') ?>:</div>
                            <div class="col-md-8" id="eventStart"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 font-weight-bold"><?= __('End Time') ?>:</div>
                            <div class="col-md-8" id="eventEnd"></div>
                        </div>
                        <div class="row mb-2" id="eventLocationRow">
                            <div class="col-md-4 font-weight-bold"><?= __('Location') ?>:</div>
                            <div class="col-md-8" id="eventLocation"></div>
                        </div>
                        <div class="row mb-2" id="eventMeetLinkRow">
                            <div class="col-md-4 font-weight-bold"><?= __('Meet Link') ?>:</div>
                            <div class="col-md-8" id="eventMeetLink"></div>
                        </div>
                        <div class="row mb-2" id="eventDescriptionRow">
                            <div class="col-md-4 font-weight-bold"><?= __('Description') ?>:</div>
                            <div class="col-md-8" id="eventDescription"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" id="viewInGoogleBtn" class="btn btn-primary" target="_blank">View in Google Calendar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            navLinks: true,
            editable: false,
            dayMaxEvents: true,
            events: <?= json_encode($calendarEvents) ?>,
            eventClick: function(info) {
                // Prevent the default action of following the URL
                info.jsEvent.preventDefault();
                
                // Populate modal with event details
                const event = info.event;
                document.getElementById('eventTitle').textContent = event.title;
                document.getElementById('eventStart').textContent = formatDateTime(event.start);
                document.getElementById('eventEnd').textContent = formatDateTime(event.end);
                
                // Handle extended props
                const locationRow = document.getElementById('eventLocationRow');
                const locationEl = document.getElementById('eventLocation');
                const meetLinkRow = document.getElementById('eventMeetLinkRow');
                const meetLinkEl = document.getElementById('eventMeetLink');
                const descriptionRow = document.getElementById('eventDescriptionRow');
                const descriptionEl = document.getElementById('eventDescription');
                
                // Set location
                if (event.extendedProps.location) {
                    locationRow.classList.remove('d-none');
                    locationEl.textContent = event.extendedProps.location;
                } else {
                    locationRow.classList.add('d-none');
                }
                
                // Set Meet link
                if (event.extendedProps.meetLink) {
                    meetLinkRow.classList.remove('d-none');
                    meetLinkEl.innerHTML = `<a href="${event.extendedProps.meetLink}" target="_blank">${event.extendedProps.meetLink}</a>`;
                } else {
                    meetLinkRow.classList.add('d-none');
                }
                
                // Set description
                if (event.extendedProps.description) {
                    descriptionRow.classList.remove('d-none');
                    descriptionEl.textContent = event.extendedProps.description;
                } else {
                    descriptionRow.classList.add('d-none');
                }
                
                // Set Google Calendar link
                const viewInGoogleBtn = document.getElementById('viewInGoogleBtn');
                if (event.url) {
                    viewInGoogleBtn.classList.remove('d-none');
                    viewInGoogleBtn.href = event.url;
                } else {
                    viewInGoogleBtn.classList.add('d-none');
                }
                
                // Show the modal
                $('#eventDetailsModal').modal('show');
            }
        });
        
        calendar.render();
        
        // Helper function to format date and time
        function formatDateTime(date) {
            if (!date) return '';
            return new Date(date).toLocaleString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }
    });
</script>

<style>
    #calendar {
        height: 800px;
    }
    .fc-event {
        cursor: pointer;
    }
</style>
