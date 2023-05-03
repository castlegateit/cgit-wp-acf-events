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
function cgit_wp_events_activate()
{
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
function cgit_wp_events_default_options()
{
    foreach (cgit_wp_events::get_options() as $option => $value) {
        if (is_null(get_option($option, null))) {
            update_option($option, $value);
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
function cgit_wp_events_uninstall()
{
    // Delete saved options
    foreach (cgit_wp_events::get_options() as $option => $value) {
        delete_option($option);
    }

    // Flush rewrite rules
    cgit_wp_events_flush_rules();
}
