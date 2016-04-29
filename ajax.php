<?php

/**
 * Enqueue the calendar AJAX javascript
 */
function cgit_events_scripts_init() {

    wp_enqueue_script('cgit-events-calendar', plugins_url('/js/calendar.js', __FILE__ ), array('jquery'));

    /**
     * Define the AJAX handler
     */
    wp_localize_script(
        'cgit-events-calendar',
        'ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php')
        )
    );

}

add_action('wp_enqueue_scripts', 'cgit_events_scripts_init');


/**
 * AJAX handler function
 */
function cgit_events_calendar_callback() {

    if (isset($_POST['year']) && isset($_POST['month'])) {

        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];

        // Prevent infinite indexing of calendar pages by restricting display
        // to fifteen year in the past/future
        if ($year > date('Y') + 15 || $year < date('Y') - 15) {
            $year = date('Y');
        }

        $calendar = new Cgit_event_calendar(
            $year,
            $month
        );

        echo json_encode($calendar->get_ajax());

    }
    wp_die();
}


/**
 * Filter must be applied to front end requests only, privileged and non privileged users
 */
add_action('wp_ajax_cgit_events_calendar', 'cgit_events_calendar_callback');
add_action('wp_ajax_nopriv_cgit_events_calendar', 'cgit_events_calendar_callback');
