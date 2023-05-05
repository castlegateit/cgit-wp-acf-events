<?php

declare(strict_types=1);

/**
 * Return sanitized event ID
 *
 * @param int|null $event_id
 * @return int|null
 */
function cgit_wp_events_sanitize_event_id(int $event_id = null): ?int
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
function cgit_wp_events_get_event_date_range(int $event_id = null): ?string
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
function cgit_wp_events_get_event_time_range(int $event_id = null): ?string
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
