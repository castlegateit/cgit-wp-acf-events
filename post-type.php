<?php

/**
 * Defines the event custom post type
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_post_type()
{
    $labels = [
        'name' => 'Events',
        'singular_name' => 'Event',
        'add_new_item' => 'Add New Event',
        'edit_item' => 'Edit Event',
        'new_item' => 'New Event',
        'view_item' => 'View Event',
        'view_items' => 'View Events',
        'search_items' => 'Search Events',
        'not_found' => 'No events found',
        'not_found_in_trash' => 'No events found in trash',
        'all_items' => 'All Events',
        'archives' => 'Event Archives',
        'attributes' => 'Event Attributes',
        'insert_into_item' => 'Insert into event',
        'filter_items_list' => 'Filter events list',
        'items_list_navigation' => 'Events list navigation',
        'items_list' => 'Events list',
        'item_published' => 'Event published',
        'item_published_privately' => 'Event published privately',
        'item_reverted_to_draft' => 'Event reverted to draft',
        'item_scheduled' => 'Event scheduled',
        'item_updated' => 'Event updated',
        'item_link' => 'Event Link',
        'item_link_description' => 'A link to an event',
    ];

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
        'supports' => cgit_wp_events::get_post_type_supports_args(),
        'has_archive' => true,
        'rewrite' => $rewrite,
        'query_var' => CGIT_EVENTS_POST_TYPE,
    );

    // Legacy support for the post tag taxonomy, shared with posts
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
function cgit_wp_events_taxonomy()
{
    if (cgit_wp_events::has_category_taxonomy()) {
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
