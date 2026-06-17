@php
    $modalTitle = $modalTitle ?? 'Leave Details';
    $showUserInModal = $showUserInModal ?? true;
    $today = $today ?? now()->toDateString();
@endphp

<div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveModalLabel">{{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body calendar-modal-body" id="leaveDetails">
                <p class="text-muted mb-0">Select a date to view leave details.</p>
            </div>
        </div>
    </div>
</div>

@once
<script>
    window.calendarLeaveEvents = window.calendarLeaveEvents || @json($events ?? []);

    window.showCalendarDetails = function (date) {
        const events = window.calendarLeaveEvents || {};
        const modalLabel = document.getElementById('leaveModalLabel');
        const details = document.getElementById('leaveDetails');
        const showUser = @json($showUserInModal);

        if (!details) {
            return;
        }

        const formatted = new Date(date + 'T12:00:00').toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });

        if (modalLabel) {
            modalLabel.textContent = showUser
                ? 'People on Leave — ' + formatted
                : 'My Leave — ' + formatted;
        }

        const todayDate = @json($today);
        const dayEvents = events[date];

        if (!dayEvents || !dayEvents.length) {
            details.innerHTML = date === todayDate
                ? '<p class="mb-0">No leave today.</p>'
                : '<p class="mb-0">No approved leave on this day.</p>';
        } else if (showUser) {
            const grouped = {};
            dayEvents.forEach(function (entry) {
                const type = entry.type || 'Leave';
                if (!grouped[type]) {
                    grouped[type] = [];
                }
                grouped[type].push(entry.user || 'Unknown');
            });

            const typeCount = Object.keys(grouped).length;
            let html = '<p class="calendar-modal-summary"><strong>' + dayEvents.length + '</strong> '
                + (dayEvents.length === 1 ? 'person' : 'people')
                + ' on leave · <strong>' + typeCount + '</strong> '
                + (typeCount === 1 ? 'type' : 'types') + '</p>';
            html += '<div class="calendar-modal-groups">';
            Object.keys(grouped).sort().forEach(function (type) {
                html += '<div class="calendar-modal-group">';
                html += '<h6 class="calendar-modal-type">' + escapeHtml(type) + ' Leave</h6>';
                html += '<ul class="leave-details-list mb-0">';
                grouped[type].forEach(function (name) {
                    html += '<li>' + escapeHtml(name) + '</li>';
                });
                html += '</ul></div>';
            });
            html += '</div>';
            details.innerHTML = html;
        } else {
            let html = '<ul class="leave-details-list mb-0">';
            dayEvents.forEach(function (entry) {
                html += '<li>' + escapeHtml(entry.type || 'Leave') + ' Leave</li>';
            });
            html += '</ul>';
            details.innerHTML = html;
        }

        const modalEl = document.getElementById('leaveModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endonce
