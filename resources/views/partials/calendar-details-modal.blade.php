@php
    $showUserInModal = $showUserInModal ?? true;
    $today = $today ?? now()->toDateString();
@endphp

@include('partials.request-details-modal-styles')

@once
<style>
    .calendar-details-container.swal2-container {
        z-index: 10150 !important;
        padding: 16px 12px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 0 !important;
    }
    .calendar-details-container .swal2-popup.request-details-popup {
        margin: 0 auto !important;
    }
</style>
<script>
    window.calendarLeaveEvents = window.calendarLeaveEvents || @json($events ?? []);

    function escapeCalendarHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function calendarSectionBlock(title, innerHtml, boxClass) {
        const boxCls = boxClass ? ' rd-box--' + boxClass : '';
        return '<div class="rd-section">'
            + '<h4 class="rd-section-title">' + escapeCalendarHtml(title) + '</h4>'
            + '<div class="rd-box' + boxCls + '">' + innerHtml + '</div>'
            + '</div>';
    }

    window.showCalendarDetails = function (date) {
        if (!window.Swal) {
            return;
        }

        const events = window.calendarLeaveEvents || {};
        const showUser = @json($showUserInModal);
        const todayDate = @json($today);

        const formatted = new Date(date + 'T12:00:00').toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });

        const dayEvents = events[date] || [];
        const hasLeave = dayEvents.length > 0;
        const headerLabel = showUser ? 'People on Leave' : 'My Leave';
        let badgeHtml = '';

        if (hasLeave) {
            const badgeText = showUser
                ? (dayEvents.length === 1 ? '1 on leave' : dayEvents.length + ' on leave')
                : 'On leave';
            badgeHtml = '<span class="rd-badge rd-badge--approved">' + escapeCalendarHtml(badgeText) + '</span>';
        }

        let bodyHtml = '';

        if (!hasLeave) {
            const emptyText = date === todayDate
                ? 'No leave today.'
                : 'No approved leave on this day.';
            bodyHtml = '<div class="rd-box rd-box--muted">' + escapeCalendarHtml(emptyText) + '</div>';
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
            bodyHtml = '<div class="rd-stats" style="margin-bottom:14px;">'
                + '<div class="rd-stat"><span class="rd-stat-label">People</span>'
                + '<span class="rd-stat-value">' + dayEvents.length + '</span></div>'
                + '<div class="rd-stat"><span class="rd-stat-label">Leave types</span>'
                + '<span class="rd-stat-value rd-stat-value--type">' + typeCount + '</span></div>'
                + '</div>';

            Object.keys(grouped).sort().forEach(function (type) {
                const list = '<ul class="leave-details-list" style="margin:0;padding-left:1.25rem;">'
                    + grouped[type].map(function (name) {
                        return '<li>' + escapeCalendarHtml(name) + '</li>';
                    }).join('')
                    + '</ul>';
                bodyHtml += calendarSectionBlock(type + ' leave', list);
            });
        } else {
            const list = '<ul class="leave-details-list" style="margin:0;padding-left:1.25rem;">'
                + dayEvents.map(function (entry) {
                    return '<li>' + escapeCalendarHtml(entry.type || 'Leave') + ' leave</li>';
                }).join('')
                + '</ul>';
            bodyHtml = calendarSectionBlock('Approved leave', list);
        }

        const html = '<div class="rd-modal">'
            + '<div class="rd-header">'
            + '<div class="rd-header-id">' + escapeCalendarHtml(headerLabel) + ' <span>' + escapeCalendarHtml(formatted) + '</span></div>'
            + badgeHtml
            + '</div>'
            + '<div class="rd-body">' + bodyHtml + '</div>'
            + '</div>';

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        Swal.fire({
            width: isMobile ? 'auto' : 640,
            position: 'center',
            showCloseButton: false,
            showIcon: false,
            customClass: {
                popup: 'request-details-popup',
                container: 'calendar-details-container',
            },
            html: html,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            focusConfirm: false,
            didOpen: function (popup) {
                const container = popup?.closest('.swal2-container');
                if (container) {
                    container.style.zIndex = '10150';
                    container.scrollTop = 0;
                }
                const htmlContainer = Swal.getHtmlContainer();
                if (htmlContainer) {
                    htmlContainer.scrollTop = 0;
                }
            },
        });
    };
</script>
@endonce
