<?php

/**
 * Plugin activation hook to check that required plugins are installed and to
 * flush rewrite rules so custom rules will take effect.
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_activate() {

    // Set default options
    cgit_wp_events_default_options();

    // Flush rewrite rules
    add_filter('wp_loaded', 'cgit_wp_events_flush_rules');
}


/**
 * Assign the default option values
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_default_options() {

    // Set default options
    foreach (cgit_wp_events::$options as $option => $value) {

        if (empty(get_option($option))) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    update_option($option, $v);
                    break;
                }
            } else {
                update_option($option, $value);
            }
        }

    }

}


/**
 * Remove all options
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_uninstall() {

    global $default_options;

    // Delete saved options
    foreach ($default_options as $option => $value) {
        delete_option($option);
    }

    // Flush rewrite rules
    cgit_wp_events_flush_rules();

}
