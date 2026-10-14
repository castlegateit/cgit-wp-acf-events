<?php

declare(strict_types=1);

/**
 * Return sanitized event ID
 *
 * @param int|null $event_id
 * @return int|null
 */
function cgit_wp_events_sanitize_event_id(?int $event_id = null): ?int
{
    if (is_null($event_id)) {
        $event_id = get_the_ID();
    }

    if (!$event_id || get_post_type($event_id) !== CGIT_EVENTS_POST_TYPE) {
        return null;
    }

    return (int) $event_id;
}

/**
 * Return the range of months the calendar may display
 *
 * Runs from the month of the earliest event start date to the month of the
 * latest event end date, extended to include the current month. Both dates are
 * the first day of the month at midnight.
 *
 * @return array{min: DateTimeImmutable, max: DateTimeImmutable}
 */
function cgit_wp_events_get_calendar_bounds(): array
{
    static $bounds = null;

    if (!is_null($bounds)) {
        return $bounds;
    }

    global $wpdb;

    $now = new DateTimeImmutable('first day of this month midnight');
    $min = $now;
    $max = $now;

    $min_meta = $wpdb->get_var($wpdb->prepare(
        'SELECT MIN(m.meta_value)
            FROM %i AS m
            JOIN %i AS p
            ON m.post_id = p.ID
            WHERE m.meta_key = "start_date"
            AND p.post_type = %s
            AND p.post_status = "publish"',
        $wpdb->postmeta,
        $wpdb->posts,
        CGIT_EVENTS_POST_TYPE
    ));

    $max_meta = $wpdb->get_var($wpdb->prepare(
        'SELECT MAX(m.meta_value)
            FROM %i AS m
            JOIN %i AS p
            ON m.post_id = p.ID
            WHERE m.meta_key = "end_date"
            AND p.post_type = %s
            AND p.post_status = "publish"',
        $wpdb->postmeta,
        $wpdb->posts,
        CGIT_EVENTS_POST_TYPE
    ));

    if (is_string($min_meta) && preg_match('/^\d{8}$/', $min_meta)) {
        $date = DateTimeImmutable::createFromFormat('!Ymd', $min_meta);

        if ($date) {
            $min = min($min, $date->modify('first day of this month'));
        }
    }

    if (is_string($max_meta) && preg_match('/^\d{8}$/', $max_meta)) {
        $date = DateTimeImmutable::createFromFormat('!Ymd', $max_meta);

        if ($date) {
            $max = max($max, $date->modify('first day of this month'));
        }
    }

    $bounds = [
        'min' => $min,
        'max' => $max,
    ];

    return $bounds;
}

/**
 * Is the date within the range the calendar may display?
 *
 * With a year only, the year must overlap the range. With a month, the month
 * must be within the range. With a day, the date must also be valid.
 *
 * @param mixed $year
 * @param mixed $month
 * @param mixed $day
 * @return bool
 */
function cgit_wp_events_is_valid_calendar_date($year, $month = null, $day = null): bool
{
    foreach ([$year, $month, $day] as $part) {
        if (!is_null($part) && (!is_scalar($part) || !ctype_digit((string) $part))) {
            return false;
        }
    }

    $bounds = cgit_wp_events_get_calendar_bounds();
    $year = (int) $year;

    if (is_null($month)) {
        return $year >= (int) $bounds['min']->format('Y')
            && $year <= (int) $bounds['max']->format('Y');
    }

    $month = (int) $month;

    if ($month < 1 || $month > 12) {
        return false;
    }

    if (!is_null($day) && !checkdate($month, (int) $day, $year)) {
        return false;
    }

    $date = (new DateTimeImmutable('midnight'))->setDate($year, $month, 1);

    return $date >= $bounds['min'] && $date <= $bounds['max'];
}

/**
 * Return date range
 *
 * @param DateTime $start
 * @param DateTime $end
 * @return string
 */
function cgit_wp_events_get_date_range(DateTime $start, DateTime $end): string
{
    $default_formats = [
        'y' => 'Y',
        'm' => 'F',
        'd' => 'j',
        'dm' => 'j F',
        'my' => 'F Y',
        'dmy' => 'j F Y',
    ];

    $dash = cgit_wp_events_dash();
    $formats = apply_filters('cgit_wp_events_date_range_formats', $default_formats);

    $formats = array_intersect_key($formats, $default_formats);
    $formats = array_merge($default_formats, $formats);

    if ($start->format('Y') !== $end->format('Y')) {
        return $start->format($formats['dmy']) . $dash . $end->format($formats['dmy']);
    }

    if ($start->format('Y-m') !== $end->format('Y-m')) {
        return $start->format($formats['dm']) . $dash . $end->format($formats['dmy']);
    }

    if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
        return $start->format($formats['d']) . $dash . $end->format($formats['dmy']);
    }

    return $start->format($formats['dmy']);
}

/**
 * Return date/time event range dash
 *
 * @return string
 */
function cgit_wp_events_dash(): string
{
    $default = '&ndash;';
    $dash = apply_filters('cgit_wp_events_dash', $default);

    if (is_string($dash)) {
        return $dash;
    }

    return $default;
}

/**
 * Return event date range
 *
 * @param int|null $event_id
 * @return string|null
 */
function cgit_wp_events_get_event_date_range(?int $event_id = null): ?string
{
    $event_id = cgit_wp_events_sanitize_event_id($event_id);

    if (!$event_id) {
        return null;
    }

    $start_string = get_field('start_date', $event_id);
    $end_string = get_field('end_date', $event_id);

    if (!$end_string) {
        $end_string = $start_string;
    }

    $format = 'd/m/Y';
    $start = DateTime::createFromFormat($format, $start_string);
    $end = DateTime::createFromFormat($format, $end_string);

    if (!$start || !$end) {
        return null;
    }

    return cgit_wp_events_get_date_range($start, $end);
}

/**
 * Return event time range
 *
 * @param int|null $event_id
 * @return string|null
 */
function cgit_wp_events_get_event_time_range(?int $event_id = null): ?string
{
    $event_id = cgit_wp_events_sanitize_event_id($event_id);

    if (!$event_id) {
        return null;
    }

    if (get_field('all_day', $event_id)) {
        return apply_filters('cgit_wp_events_all_day', 'All day');
    }

    $input_format = 'H:i';
    $output_format = apply_filters('cgit_wp_events_time_format', 'g:ia');
    $dash = cgit_wp_events_dash();

    $start_string = get_field('start_time', $event_id);
    $end_string = get_field('end_time', $event_id);

    if (!$end_string) {
        $end_string = $start_string;
    }

    $start = DateTime::createFromFormat($input_format, $start_string);
    $end = DateTime::createFromFormat($input_format, $end_string);

    if ($start instanceof DateTime) {
        $start_string = $start->format($output_format);
    }

    if ($end instanceof DateTime) {
        $end_string = $end->format($output_format);
    }

    if (!$end_string || $start_string === $end_string) {
        return $start_string;
    }

    return $start_string . $dash . $end_string;
}
