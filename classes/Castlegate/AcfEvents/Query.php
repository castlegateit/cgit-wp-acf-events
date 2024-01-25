<?php

namespace Castlegate\AcfEvents;

use DateTime;
use WP_Query;

class Query
{
    /**
     * Initialise
     *
     * @return void
     */
    public static function init(): void
    {
        // Filter front-end event archive query
        add_filter(
            'pre_get_posts',
            [get_called_class(), 'filterArchive']
        );

        // Filter front-end event taxonomy query
        add_filter(
            'pre_get_posts',
            [get_called_class(), 'filterTaxonomy']
        );

        // Filter front-end event query where clause
        add_filter(
            'posts_where',
            [get_called_class(), 'filterWhere'],
            10, 2
        );
    }

    /**
     * Rewrite the archive and category page queries to filter and order the
     * posts correctly. This method applies to non-admin, main queries and not
     * taxonomy queries. It calls a number of different filter methods depending
     * on whether date URL parameters are present.
     *
     * @return void
     */
    public static function filterArchive(WP_Query $query)
    {
        // Override action name
        $action = 'cgit_wp_acf_events_query_archive_override';

        // Override query?
        if (has_action($action)) {
            do_action($action, $query);
            return;
        }

        // Apply to main query only
        if (!$query->is_main_query() || is_admin()) {
            return;
        }

        // Apply to archive and not taxonomy
        if (!(is_post_type_archive(CGIT_EVENTS_POST_TYPE)
            && !is_tax(CGIT_EVENTS_POST_TYPE_CATEGORY))
        ) {
            return;
        }

        if (self::isDayArchive()) {
            self::filterArchiveDay($query);
        } elseif (self::isMonthArchive()) {
            self::filterArchiveMonth($query);
        } elseif (self::isYearArchive()) {
            self::filterArchiveYear($query);
        } else {
            self::filterIndex($query);
        }
    }

    /**
     * Rewrite the archive queries to filter and order the posts correctly. This
     * method is called from the archive filter (if no dates have been provided)
     * and from the taxonomy filter.
     *
     * @return void
     */
    public static function filterIndex($query)
    {
        // Override action name
        $action = 'cgit_wp_acf_events_query_index_override';

        // Override query?
        if (has_action($action)) {
            do_action($action, $query);
            return;
        }

        // DateTime for now
        $now = new DateTime('now');

        // DateTime for end time
        $end = (new DateTime('now'))->modify('+999 years');

        // Get date format
        $format = self::getQueryDateFormat();

        $query->set(
            'meta_query',
            [
                'relation' => 'AND',
                [
                    'key' => 'start_date',
                    'value' => $now->format($format),
                    'type' => 'DATE',
                    'compare' => '>='
                ],
                'order_by_clause' => [
                    'key' => 'start_date',
                    'value' => $end->format($format),
                    'type' => 'DATE',
                    'compare' => '<='
                ],
            ]
        );

        // Order by start date
        $query->set('orderby', 'order_by_clause');
        $query->set('order', 'ASC');
    }

    /**
     * Filter queries for events which contain a taxonomy. This calls the
     * filterIndex method since WordPress handles the taxonomy aspect of the
     * query already. We simply need to apply the date-related meta query that
     * is used for the main event index query
     *
     * @param WP_Query $query
     * @return void
     */
    public static function filterTaxonomy(WP_Query $query): void
    {
        // Override action name
        $action = 'cgit_wp_acf_events_query_taxonomy_override';

        // Override query?
        if (has_action($action)) {
            do_action($action, $query);
            return;
        }

        // Apply to main query only
        if (!$query->is_main_query()) {
            return;
        }

        if (is_tax(CGIT_EVENTS_POST_TYPE_CATEGORY)) {
            // Adjust the query for category listings
            global $wp_query;

            $term = $wp_query->get_queried_object();
            if ($term && $term->taxonomy == CGIT_EVENTS_POST_TYPE_CATEGORY) {
                self::filterIndex($query);
            }
        }
    }

    /**
     * Database query filter to include only events that occur on a specific
     * day, month and year
     *
     * @param WP_Query $query
     * @return void
     */
    public static function filterArchiveDay(WP_Query $query): void
    {
        // Override action name
        $action = 'cgit_wp_acf_events_query_archive_day_override';

        // Override query?
        if (has_action($action)) {
            do_action($action, $query);
            return;
        }

        // Get date format
        $format = self::getQueryDateFormat();

        // Query date
        $date = self::getQueryDate();

        // Displaying a single day archive
        $query->set(
            'meta_query',
            [
                'relation' => 'AND',
                [
                    'key' => 'start_date',
                    'value' => $date->format($format),
                    'type' => 'NUMERIC',
                    'compare' => '<='
                ],
                [
                    'key' => 'end_date',
                    'value' => $date->format($format),
                    'type' => 'NUMERIC',
                    'compare' => '>='
                ]
            ]
        );

        $query->set('orderby', 'meta_value_num');
        $query->set('meta_key', 'start_date');
        $query->set('order', 'DESC');
    }

    /**
     * Database query filter to include only events that occur on a specific
     * year and month
     *
     * @param WP_Query $query
     * @return void
     */
    public static function filterArchiveMonth(WP_Query $query): void
    {
        // Override action name
        $action = 'cgit_wp_acf_events_query_archive_month_override';

        // Override query?
        if (has_action($action)) {
            do_action($action, $query);
            return;
        }

        // Create DateTime for the start of month
        $start = (new DateTime())->setDate(
            self::getQueryYear(), self::getQueryMonth(), 1
        );

        // Number of days in requested month
        $days_in_month = cal_days_in_month(
            CAL_GREGORIAN,
            self::getQueryMonth(),
            self::getQueryYear()
        );

        // Create DateTime for the end of the month
        $end = (new DateTime())->setDate(
            self::getQueryYear(), self::getQueryMonth(), $days_in_month
        );

        // Get date format
        $format = self::getQueryDateFormat();

        // Month archive
        $query->set(
            'meta_query',
            [
                'relation' => 'OR',
                [
                    'relation' => 'OR',
                    [
                        'key' => 'start_date',
                        'value' => [$start->format($format), $end->format($format)],
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    ],
                    [
                        'key' => 'end_date',
                        'value' => [$start->format($format), $end->format($format)],
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    ]
                ],
                [
                    'relation' => 'AND',
                    [
                        'key' => 'start_date',
                        'value' => $start->format($format),
                        'type' => 'NUMERIC',
                        'compare' => '<'
                    ],
                    [
                        'key' => 'end_date',
                        'value' => $end->format($format),
                        'type' => 'NUMERIC',
                        'compare' => '>'
                    ]
                ],
            ]
        );

        $query->set('orderby', 'meta_value_num');
        $query->set('meta_key', 'start_date');
        $query->set('order', 'DESC');
    }

    /**
     * Database query filter to include only events that occur on a specific
     * year
     *
     * @param WP_Query $query
     * @return void
     */
    public static function filterArchiveYear(WP_Query $query): void
    {
        // Override action name
        $action = 'cgit_wp_acf_events_query_archive_year_override';

        // Override query?
        if (has_action($action)) {
            do_action($action, $query);
            return;
        }

        // Create DateTime at the start of the year
        $start = (new DateTime())->setDate(
            self::getQueryYear(), 1, 1
        );

        // Create DateTime at the end of the year
        $end = (new DateTime())->setDate(
            self::getQueryYear(), 12, 31
        );

        $format = self::getQueryDateFormat();

        // Year archive
        $query->set(
            'meta_query',
            [
                'relation' => 'OR',
                [
                    'relation' => 'OR',
                    'start_date' => [
                        'key' => 'start_date',
                        'value' => [$start->format($format), $end->format($format)],
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    ],
                    'end_date' => [
                        'key' => 'end_date',
                        'value' => [$start->format($format), $end->format($format)],
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN'
                    ]
                ],
                [
                    'relation' => 'AND',
                    [
                        'key' => 'start_date',
                        'value' => $start->format($format),
                        'type' => 'NUMERIC',
                        'compare' => '<'
                    ],
                    [
                        'key' => 'end_date',
                        'value' => $end->format($format),
                        'type' => 'NUMERIC',
                        'compare' => '>'
                    ]
                ],
            ]
        );

        $query->set('orderby', 'meta_value_num');
        $query->set('meta_key', 'start_date');
        $query->set('order', 'DESC');
    }

    /**
     * The default WHERE clause will take date parameters and use them to
     * restrict the queries to specific publish dates. We need to remove this
     * so that we can use the query vars for event dates instead
     *
     * @param string $where
     * @param WP_Query $query
     * @return string
     */
    public static function filterWhere(string $where, WP_Query $query)
    {
        // Override action name
        $filter = 'cgit_wp_acf_events_query_where_override';

        // Override query?
        if (has_filter($filter)) {
            return apply_filters($filter, $where, $query);
        }

        // Filter applies to front end queries only.
        if (is_admin()) {
            return $where;
        }

        if (is_post_type_archive(CGIT_EVENTS_POST_TYPE)) {

            // Year archive
            if (self::isYearArchive()) {
                $regex = '/\s*AND\s*\(\s*YEAR\s*\([^\)]+?\)\s*=\s*\d+\s*\)/';
                $where = preg_replace($regex, '', $where);
            }

            // Monthly archive
            if (self::isMonthArchive()) {
                $regex = '/\s*AND\s*\(\s*\(\s*YEAR\s*\([^\)]+?\)\s*=\s*\d+\s*AND\s*MONTH\s*\([^\)]+?\)\s*=\s*\d+\s*\)\s*\)/';
                $where = preg_replace($regex, '', $where);
            }

            // Day archive
            if (self::isDayArchive()) {
                $regex = '/\s*AND\s*\(\s*\(\s*YEAR\s*\([^\)]+?\)\s*=\s*\d+\s*AND\s*MONTH\s*\([^\)]+?\)\s*=\s*\d+\s*AND\s*DAYOFMONTH\s*\([^\)]+?\)\s*=\s*\d+\s*\)\s*\)/';
                $where = preg_replace($regex, '', $where);
            }
        }

        return $where;
    }

    /**
     * Check year query variable
     *
     * @return int|null
     */
    public static function getQueryYear(): ?int
    {
        $year = get_query_var('year', null);

        if (!empty($year)) {
            return (int) $year;
        }

        return $year;
    }

    /**
     * Check month query variable
     *
     * @return int|null
     */
    public static function getQueryMonth(): ?int
    {
        $month = get_query_var('monthnum', null);

        if (!empty($month)) {
            return (int)$month;
        }

        return $month;
    }

    /**
     * Check day query variable
     *
     * @return int|null
     */
    public static function getQueryDay(): ?int
    {
        $day = get_query_var('day', null);

        if (!empty($day)) {
            return (int) $day;
        }

        return $day;
    }

    /**
     * Check if the requested URL is a day archive
     *
     * @return bool
     */
    public static function isDayArchive(): bool
    {
        // Require these values
        $check = [
            self::getQueryDay(),
            self::getQueryMonth(),
            self::getQueryYear(),
        ];

        // Remove non integer values
        $callback = function($a) {
            return is_int($a) && $a > 0;
        };

        return count(array_filter($check, $callback)) === 3;
    }

    /**
     * Check if the requested URL is a month archive
     *
     * @return bool
     */
    public static function isMonthArchive(): bool
    {
        // Require these values
        $check = [
            self::getQueryMonth(),
            self::getQueryYear(),
        ];

        // Remove non integer values
        $callback = function($a) {
            return is_int($a) && $a > 0;
        };

        $maybe_month = count(array_filter($check, $callback)) === 2;

        // If it's not a day archive, then it's a month archive
        return $maybe_month && !self::isDayArchive();
    }

    /**
     * Check if the requested URL is a year archive
     *
     * @return bool
     */
    public static function isYearArchive(): bool
    {
        // If it's not a day/month archive, then it's probably a year archive
        $maybe_year = is_int(self::getQueryYear()) && self::getQueryYear() > 0;

        return $maybe_year && !self::isMonthArchive() && !self::isDayArchive();
    }

    /**
     * Get the query date as a DateTime instance
     *
     * @return DateTime
     */
    public static function getQueryDate(): DateTime
    {
        $date = (new DateTime())->setDate(
            self::getQueryYear(),
            self::getQueryMonth(),
            (self::getQueryDay() ?? 1)
        );

        // Legacy constants
        define('CGIT_WP_EVENTS_YEAR', $date->format('Y'));
        define('CGIT_WP_EVENTS_MONTH', $date->format('n'));
        define('CGIT_WP_EVENTS_DAY', $date->format('j'));

        return $date;
    }

    /**
     * Get the date format used in database queries
     *
     * @return string
     */
    public static function getQueryDateFormat(): string
    {
        return apply_filters('cgit_wp_acf_events_meta_date_format', 'Ymd');
    }
}