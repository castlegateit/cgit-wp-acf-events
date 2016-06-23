<?php

class Cgit_event_calendar {

    // -------------------------------------------------------------------------

    /**
     * Current calendar year
     *
     * @var integer
     */
    private $year = 0;

    /**
     * Current calendar month
     *
     * @var integer
     */
    private $month = 0;

    /**
     * Calendar week start
     *
     * @var string
     */
    private $week_start = "Monday";

    /**
     * Array of classes, shortened with array keys to keep template code to a
     * minimum
     *
     * @var array
     */
    private $class = array(
        'pm' => 'prev-month',
        'py' => 'prev-year',
        'nm' => 'next-month',
        'ny' => 'next-year',
        'ca' => 'calendar',
        'co' => 'control',
        'cu' => 'current',
        'wd' => 'weekday',
        'pa' => 'past',
        'fu' => 'future',
        'to' => 'today',
        'ev' => 'events'
    );

    /**
     * WordPress plugin options required for the calendar
     *
     * @var string
     */
    private $options = array();

    // -------------------------------------------------------------------------

    /**
     * Check the year and month and set to today if invalid. Set all options
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return void
     */
    public function __construct($year, $month)
    {
        $this->setOptions();

        // Set year and month
        if (checkdate($month, 1, $year)) {
            $this->year = $year;
            $this->month = $month;

            return;
        }

        $this->year = date('Y');
        $this->month = date('m');
    }

    // -------------------------------------------------------------------------

    /**
     * Configure an array of options from WordPress' saved plugin settings
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return void
     */
    public function setOptions()
    {
        // Set options
        $this->options = array(
            'format_day' => apply_filters('cgit_wp_events_format_day', 'j'),
            'class_prefix' => apply_filters('cgit_wp_events_class_prefix', 'cgit-events-'),
            'current_month' => apply_filters('cgit_wp_events_format_current_month', 'M Y'),
            'format_next_year' => apply_filters('cgit_wp_events_format_next_year', '&raquo;'),
            'format_prev_year' => apply_filters('cgit_wp_events_format_prev_year', '&laquo;'),
            'format_next_month' => apply_filters('cgit_wp_events_format_next_month', '&rsaquo;'),
            'format_prev_month' => apply_filters('cgit_wp_events_format_prev_month', '&lsaquo;')
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Render the calendar
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return string
     */
    public function render()
    {
        $out = "<table class=\"" . $this->c('ca') . "\"";
        $out.= " data-cgit-events-year=\"" . $this->year . "\"";
        $out.= " data-cgit-events-month=\"" . $this->month . "\">\n";
        $out.= $this->header();
        $out.= $this->days();
        $out.= "</table>";

        return $out;
    }

    // -------------------------------------------------------------------------

    /**
     * Render the calendar header
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return string
     */
    private function header()
    {
        // Current month
        $current = new DateTime(
            $this->year . '-' . $this->month . '-01 00:00:00'
        );

        $prev_year = $this->year - 1;
        $next_year = $this->year + 1;
        $prev_month = $this->month - 1;
        $next_month = $this->month + 1;

        $prev_year_link = http_build_query(array(
            'cgit-year' => $prev_year,
            'cgit-month' => $this->month
        ));

        $next_year_link = http_build_query(array(
            'cgit-year' => $next_year,
            'cgit-month' => $this->month
        ));

        $prev_month_link = http_build_query(array(
            'cgit-year' => $this->year,
            'cgit-month' => $prev_month
        ));

        $next_month_link = http_build_query(array(
            'cgit-year' => $this->year,
            'cgit-month' => $next_month
        ));

        $out = "<thead>\n";
        $out.= "<tr>\n";

        // Previous year
        $out.= "<th class=\"" . $this->c('co,py') . "\">";
            $out.= "<a href=\"?" . htmlentities($prev_year_link) . "\"><span>";
            $out.= $this->options['format_prev_year'] . "</span></a>";
        $out.= "</th>\n";

        // Previous month
        $out.= "<th class=\"" . $this->c('co,pm') . "\">";
            $out.= "<a href=\"?" . htmlentities($prev_month_link) . "\"><span>";
            $out.= $this->options['format_prev_month'] . "</span></a>";
        $out.= "</th>\n";

        // Current month
        $out.= "<th colspan=\"3\" class=\"" . $this->c('cu') . "\">";
            $out.= "<a href=\"";
            $out.= get_post_type_archive_link('event') .$current->format('Y/m');
            $out.= "\"><span>";
            $out.= $current->format($this->options['current_month']);
            $out.= "</span></a>";
        $out.= "</th>\n";

        // Next month
        $out.= "<th class=\"" . $this->c('co,nm') . "\">";
            $out.= "<a href=\"?" . htmlentities($next_month_link) . "\"><span>";
            $out.= $this->options['format_next_month'] . "</span></a>";
        $out.= "</th>\n";

        // Next year
        $out.= "<th class=\"" . $this->c('co,ny') . "\">";
            $out.= "<a href=\"?" . htmlentities($next_year_link) . "\"><span>";
            $out.= $this->options['format_next_year'] . "</span></a>";
        $out.= "</th>\n";

        $out.= "</tr>\n";
        $out.= "<tr>\n";
        $days = new DateTime($this->week_start);
        for ($i = 0; $i <= 6; $i++) {
            $out.= "<th class=\"" . $this->c('wd') . "\"><span>";
            $out.= substr($days->format('D'), 0, 2) . "</span></th>\n";
            $days->add(new DateInterval('P1D'));
        }

        $out.= "</tr>\n";
        $out.= "</thead>\n";

        return $out;
    }

    // -------------------------------------------------------------------------

    /**
     * Render the calendar days
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return string
     */
    private function days()
    {
        // Output variable
        $out = '';

        // Loop through and output calendar days
        $i = 1;
        foreach ($this->getDays($this->year, $this->month) as $day) {
            if ($i == 1) {
                $out.= "<tr>\n";
            }

            $link = "";

            $out.= "<td class=\"" . $day['class'] . "\"><a";

            if ($day['events']) {
                if (count($day['events']) == 1) {
                    $link = reset($day['events']);
                    $link = $link['permalink'];
                } else {
                    $link = $day['link'];
                }

                $out.= " href=\"" . $link . "\">" . $day['date'];
            } else {
                $out.= ">" . $day['date'];
            }

            $out.= "</a></td>\n";

            if ($i == 7) {
                $out.= "</tr>\n";
                $i = 0;
            }

            $i++;
        }

        return $out;
    }

    // -------------------------------------------------------------------------

    /**
     * Returns an array of day data, include classes and number of events
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return array
     */
    private function getDays()
    {
        // DateTime for now
        $now = new DateTime('now');

        // DateTime for the month we are going to view
        $start = new DateTime($this->year . '-' . $this->month . '-01');

        // Clone it so we have a DateTime for the current month for comparisons
        // later
        $current = clone $start;

        /**
         * We begin at the first Monday of the month, minus 7 days
         * to create a 6 row calendar. Monday can change depending on the week
         * start
         */
        $start->modify(
            'first ' . ucwords($this->week_start) . ' of '
            . $start->format('F') . ' ' . $start->format('Y')
        );
        $start->modify('-7 days');

        // The end date is the start day plus 42 (6 rows of 7 days). Simply add
        // 42 days to our calculated start
        $end = clone $start;
        $end = $end->modify('+42 days');



        // Clone the current month DateTime for use in building our query
        $query_start = clone $current;
        $query_end = clone $current;

        // Get the first day of next month
        $query_end->modify('first day of next month');

        // Get the first day of this month
        $query_start->modify('first day of this month');


        global $wpdb;
        $posts = $wpdb->get_results(
            "SELECT

                start_meta.meta_value AS event_start,
                end_meta.meta_value AS event_end,
                " . $wpdb->prefix . "posts.*

            FROM `" . $wpdb->prefix . "posts`

            LEFT JOIN `" . $wpdb->prefix . "postmeta` start_meta
                ON `" . $wpdb->prefix . "posts`.`ID` = `start_meta`.`post_id`
                    AND start_meta.meta_key = 'start_date'

            LEFT JOIN `" . $wpdb->prefix . "postmeta` end_meta
                ON `" . $wpdb->prefix . "posts`.`ID` = `end_meta`.`post_id`
                    AND end_meta.meta_key = 'end_date'

            WHERE

                post_status = 'publish' AND post_type = 'event'

                AND

                (
                    (
                        start_meta.meta_value < " . $query_end->format('Ymd') ."
                        AND
                        start_meta.meta_value >= " . $query_start->format('Ymd') ."
                    )
                    OR
                    (
                        start_meta.meta_value < " . $query_start->format('Ymd') . "
                        AND
                        end_meta.meta_value>= " . $query_start->format('Ymd') . "
                    )
                )

            GROUP BY `" . $wpdb->prefix . "posts`.`ID`"
        );

        // Create a DatePeriod object for this calendars date range
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($start, $interval, $end);

        $data = array();

        // Loop through and generate day data
        foreach ($daterange as $date) {
            // Look for events
            $events = array();

            /**
             * Only include event information if the current date is in the
             * current month. This prevents days from the previous and next
             * month from showing events.
             */
            if ($date->format('m') == $current->format('m')) {
                // Any posts for this date?
                foreach ($posts as $p) {
                    $start = get_post_meta($p->ID, 'start_date', true);
                    $end = get_post_meta($p->ID, 'end_date', true);

                    if ($start == $date->format('Ymd')
                        || $end == $date->format('Ymd')
                        || ($date->format('Ymd') <= $end
                        && $date->format('Ymd') >= $start)
                    ) {
                        $events[] = array(
                            'id' => $p->ID,
                            'permalink' => get_the_permalink($p->ID)
                        );
                    }
                }
            }

            // Determine which class to use
            if ($now->format('Y-m-d') == $date->format('Y-m-d')) {
                $class = $this->c('to');
            } elseif ($current->format('Y-m') == $date->format('Y-m')) {
                // Current month
                $class = $this->c('cu');
            } elseif ($current > $date) {
                $class = $this->c('pa');
            } else {
                $class = $this->c('fu');
            }

            // Build the data array
            $link = get_post_type_archive_link('event');
            $link.= $date->format('Y/m/d/');

            $class_events = (count($events) > 0 ? ' ' . $this->c('ev') : '');

            $data[] = array(
                'class' => $class . $class_events,
                'date' => $date->format($this->options['format_day']),
                'events' => $events,
                'link' => $link
            );
        }

        return $data;
    }

    // -------------------------------------------------------------------------

    /**
     * Returns the data require for AJAX calls
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return array
     */
    public function getAjax()
    {
        $current = new DateTime($this->year . '-' . $this->month . '-01');

        return array(
            'year' => $this->year,
            'month' => $this->month,
            'days' => $this->getDays(),
            'current' => $current->format($this->options['current_month'])
        );
    }

    // -------------------------------------------------------------------------

    /**
     * Returns a class name
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @param string $index Class name key
     * @return string
     */
    private function c($index)
    {
        $return = array();

        $classes = explode(',', $index);

        foreach ($classes as $class) {
            if (isset($this->class[trim($class)])) {
                $return[] = $this->options['class_prefix']
                    . $this->class[$class];
            }
        }

        return implode(' ', $return);
    }

    // -------------------------------------------------------------------------
}
