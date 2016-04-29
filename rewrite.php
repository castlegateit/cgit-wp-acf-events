<?php

/**
 * Check for the existence of the rewrite rules on each page load and flushes
 * the rules if they do not exist.
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_apply_rules() {

    global $wp_rewrite;

    // Get the post type for the archive slug and check it has an archive
    $post_type = get_post_type_object(CGIT_EVENTS_POST_TYPE);
    if (!$post_type->has_archive) {
        return;
    }

    // Check for the main rewrite rule and flush if it does not exist
    $check_rule = $post_type->rewrite['slug'] . '/?$';
    $rules = $wp_rewrite->wp_rewrite_rules();
    if ($rules && in_array($check_rule, array_keys($rules))) {
        cgit_wp_events_flush_rules();
    }
}
add_filter('init', 'cgit_wp_events_apply_rules');


/**
 * Setup date archives rewrite rules for the custom post type.
 *
 * @param array $existing_rules Existing WP rewrite rules
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return array
 */
function cgit_wp_events_generate_archives($existing_rules) {
    global $wp_rewrite;

    $rules = array();

    // Get the post type for the archive slug
    $post_type = get_post_type_object(CGIT_EVENTS_POST_TYPE);
    $slug_archive = $post_type->has_archive;

    // The post type always has an archive, but include this check anyway
    if ($slug_archive === false) {
        return $rules;
    }

    // Define the archive slug if it's not already defined
    if ($slug_archive === true) {
        $slug_archive = $post_type->rewrite['slug'];
        if (!$post_type->rewrite['slug']) {
            $slug_archive = $post_type->name;
        }
    }

    // Build an array of regex rules and their query vars
    $dates = array(
        array(
            'rule' => "([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})",
            'vars' => array('year', 'monthnum', 'day')
        ),
        array(
            'rule' => "([0-9]{4})/([0-9]{1,2})",
            'vars' => array('year', 'monthnum')
        ),
        array(
            'rule' => "([0-9]{4})",
            'vars' => array('year')
        )
    );

    // Process each rewrite rule
    foreach ($dates as $data) {

        $query = 'index.php?post_type=' . CGIT_EVENTS_POST_TYPE;
        $rule = $slug_archive . '/' . $data['rule'];

        $i = 1;
        foreach ($data['vars'] as $var) {
            $query.= '&' . $var . '=$matches[' . $i . ']';
            $i++;
        }

        // The completed rules
        $regex = $rule . '/?$';
        $rules[$regex] = $query;

        // Rules for feeds
        $regex = $rule . '/feed/(feed|rdf|rss|rss2|atom)/?$';
        $rules[$regex] = $query . '&feed=$matches[' . $i . ']';
        $regex = $rule . '/(feed|rdf|rss|rss2|atom)/?$';
        $rules[$regex] = $query . '&feed=$matches[' . $i . ']';

        // Rules for pages
        $regex = $rule . '/page/([0-9]{1,})/?$';
        $rules[$regex] = $query.'&paged=$matches[' . $i . ']';
    }

    // Return the rules
    return $rules + $existing_rules;
}
add_filter('rewrite_rules_array', 'cgit_wp_events_generate_archives');


/**
 * Flush the rewrite rules
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_flush_rules() {

    global $wp_rewrite;
    $wp_rewrite->flush_rules();

}


