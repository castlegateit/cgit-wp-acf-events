<?php

/**
 * Enqueue the calendar AJAX javascript
 */
function cgit_events_scripts_init()
{
    wp_enqueue_script(
        'cgit-events-calendar',
        plugins_url('/js/calendar.js', __FILE__),
        [],
        CGIT_EVENTS_VERSION,
        ['strategy' => 'defer']
    );

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
function cgit_events_calendar_callback()
{
    if (isset($_POST['year']) && isset($_POST['month'])) {
        // Prevent infinite indexing of calendar pages by restricting display
        // to the range of event dates
        if (!cgit_wp_events_is_valid_calendar_date($_POST['year'], $_POST['month'])) {
            wp_die('', '', ['response' => 404]);
        }

        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];

        $calendar = new Cgit_event_calendar(
            $year,
            $month
        );

        $calendar->full = (bool) ($_POST['full'] ?? false);

        echo $calendar->getAjax();
    }
    wp_die();
}


/**
 * Filter must be applied to front end requests only, privileged and non privileged users
 */
add_action('wp_ajax_cgit_events_calendar', 'cgit_events_calendar_callback');
add_action('wp_ajax_nopriv_cgit_events_calendar', 'cgit_events_calendar_callback');
