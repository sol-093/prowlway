<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Calendar - PROWLWAY';
$bodyClass = 'calendar-page';

// Calendar month view
$calYear = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$calMonth = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
if ($calMonth < 1) { $calMonth = 1; } elseif ($calMonth > 12) { $calMonth = 12; }
$calFirst = new DateTime("$calYear-$calMonth-01");
$calLast = clone $calFirst;
$calLast->modify('last day of this month');
$firstDayStr = $calFirst->format('Y-m-d');
$lastDayStr = $calLast->format('Y-m-d');

$events = dbFetchAll(
    "SELECT * FROM events WHERE status = 'published' AND date <= ? AND (end_date IS NULL OR end_date >= ?) ORDER BY date ASC, display_order ASC",
    [$lastDayStr, $firstDayStr]
);
$eventsByDay = [];
foreach ($events as $ev) {
    $start = new DateTime($ev['date']);
    $end = !empty($ev['end_date']) ? new DateTime($ev['end_date']) : clone $start;
    $d = clone $start;
    while ($d <= $end) {
        $key = $d->format('Y-m-d');
        if (!isset($eventsByDay[$key])) $eventsByDay[$key] = [];
        $eventsByDay[$key][] = $ev;
        $d->modify('+1 day');
    }
}

$todayStr = date('Y-m-d');
$cutoffStr = date('Y-m-d', strtotime('+2 months')); // Reminders: only next 1–2 months

// Upcoming events: within next 2 months only (starts by cutoff, and starts today+ or ongoing)
$upcomingEvents = dbFetchAll(
    "SELECT * FROM events WHERE status = 'published' AND date <= ? AND (date >= ? OR (end_date IS NOT NULL AND end_date >= ?)) ORDER BY date ASC, display_order ASC LIMIT 15",
    [$cutoffStr, $todayStr, $todayStr]
);

// Upcoming calendar entries (holidays / school): within next 2 months only
$upcomingHolidays = [];
try {
    $upcomingHolidays = dbFetchAll(
        "SELECT id, date, end_date, name, type, type_label, region FROM holidays WHERE date <= ? AND (date >= ? OR (end_date IS NOT NULL AND end_date >= ?)) ORDER BY date ASC LIMIT 10",
        [$cutoffStr, $todayStr, $todayStr]
    );
} catch (Throwable $e) {}

// Recent announcements for Reminders (connect events + announcements in one place)
$recentAnnouncements = dbFetchAll(
    "SELECT id, title, description, category, created_at FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 5"
);

$prevMonth = clone $calFirst; $prevMonth->modify('-1 month');
$nextMonth = clone $calFirst; $nextMonth->modify('+1 month');

$holidaysByDay = [];
try {
    $holidays = dbFetchAll(
        "SELECT id, date, end_date, name, description, type, type_label, region FROM holidays WHERE date <= ? AND (end_date IS NULL OR end_date >= ?) ORDER BY date ASC",
        [$lastDayStr, $firstDayStr]
    );
    foreach ($holidays as $h) {
        $start = new DateTime($h['date']);
        $end = !empty($h['end_date']) ? new DateTime($h['end_date']) : clone $start;
        $d = clone $start;
        while ($d <= $end) {
            $key = $d->format('Y-m-d');
            if (!isset($holidaysByDay[$key])) $holidaysByDay[$key] = [];
            $holidaysByDay[$key][] = $h;
            $d->modify('+1 day');
        }
    }
} catch (Throwable $e) {}

$scheduleTypeLabels = [
    'event' => 'Event',
    'enrollment' => 'Enrollment',
    'school_break' => 'School Break',
    'school_end' => 'School End',
    'start_of_classes' => 'Start of Classes',
    'exam_period' => 'Exam Period',
];

$calendarDayEventsJson = [];
foreach ($eventsByDay as $date => $evs) {
    $calendarDayEventsJson[$date] = ['events' => array_map(function ($ev) use ($scheduleTypeLabels) {
        $st = isset($ev['schedule_type']) ? $ev['schedule_type'] : 'event';
        return [
            'id' => (int) $ev['id'],
            'title' => $ev['title'],
            'description' => $ev['description'] ?? '',
            'summary' => $ev['summary'] ?? '',
            'image_url' => !empty($ev['image']) ? getImageUrl($ev['image']) : '',
            'date' => $ev['date'],
            'end_date' => $ev['end_date'] ?? null,
            'location' => $ev['location'] ?? '',
            'schedule_type' => $st,
            'schedule_type_label' => isset($scheduleTypeLabels[$st]) ? $scheduleTypeLabels[$st] : 'Event',
            'detail_url' => PUBLIC_URL . '/event-detail.php?id=' . (int) $ev['id'],
        ];
    }, $evs), 'holidays' => []];
}
$holidayTypeLabels = [
    'regular' => 'Holiday', 'special_non_working' => 'Special Non-Working', 'special_working' => 'Special Working', 'dasma' => 'Dasma',
    'enrollment' => 'Enrollment', 'start_of_school' => 'Start of School', 'wellness_break' => 'Wellness Break', 'christmas_break' => 'Christmas Break', 'year_end' => 'Year End', 'school_end' => 'School End', 'school' => 'School',
];
foreach ($holidaysByDay as $date => $holidays) {
    $holidayList = array_map(function ($h) use ($holidayTypeLabels) {
        $displayLabel = !empty($h['type_label']) ? $h['type_label'] : (isset($holidayTypeLabels[$h['type'] ?? '']) ? $holidayTypeLabels[$h['type']] : 'Holiday');
        return [
            'name' => $h['name'],
            'description' => isset($h['description']) ? $h['description'] : '',
            'type' => isset($h['type']) ? $h['type'] : 'regular',
            'region' => isset($h['region']) ? $h['region'] : 'PH',
            'date' => $h['date'],
            'end_date' => isset($h['end_date']) ? $h['end_date'] : null,
            'type_label' => $displayLabel,
        ];
    }, $holidays);
    if (!isset($calendarDayEventsJson[$date])) {
        $calendarDayEventsJson[$date] = ['events' => [], 'holidays' => $holidayList];
    } else {
        $calendarDayEventsJson[$date]['holidays'] = $holidayList;
    }
}

include '../includes/header.php';
?>

<div class="calendar-page-container">
    <section class="institute-section institute-calendar-section">
        <div class="institute-calendar-header">
            <h1 class="section-title">Calendar</h1>
            <div class="calendar-controls">
                <div class="calendar-year-month-picker">
                    <span class="calendar-year-month-label">Choose month &amp; year</span>
                    <form method="get" action="<?php echo PUBLIC_URL; ?>/calendar.php" class="calendar-picker-form" id="cal-picker-form">
                        <label for="cal-year" class="sr-only">Year</label>
                        <select name="year" id="cal-year" class="calendar-select" onchange="this.form.submit()" aria-label="Year">
                            <?php for ($y = $calYear - 10; $y <= $calYear + 2; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $calYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                        <label for="cal-month" class="sr-only">Month</label>
                        <select name="month" id="cal-month" class="calendar-select" onchange="this.form.submit()" aria-label="Month">
                            <?php
                            $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                            for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m == $calMonth ? 'selected' : ''; ?>><?php echo $monthNames[$m - 1]; ?></option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="calendar-go-btn">Go</button>
                    </form>
                </div>
                <nav class="calendar-month-nav" aria-label="Calendar month navigation">
                    <a href="<?php echo PUBLIC_URL; ?>/calendar.php?month=<?php echo $prevMonth->format('n'); ?>&year=<?php echo $prevMonth->format('Y'); ?>" class="calendar-nav-btn" aria-label="Previous month">‹</a>
                    <span class="calendar-month-title-large"><?php echo $calFirst->format('F'); ?> <span class="calendar-year-num"><?php echo $calYear; ?></span></span>
                    <a href="<?php echo PUBLIC_URL; ?>/calendar.php?month=<?php echo $nextMonth->format('n'); ?>&year=<?php echo $nextMonth->format('Y'); ?>" class="calendar-nav-btn" aria-label="Next month">›</a>
                </nav>
            </div>
        </div>

        <div class="institute-calendar-layout">
            <div class="calendar-grid-wrap">
                <div class="calendar-weekdays">
                    <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                </div>
                <div class="calendar-days-grid">
                    <?php
                    $startWeekday = (int) $calFirst->format('N') - 1;
                    $daysInMonth = (int) $calLast->format('j');
                    for ($i = 0; $i < $startWeekday; $i++) {
                        echo '<div class="calendar-day calendar-day-empty"></div>';
                    }
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $dateStr = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $day);
                        $isToday = ($dateStr === $todayStr);
                        $dayEvents = isset($eventsByDay[$dateStr]) ? $eventsByDay[$dateStr] : [];
                        $dayHolidays = isset($holidaysByDay[$dateStr]) ? $holidaysByDay[$dateStr] : [];
                        $classes = 'calendar-day';
                        if ($isToday) $classes .= ' calendar-day-today';
                        if (!empty($dayEvents)) $classes .= ' calendar-day-has-events';
                        if (!empty($dayHolidays)) $classes .= ' calendar-day-has-holidays';
                    ?>
                        <div class="<?php echo $classes; ?>" data-date="<?php echo $dateStr; ?>">
                            <span class="calendar-day-num"><?php echo $day; ?></span>
                            <div class="calendar-day-events">
                                <?php foreach (array_slice($dayHolidays, 0, 2) as $hol):
                                    $holLabelFull = !empty($hol['type_label']) ? $hol['type_label'] : (isset($holidayTypeLabels[$hol['type'] ?? '']) ? $holidayTypeLabels[$hol['type']] : ($hol['region'] === 'Dasma' ? 'Dasma' : 'Holiday'));
                                    $holLabelShort = mb_strlen($holLabelFull) > 12 ? mb_substr($holLabelFull, 0, 11) . '…' : $holLabelFull;
                                ?>
                                    <span class="calendar-day-holiday-dot calendar-holiday-type-<?php echo htmlspecialchars($hol['type'] ?? 'regular'); ?>" title="<?php echo htmlspecialchars($hol['name']); ?> — <?php echo htmlspecialchars($holLabelFull); ?>"><?php echo htmlspecialchars($holLabelShort); ?></span>
                                <?php endforeach; ?>
                                <?php foreach (array_slice($dayEvents, 0, 3) as $ev):
                                    $st = isset($ev['schedule_type']) ? $ev['schedule_type'] : 'event';
                                    $stLabelFull = isset($scheduleTypeLabels[$st]) ? $scheduleTypeLabels[$st] : $ev['title'];
                                    $stLabelShort = mb_strlen($stLabelFull) > 14 ? mb_substr($stLabelFull, 0, 13) . '…' : $stLabelFull;
                                ?>
                                    <a href="<?php echo PUBLIC_URL; ?>/event-detail.php?id=<?php echo $ev['id']; ?>" class="calendar-day-event-dot schedule-type-<?php echo htmlspecialchars($st); ?>" title="<?php echo htmlspecialchars($ev['title']); ?>">
                                        <span class="calendar-event-text"><?php echo htmlspecialchars($stLabelShort); ?></span>
                                    </a>
                                <?php endforeach; ?>
                                <?php if (count($dayEvents) > 3): ?>
                                    <span class="calendar-day-more">+<?php echo count($dayEvents) - 3; ?> more</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if (!empty($upcomingEvents) || !empty($upcomingHolidays) || !empty($recentAnnouncements)): ?>
            <aside class="calendar-reminders">
                <h3 class="calendar-reminders-title">Reminders &amp; Upcoming</h3>
                <div class="calendar-reminders-list">
                    <?php if (!empty($upcomingEvents)): ?>
                    <div class="calendar-reminders-block">
                        <h4 class="calendar-reminders-subtitle">Upcoming events</h4>
                        <?php foreach ($upcomingEvents as $ev):
                            $evStart = new DateTime($ev['date']);
                            $evEnd = !empty($ev['end_date']) ? new DateTime($ev['end_date']) : null;
                            $evDateDisplay = $evEnd && $evEnd != $evStart
                                ? $evStart->format('M j') . ' – ' . $evEnd->format('j, Y')
                                : $evStart->format('M j, Y');
                            $isTodayEv = ($ev['date'] === $todayStr) || ($evEnd && $ev['date'] <= $todayStr && $evEnd->format('Y-m-d') >= $todayStr);
                            $st = isset($ev['schedule_type']) ? $ev['schedule_type'] : 'event';
                            $stLabel = isset($scheduleTypeLabels[$st]) ? $scheduleTypeLabels[$st] : 'Event';
                        ?>
                            <a href="<?php echo PUBLIC_URL; ?>/event-detail.php?id=<?php echo $ev['id']; ?>" class="calendar-reminder-item reminder-schedule-<?php echo htmlspecialchars($st); ?> <?php echo $isTodayEv ? 'reminder-today' : ''; ?>">
                                <span class="reminder-date"><?php echo $evDateDisplay; ?></span>
                                <span class="reminder-schedule-badge"><?php echo htmlspecialchars($stLabel); ?></span>
                                <span class="reminder-title"><?php echo htmlspecialchars($ev['title']); ?></span>
                                <?php if (!empty($ev['location'])): ?>
                                    <span class="reminder-location"><?php echo htmlspecialchars($ev['location']); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($upcomingHolidays)): ?>
                    <div class="calendar-reminders-block">
                        <h4 class="calendar-reminders-subtitle">Upcoming (calendar)</h4>
                        <?php foreach ($upcomingHolidays as $hol):
                            $holStart = new DateTime($hol['date']);
                            $holEnd = !empty($hol['end_date']) ? new DateTime($hol['end_date']) : null;
                            $holDateDisplay = $holEnd && $holEnd != $holStart
                                ? $holStart->format('M j') . ' – ' . $holEnd->format('j, Y')
                                : $holStart->format('M j, Y');
                            $holLabel = !empty($hol['type_label']) ? $hol['type_label'] : (isset($holidayTypeLabels[$hol['type'] ?? '']) ? $holidayTypeLabels[$hol['type']] : 'Calendar');
                        ?>
                            <div class="calendar-reminder-item calendar-reminder-holiday" title="<?php echo htmlspecialchars($hol['name']); ?>">
                                <span class="reminder-date"><?php echo $holDateDisplay; ?></span>
                                <span class="reminder-schedule-badge"><?php echo htmlspecialchars($holLabel); ?></span>
                                <span class="reminder-title"><?php echo htmlspecialchars($hol['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($recentAnnouncements)): ?>
                    <div class="calendar-reminders-block">
                        <h4 class="calendar-reminders-subtitle">Recent announcements</h4>
                        <?php foreach ($recentAnnouncements as $ann):
                            $annDate = !empty($ann['created_at']) ? date('M j, Y', strtotime($ann['created_at'])) : '';
                        ?>
                            <a href="<?php echo PUBLIC_URL; ?>/home.php#announcements" class="calendar-reminder-item calendar-reminder-announcement">
                                <span class="reminder-date"><?php echo $annDate; ?></span>
                                <span class="reminder-schedule-badge"><?php echo htmlspecialchars($ann['category'] ?? 'general'); ?></span>
                                <span class="reminder-title"><?php echo htmlspecialchars($ann['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>
            <?php endif; ?>
        </div>

        <div id="calendar-day-modal" class="calendar-day-modal" aria-hidden="true">
            <div class="calendar-day-modal-backdrop"></div>
            <div class="calendar-day-modal-content">
                <button type="button" class="calendar-day-modal-close" aria-label="Close">&times;</button>
                <h3 id="calendar-day-modal-title" class="calendar-day-modal-title"></h3>
                <div id="calendar-day-modal-events" class="calendar-day-modal-events"></div>
            </div>
        </div>
        <script>
        (function() {
            window.CALENDAR_DAY_EVENTS = <?php echo json_encode($calendarDayEventsJson); ?>;
            var modal = document.getElementById('calendar-day-modal');
            var modalTitle = document.getElementById('calendar-day-modal-title');
            var modalEvents = document.getElementById('calendar-day-modal-events');
            function formatDayTitle(dateStr) {
                var d = new Date(dateStr + 'T12:00:00');
                return d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
            function openDayModal(dateStr) {
                var data = (window.CALENDAR_DAY_EVENTS && window.CALENDAR_DAY_EVENTS[dateStr]) || {};
                var events = Array.isArray(data) ? data : (data.events || []);
                var holidays = Array.isArray(data) ? [] : (data.holidays || []);
                modalTitle.textContent = formatDayTitle(dateStr);
                var parts = [];
                holidays.forEach(function(h) {
                    var typeClass = 'holiday-type-' + (h.type || 'regular');
                    var badge = h.type_label || (h.region === 'Dasma' ? 'Dasma' : 'Holiday');
                    var dateRange = h.date;
                    if (h.end_date && h.end_date !== h.date) dateRange += ' – ' + h.end_date;
                    var desc = (h.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
                    parts.push('<div class="calendar-day-modal-holiday ' + typeClass + '">' +
                        '<span class="calendar-day-modal-holiday-badge">' + (badge || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                        '<span class="calendar-day-modal-holiday-name">' + (h.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                        (dateRange ? '<p class="calendar-day-modal-holiday-daterange">' + dateRange.replace(/</g, '&lt;') + '</p>' : '') +
                        (desc ? '<div class="calendar-day-modal-holiday-desc">' + desc + '</div>' : '') +
                        '</div>');
                });
                if (events.length === 0 && parts.length === 0) {
                    modalEvents.innerHTML = '<p class="calendar-day-modal-empty">No events or holidays on this day.</p>';
                } else {
                    if (parts.length) modalEvents.innerHTML = '<div class="calendar-day-modal-holidays">' + parts.join('') + '</div>';
                    else modalEvents.innerHTML = '';
                    modalEvents.innerHTML += events.map(function(ev) {
                        var dateRange = ev.date;
                        if (ev.end_date && ev.end_date !== ev.date) dateRange += ' – ' + ev.end_date;
                        var img = ev.image_url ? '<img src="' + ev.image_url.replace(/"/g, '&quot;') + '" alt="" class="calendar-day-modal-event-img">' : '';
                        var desc = (ev.description || ev.summary || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
                        return '<article class="calendar-day-modal-event schedule-type-' + (ev.schedule_type || 'event') + '">' +
                            (img ? '<div class="calendar-day-modal-event-media">' + img + '</div>' : '') +
                            '<div class="calendar-day-modal-event-body">' +
                            '<span class="calendar-day-modal-event-badge">' + (ev.schedule_type_label || 'Event') + '</span>' +
                            '<h4 class="calendar-day-modal-event-title">' + (ev.title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h4>' +
                            (dateRange ? '<p class="calendar-day-modal-event-date">' + dateRange.replace(/</g, '&lt;') + '</p>' : '') +
                            (ev.location ? '<p class="calendar-day-modal-event-location">' + (ev.location || '').replace(/</g, '&lt;') + '</p>' : '') +
                            (desc ? '<div class="calendar-day-modal-event-desc">' + desc + '</div>' : '') +
                            '<a href="' + (ev.detail_url || '').replace(/"/g, '&quot;') + '" class="calendar-day-modal-event-link">View full details</a>' +
                            '</div></article>';
                    }).join('');
                }
                modal.classList.add('calendar-day-modal-open');
                modal.setAttribute('aria-hidden', 'false');
            }
            function closeDayModal() {
                modal.classList.remove('calendar-day-modal-open');
                modal.setAttribute('aria-hidden', 'true');
            }
            document.querySelectorAll('.institute-calendar-section .calendar-day.calendar-day-has-events, .institute-calendar-section .calendar-day.calendar-day-has-holidays').forEach(function(cell) {
                cell.style.cursor = 'pointer';
                cell.addEventListener('click', function(e) {
                    if (e.target.closest('a')) return;
                    var dateStr = cell.getAttribute('data-date');
                    if (dateStr) openDayModal(dateStr);
                });
            });
            if (modal) {
                modal.querySelector('.calendar-day-modal-close').addEventListener('click', closeDayModal);
                modal.querySelector('.calendar-day-modal-backdrop').addEventListener('click', closeDayModal);
            }
        })();
        </script>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
