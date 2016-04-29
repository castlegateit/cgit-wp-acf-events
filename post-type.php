<?php

/**
 * Defines the event custom post type
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_post_type() {

    $labels = array(
        'name' => 'Events',
        'singular_name' => 'Event',
        'add_new_item' => 'Add New Event',
        'edit_item' => 'Edit Event',
        'new_item' => 'New Event',
        'view_item' => 'View Event',
        'search_items' => 'Search Events',
        'not_found' => 'No events found',
        'not_found_in_trash' => 'No events found in Trash',
    );

    // Get support from options
    $supports = array(
        'title',
        'revisions'
    );

    foreach (cgit_wp_events::$options as $option => $v) {

        if (substr($option, 0, 33) == 'cgit_wp_events_post_type_support_'
            && get_option($option) == 1
        ) {
            $supports[] = substr($option, 33);
        }
    }

    // Post type rewrite options
    $rewrite = array(
        'slug' => CGIT_EVENTS_POST_TYPE,
        'with_front' => false,
    );

    // Post type options
    $options = array(
        'labels' => $labels,
        'public' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => $supports,
        'has_archive' => true,
        'rewrite' => $rewrite,
        'query_var' => CGIT_EVENTS_POST_TYPE,
    );

    // Should we include tags?
    if (get_option('cgit_wp_events_post_type_support_tag') == 1) {
        $options['taxonomies'] = array('post_tag');
    }

    register_post_type(CGIT_EVENTS_POST_TYPE, $options);

}
add_action('init', 'cgit_wp_events_post_type', 10);


/**
 * Defines the categories taxonomy
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_taxonomy() {

    if (get_option('cgit_wp_events_post_type_support_category') == 1) {
        $labels = array(
            'name' => 'Categories',
            'singular_name' => 'Category',
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
        );

        register_taxonomy(
            CGIT_EVENTS_POST_TYPE_CATEGORY,
            CGIT_EVENTS_POST_TYPE,
            $args
        );
    }

}
add_action('init', 'cgit_wp_events_taxonomy');
