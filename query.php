<?php

/**
 * Rewrite the archive and category page queries to filter and order the posts
 * correctly.
 *
 * @return void
 */
add_filter(
    'pre_get_posts',
    function ($query) {
        // Filter applies to front end queries only.
        if (is_admin()) {
            return;
        }

        // Apply the filter to the main query.
        if ($query->is_main_query()) {
            if (is_post_type_archive(CGIT_EVENTS_POST_TYPE)) {
                // Adjust the query for archives and main event listings
                cgit_wp_events_query_archive($query);
            } elseif (is_tax()) {
                // Adjust the query for category listings
                global $wp_query;

                $term = $wp_query->get_queried_object();

                if ($term
                    && $term->taxonomy == CGIT_EVENTS_POST_TYPE_CATEGORY
                ) {
                    cgit_wp_events_query_main_listing($query);
                }
            }
        }
    }
);

/**
 * Rewrite the events archive page SQL query. WordPress assumes the dates in the
 * URL are to show standard post archives by date. These are disabled and custom
 * queries generated to check against the meta values that the start and end
 * dates are stored in.
 *
 * @return void
 */
function cgit_wp_events_query_archive($query)
{
    // Get the dates from query vars
    $year = get_query_var('year', null);
    $month = get_query_var('monthnum', null);
    $day = get_query_var('day', null);

    $has_year = !empty($year);
    $has_month = !empty($month);
    $has_day = !empty($day);

    $date = (new DateTime())->setDate($year, $month, (empty($day) ? 1 : $day));

    define('CGIT_WP_EVENTS_YEAR', $date->format('Y'));
    define('CGIT_WP_EVENTS_MONTH', $date->format('n'));
    define('CGIT_WP_EVENTS_DAY', $date->format('j'));

    $meta_date_format = 'Ymd';
    $meta_date_format = apply_filters('cgit_wp_acf_events_meta_date_format', $meta_date_format);

    if ($has_year && $has_month && $has_day) {
        // Displaying a single day archive
        $query->set('meta_query', array(
            'relation' => 'AND',
            'order_by_clause' => array(
                'key' => 'start_date',
                'value' => $date->format($meta_date_format),
                'type' => 'NUMERIC',
                'compare' => '<='
            ),
            array(
                'key' => 'end_date',
                'value' => $date->format($meta_date_format),
                'type' => 'NUMERIC',
                'compare' => '>='
            )
        ));
        $query->set('orderby', 'order_by_clause');
        $query->set('order', 'ASC');

    } elseif ($has_year && $has_month) {
        // Number of days in this month
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Start of month
        $month_start = (new DateTime())->setDate($year, $month, 1);

        // End of the month
        $month_end = (new DateTime())->setDate($year, $month, $days_in_month);

        // Month archive
        $query->set('meta_query', array(
            'relation' => 'OR',
            array(
                'relation' => 'OR',
                array(
                    'key' => 'start_date',
                    'value' => [$month_start->format($meta_date_format), $month_end->format($meta_date_format)],
                    'type' => 'DATE',
                    'compare' => 'BETWEEN'
                ),
                array(
                    'key' => 'end_date',
                    'value' => [$month_start->format($meta_date_format), $month_end->format($meta_date_format)],
                    'type' => 'DATE',
                    'compare' => 'BETWEEN'
                )
            ),
            array(
                'relation' => 'AND',
                'order_by_clause' => array(
                    'key' => 'start_date',
                    'value' => $month_start->format($meta_date_format),
                    'type' => 'DATE',
                    'compare' => '<'
                ),
                array(
                    'key' => 'end_date',
                    'value' => $month_end->format($meta_date_format),
                    'type' => 'DATE',
                    'compare' => '>'
                )
            ),
        ));
        $query->set('orderby', 'order_by_clause');
        $query->set('order', 'ASC');
    } elseif ($has_year) {
        // Start of the year
        $year_start = (new DateTime())->setDate($year, 1, 1);

        // End of the year
        $year_end = (new DateTime())->setDate($year, 12, 31);

        // Year archive
        $query->set('meta_query', array(
            'relation' => 'OR',
            array(
                'relation' => 'OR',
                array(
                    'key' => 'start_date',
                    'value' => [$year_start->format($meta_date_format), $year_end->format($meta_date_format)],
                    'type' => 'DATE',
                    'compare' => 'BETWEEN'
                ),
                array(
                    'key' => 'end_date',
                    'value' => [$year_start->format($meta_date_format), $year_end->format($meta_date_format)],
                    'type' => 'DATE',
                    'compare' => 'BETWEEN'
                )
            ),
            array(
                'relation' => 'AND',
                'order_by_clause' => array(
                    'key' => 'start_date',
                    'value' => $year_start->format($meta_date_format),
                    'type' => 'DATE',
                    'compare' => '<'
                ),
                array(
                    'key' => 'end_date',
                    'value' => $year_end->format($meta_date_format),
                    'type' => 'DATE',
                    'compare' => '>'
                )
            ),
        ));
        $query->set('orderby', 'order_by_clause');
        $query->set('order', 'ASC');
    } else {
        // This is the main listing of events
        cgit_wp_events_query_main_listing($query);
    }

    /**
     * If we are showing an archive, we just adjust the order and remove
     * standard year/month/day filtering
     */
    if ($year) {
        $query->set('year', '');
        $query->set('monthnum', '');
        $query->set('day', '');
    }

}


/**
 * Rewrite the category listings. Join with the meta tables so we can order the
 * results by start_date.
 *
 * @return void
 */
function cgit_wp_events_query_main_listing($query)
{
    $now = new DateTime('now');
    $compare = (new DateTime('now'))->modify('+900 years');

    $meta_date_format = 'Ymd';
    $meta_date_format = apply_filters('cgit_wp_acf_events_meta_date_format', $meta_date_format);

    /**
     * Where start_date is greater than one. This is here purely to force a join
     * on the meta tables without manually overwriting the join.
     */
    $query->set('meta_query',
        array(
            'relation' => 'AND',
            array(
                'key' => 'start_date',
                'value' => $now->format($meta_date_format),
                'type' => 'DATE',
                'compare' => '>='
            ),
            'order_by_clause' => array(
                'key' => 'start_date',
                'value' => $compare->format($meta_date_format),
                'type' => 'DATE',
                'compare' => '!='
            ),
        )
    );

    // Order by start date
    $query->set('orderby', 'order_by_clause');
    $query->set('order', 'ASC');
}
