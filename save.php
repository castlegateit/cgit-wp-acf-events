<?php

/**
 * Ensure required fields are set on save.
 *
 * The end date is not a required field per se, but it is required by the
 * modified query used to display events in the events archive. Therefore, it
 * remains optional in the WP editor, but if it is left blank, it is set to the
 * same value as the start date on save.
 */
add_filter('save_post', function ($post_id) {
    $not_post_type = isset($_POST['post_type'])
        && $_POST['post_type'] != CGIT_EVENTS_POST_TYPE;

    $end_date_set = isset($_POST['acf']['end_date'])
        && !empty($_POST['acf']['end_date']);

    if (!$_POST || $not_post_type || $end_date_set) {
        return $post_id;
    }

    $start = isset($_POST['acf']['start_date']) ? $_POST['acf']['start_date'] : '';
    update_field('end_date', $start, $post_id);

    return $post_id;
});
