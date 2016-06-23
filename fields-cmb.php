<?php

/**
 * Add custom fields using Custom Meta Boxes
 *
 * Custom Meta Boxes uses filters to add fields, so these definitions will be
 * ignored safely if the Custom Meta Boxes plugin is not installed.
 */


/**
 * Add custom meta box fields for our events
 *
 * @todo We ought to make UK_date use datetimes rather than unix timestamps
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return array
 */
function cgit_wp_events_fields($meta_boxes)
{
    $date_type = 'date_unix';
    $types = _cmb_available_fields();

    if (array_key_exists('UK_date', $types)) {
        $date_type = 'UK_date';
    }

    $meta_boxes[] = array(
        'id' => 'cgit-events-when',
        'title' => 'When',
        'pages' => array(CGIT_EVENTS_POST_TYPE),
        'context' => 'side',
        'fields' => array(
            array(
                'id' => 'start_date',
                'name' => 'Start Date',
                'type' => $date_type,
                'required' => true,
            ),
            array(
                'id' => 'start_time',
                'name' => 'Start Time',
                'type' => 'time',
            ),
            array(
                'id' => 'end_date',
                'name' => 'End Date',
                'type' => $date_type,
            ),
            array(
                'id' => 'end_time',
                'name' => 'End Time',
                'type' => 'time',
            ),
            array(
                'id' => 'all_day',
                'name' => 'All day event?',
                'type' => 'select',
                'options' => array(
                    0 => 'No',
                    1 => 'Yes',
                ),
            ),
        ),
    );

    $meta_boxes[] = array(
        'id' => 'cgit-events-where',
        'title' => 'Where',
        'pages' => array(CGIT_EVENTS_POST_TYPE),
        'context' => 'normal',
        'fields' => array(
            array(
                'id' => 'location_name',
                'name' => 'Location name',
                'type' => 'text',
            ),
            array(
                'id' => 'address',
                'name' => 'Address',
                'type' => 'textarea',
            ),
            array(
                'id' => 'location',
                'name' => 'Location',
                'type' => 'gmap',
            ),
            array(
                'id' => 'price',
                'name' => 'Price',
                'type' => 'text',
            ),
        ),
    );

    return $meta_boxes;

}
add_filter('cmb_meta_boxes', 'cgit_wp_events_fields');


/**
 * Hack: custom-meta-boxes does not currently allow for required fields. We
 * require an end date. Forces empty end dates to be the same as the start date
 *
 * @todo Should also check that end date is after start date here, it can be set
 * to start date if not. JS validation would improve this.
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_save_post($post_id)
{
    if (get_post_type($post_id) != CGIT_EVENTS_POST_TYPE || !isset($_POST['start_date'])) {
        return;
    }

    // Get dates
    $start = esc_attr($_POST['start_date']);
    $end = esc_attr($_POST['end_date']);

    // Check they are arrays as expected
    if (is_array($start)) {
        $start =  reset($start);
    }
    if (is_array($end)) {
        $end =  reset($end);
    }

    // If the end date is empty, copy the start date.
    if (empty($end)) {
        $_POST['end_date'] = $_POST['start_date'];
    }

}
add_action('save_post', 'cgit_wp_events_save_post');
