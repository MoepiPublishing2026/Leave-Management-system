@php
    $showUserInModal = $showUserInModal ?? true;
    $today = $today ?? now()->toDateString();
@endphp

@once
<style>
    .calendar-details-container.swal2-container {
        z-index: 10150 !important;
        padding: 16px 12px !important;
        align-items: center !important;
        justify-content: center !important;
    }
    html body .swal2-popup.calendar-day-popup,
    html body .swal2-popup:has(.cal-day-modal) {
        width: 560px !important;
        max-width: calc(100vw - 32px) !important;
        padding: 20px 26px 16px !important;
        border: 0 !important;
        border-radius: 16px !important;
        background: #fff !important;
        box-shadow: 0 16px 44px rgba(15, 23, 42, 0.2) !important;
    }
    html body .swal2-popup.calendar-day-popup .swal2-title,
    html body .swal2-popup.calendar-day-popup .swal2-icon,
    html body .swal2-popup.calendar-day-popup .swal2-actions,
    html body .swal2-popup.calendar-day-popup .swal2-footer,
    html body .calendar-day-actions-hidden {
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    html body .swal2-popup.calendar-day-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        text-align: left !important;
        color: inherit !important;
        font-size: inherit !important;
    }
    .cal-day-group { margin: 0 0 12px; }
    .cal-day-group:last-child { margin-bottom: 0; }
    .cal-day-group-title {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin: 0 0 6px;
    }
    .cal-day-list {
        margin: 0;
        padding-left: 1.2rem;
        color: #334155;
        font-size: 14px;
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

    window.showCalendarDetails = function (date) {
        if (!window.Swal) {
            return;
        }

        const events = window.calendarLeaveEvents || {};
        const showUser = @json($showUserInModal);

        const dayDate = new Date(date + 'T12:00:00');
        const weekday = dayDate.toLocaleDateString('en-US', { weekday: 'long' });
        const rest = dayDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
        const formatted = weekday + ', ' + rest;

        const dayEvents = events[date] || [];
        const hasLeave = dayEvents.length > 0;
        let contentHtml = '';

        if (!hasLeave) {
            contentHtml = '<div style="color:#8b8b8b;font-size:14px;font-style:italic;font-weight:400;">No leave on this day</div>';
        } else if (showUser) {
            const grouped = {};
            dayEvents.forEach(function (entry) {
                const type = entry.type || 'Leave';
                if (!grouped[type]) {
                    grouped[type] = [];
                }
                grouped[type].push(entry.user || 'Unknown');
            });

            Object.keys(grouped).sort().forEach(function (type) {
                contentHtml += '<div class="cal-day-group">'
                    + '<div class="cal-day-group-title">' + escapeCalendarHtml(type) + ' leave</div>'
                    + '<ul class="cal-day-list">'
                    + grouped[type].map(function (name) {
                        return '<li>' + escapeCalendarHtml(name) + '</li>';
                    }).join('')
                    + '</ul></div>';
            });
        } else {
            contentHtml = '<ul class="cal-day-list">'
                + dayEvents.map(function (entry) {
                    return '<li>' + escapeCalendarHtml(entry.type || 'Leave') + ' leave</li>';
                }).join('')
                + '</ul>';
        }

        const html = '<div class="cal-day-modal">'
            + '<div style="color:#3b7ec4;font-size:15px;font-weight:600;text-align:left;line-height:1.35;padding:0 0 14px;margin:0;border-bottom:1px solid #d0d0d0;">'
            + 'Selected Day - ' + escapeCalendarHtml(formatted)
            + '</div>'
            + '<div style="padding:22px 8px;border-bottom:1px solid #d0d0d0;text-align:' + (hasLeave ? 'left' : 'center') + ';">'
            + contentHtml
            + '</div>'
            + '<div style="display:flex;justify-content:center;padding:16px 0 2px;">'
            + '<button type="button" class="cal-day-close" style="background:#f1ec3c;color:#111;border:0 none;outline:none;box-shadow:none;border-radius:999px;padding:8px 26px;font-size:14px;font-weight:700;line-height:1.2;cursor:pointer;min-width:0;height:auto;width:auto;">Close</button>'
            + '</div>'
            + '</div>';

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        Swal.fire({
            width: isMobile ? 'auto' : 560,
            position: 'center',
            showCloseButton: false,
            showConfirmButton: false,
            showDenyButton: false,
            showCancelButton: false,
            buttonsStyling: false,
            customClass: {
                popup: 'calendar-day-popup',
                container: 'calendar-details-container',
                htmlContainer: 'calendar-day-html',
                actions: 'calendar-day-actions-hidden',
            },
            html: html,
            didOpen: function (popup) {
                popup.classList.add('calendar-day-popup');
                const container = popup.closest('.swal2-container');
                if (container) {
                    container.classList.add('calendar-details-container');
                    container.style.zIndex = '10150';
                }
                const actions = popup.querySelector('.swal2-actions');
                if (actions) {
                    actions.style.setProperty('display', 'none', 'important');
                }
                const swalTitle = popup.querySelector('.swal2-title');
                if (swalTitle) {
                    swalTitle.style.setProperty('display', 'none', 'important');
                }
                popup.querySelector('.cal-day-close')?.addEventListener('click', function () {
                    Swal.close();
                });
            },
        });
    };
</script>
@endonce
