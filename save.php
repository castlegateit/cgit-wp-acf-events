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
    if (!$_POST || $_POST['post_type'] != CGIT_EVENTS_POST_TYPE ||
        $_POST['acf']['end_date']) {
        return $post_id;
    }

    // $_POST['acf']['end_date'] = $_POST['acf']['start_date'];
    update_field('end_date', $_POST['acf']['start_date'], $post_id);

    return $post_id;
});
