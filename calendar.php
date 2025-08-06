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

    /**
     * Show a full list of events for each day?
     *
     * @var bool
     */
    public bool $full = false;

    /**
     * Earliest event date
     *
     * @var DateTime
     */
    private DateTime $minDate;

    /**
     * Latest event date
     *
     * @var DateTime
     */
    private DateTime $maxDate;

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
        $this->setMinMaxDate();

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

    /**
     * Set minimum and maximum dates
     *
     * @return void
     */
    private function setMinMaxDate(): void
    {
        global $wpdb;

        $this->minDate = new DateTime();
        $this->maxDate = new DateTime();

        $min = $wpdb->get_var($wpdb->prepare(
            'SELECT MIN(meta_value) AS start_date
                FROM %i AS m
                JOIN %i AS p
                ON m.post_id = p.ID
                WHERE m.meta_key = "start_date"
                AND p.post_type = "event"
                AND p.post_status = "publish"',
            'wp_postmeta',
            'wp_posts'
        ));

        $max = $wpdb->get_var($wpdb->prepare(
            'SELECT MAX(meta_value) AS start_date
                FROM %i AS m
                JOIN %i AS p
                ON m.post_id = p.ID
                WHERE m.meta_key = "end_date"
                AND p.post_type = "event"
                AND p.post_status = "publish"',
            'wp_postmeta',
            'wp_posts'
        ));

        if (is_string($min) && strlen($min) === 8) {
            $this->minDate = DateTime::createFromFormat('Ymd', $min);
        }

        if (is_string($max) && strlen($max) === 8) {
            $this->maxDate = DateTime::createFromFormat('Ymd', $max);
        }
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
        $out .= ' data-cgit-events-min-year="' . esc_attr($this->minDate->format('Y')) . '"';
        $out .= ' data-cgit-events-min-month="' . esc_attr($this->minDate->format('m')) . '"';
        $out .= ' data-cgit-events-max-year="' . esc_attr($this->maxDate->format('Y')) . '"';
        $out .= ' data-cgit-events-max-month="' . esc_attr($this->maxDate->format('m')) . '"';
        $out.= " data-cgit-events-year=\"" . $this->year . "\"";
        $out.= " data-cgit-events-month=\"" . $this->month . "\">\n";
        $out.= $this->header();

        if ($this->full) {
            $out .= $this->getFullTableBody();
        } else {
            $out .= $this->days();
        }

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

        $prev_date = clone $current;
        $next_date = clone $current;

        $prev_date->modify('-1 month');
        $next_date->modify('+1 month');

        $out = "<thead>\n";
        $out.= "<tr>\n";

        // Previous year
        $out.= "<th class=\"" . $this->c('co,py') . "\">";

        if ($prev_year >= ((int) $this->minDate->format('Y'))) {
            $prev_year_link = http_build_query(array(
                'cgit-year' => $prev_year,
                'cgit-month' => $this->month
            ));

            $out.= "<a href=\"?" . htmlentities($prev_year_link) . "\"><span>";
            $out.= $this->options['format_prev_year'] . "</span></a>";
        }

        $out.= "</th>\n";

        // Previous month
        $out.= "<th class=\"" . $this->c('co,pm') . "\">";

        if ($prev_date >= $this->minDate) {
            $prev_month_link = http_build_query(array(
                'cgit-year' => $this->year,
                'cgit-month' => $prev_month
            ));

            $out.= "<a href=\"?" . htmlentities($prev_month_link) . "\"><span>";
            $out.= $this->options['format_prev_month'] . "</span></a>";
        }

        $out.= "</th>\n";

        // Current month
        $link = apply_filters(
            'cgit_wp_acf_events_calendar_current_month_link',
            get_post_type_archive_link('event').$current->format('Y/m')
        );
        $out.= "<th colspan=\"3\" class=\"" . $this->c('cu') . "\">";
            $out.= "<a href=\"";
            $out.= $link;
            $out.= "\"><span>";
            $out.= $current->format($this->options['current_month']);
            $out.= "</span></a>";
        $out.= "</th>\n";

        // Next month
        $out.= "<th class=\"" . $this->c('co,nm') . "\">";

        if ($next_date <= $this->maxDate) {
            $next_month_link = http_build_query(array(
                'cgit-year' => $this->year,
                'cgit-month' => $next_month
            ));

            $out.= "<a href=\"?" . htmlentities($next_month_link) . "\"><span>";
            $out.= $this->options['format_next_month'] . "</span></a>";
        }

        $out.= "</th>\n";

        // Next year
        $out.= "<th class=\"" . $this->c('co,ny') . "\">";

        if ($next_year <= ((int) $this->maxDate->format('Y'))) {
            $next_year_link = http_build_query(array(
                'cgit-year' => $next_year,
                'cgit-month' => $this->month
            ));

            $out.= "<a href=\"?" . htmlentities($next_year_link) . "\"><span>";
            $out.= $this->options['format_next_year'] . "</span></a>";
        }

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

        // Select
        $select = "SELECT
            start_meta.meta_value AS event_start,
            end_meta.meta_value AS event_end,
            " . $wpdb->prefix . "posts.* ";

        // Filter select
        $select = apply_filters(
            'cgit_wp_acf_events_calendar_sql_select',
            $select
        );

        // From
        $from = "FROM `" . $wpdb->prefix . "posts` ";

        // Filter from
        $from = apply_filters(
            'cgit_wp_acf_events_calendar_sql_from',
            $from
        );

        // Join
        $join = "LEFT JOIN `" . $wpdb->prefix . "postmeta` start_meta
            ON `" . $wpdb->prefix . "posts`.`ID` = `start_meta`.`post_id`
                AND start_meta.meta_key = 'start_date'

            LEFT JOIN `" . $wpdb->prefix . "postmeta` end_meta
                ON `" . $wpdb->prefix . "posts`.`ID` = `end_meta`.`post_id`
                    AND end_meta.meta_key = 'end_date' ";

        // Filter join
        $join = apply_filters(
            'cgit_wp_acf_events_calendar_sql_join',
            $join
        );

        // Where
        $where = "post_status = 'publish' AND post_type = 'event'
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
            )";

        // Filter where
        $where = apply_filters(
            'cgit_wp_acf_events_calendar_sql_where',
            $where
        );

        // Group
        $group = "GROUP BY `" . $wpdb->prefix . "posts`.`ID`";

        // Filter group
        $group = apply_filters(
            'cgit_wp_acf_events_calendar_sql_group',
            $group
        );

        $posts = $wpdb->get_results($select.$from.$join.' WHERE '.$where.$group);

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
                            'permalink' => apply_filters(
                                'cgit_wp_acf_events_calendar_event_link',
                                get_the_permalink($p->ID)
                            )
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
            $link = apply_filters('cgit_wp_acf_events_calendar_day_link', $link);

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

    /**
     * Return tbody for the full calendar showing all events
     *
     * @return string
     */
    private function getFullTableBody(): string
    {
        $days = $this->getDays();
        $weeks = array_chunk($days, 7);
        $prefix = $this->options['class_prefix'] ?? '';

        $max = apply_filters('cgit_wp_events_calendar_max_items', 3);
        $plus_n_text = apply_filters('cgit_wp_events_calendar_plus_n_events', '+ %d events');
        $plus_1_text = apply_filters('cgit_wp_events_calendar_plus_1_event', '+ 1 event');

        $post_type = get_post_type_object(CGIT_EVENTS_POST_TYPE);

        $path = implode('/', [
            $post_type->rewrite['slug'] ?? CGIT_EVENTS_POST_TYPE,
            $this->year,
            $this->month,
        ]);

        ob_start();

        ?>
        <tbody>
            <?php

            foreach ($weeks as $days) {
                ?>
                <tr>
                    <?php

                    foreach ($days as $day) {
                        $events = array_map(function ($event) {
                            $event['id'] = (int) ($event['id'] ?? 0);
                            $event['start_time'] = null;
                            $event['end_time'] = null;

                            if ($event['id']) {
                                $event['start_time'] = get_field('start_time', $event['id']);
                                $event['end_time'] = get_field('end_time', $event['id']);
                            }

                            return $event;
                        }, (array) ($day['events'] ?? []));

                        // Sort events by start time (all day events first)
                        usort($events, function ($event_a, $event_b) {
                            return $event_a['start_time'] <=> $event_b['start_time'];
                        });

                        // If the number of events is above a threshold value,
                        // only output the first n events and show a "more
                        // events" link underneath.
                        $more_title = null;
                        $more_url = null;

                        if (count($events) > $max) {
                            $diff = count($events) - $max;

                            if ($diff === 1) {
                                $more_title = $plus_1_text;
                            } else {
                                $more_title = sprintf($plus_n_text, $diff);
                            }

                            $more_url = home_url('/' . $path . '/' . $day['date'] . '/');
                            $events = array_slice($events, 0, $max);
                        }

                        ?>
                        <td class="<?= esc_attr($day['class'] ?? '') ?>">
                            <div class="<?= esc_attr($prefix . 'day-date') ?>">
                                <?= esc_html($day['date'] ?? '') ?>
                            </div>

                            <?php

                            foreach ($events as $event) {
                                if (!$event['id']) {
                                    continue;
                                }

                                $time = null;

                                if ($event['start_time']) {
                                    $date = DateTime::createFromFormat('H:i', $event['start_time']);
                                    $time = $date->format(apply_filters('cgit_wp_events_calendar_time_format', 'H:i'));
                                }

                                ?>
                                <a href="<?= esc_url(get_permalink($event['id'])) ?>" class="<?= esc_attr($prefix . 'day-event') ?>">
                                    <?php

                                    if ($time) {
                                        ?>
                                        <span class="<?= esc_attr($prefix . 'day-event-time') ?>">
                                            <?= esc_html($time) ?>
                                        </span>
                                        <?php
                                    }

                                    ?>

                                    <span class="<?= esc_attr($prefix . 'day-event-text') ?>">
                                        <?= esc_html(get_the_title($event['id'])) ?>
                                    </span>
                                </a>
                                <?php
                            }

                            if ($more_title && $more_url) {
                                ?>
                                <a href="<?= esc_url($more_url) ?>" class="<?= esc_attr($prefix . 'day-more') ?>">
                                    <?= esc_html($more_title) ?>
                                </a>
                                <?php
                            }

                            ?>
                        </td>
                        <?php
                    }

                    ?>
                </tr>
                <?php
            }

            ?>
        </tbody>
        <?php

        return ob_get_clean();
    }

    // -------------------------------------------------------------------------

    /**
     * Returns the data require for AJAX calls
     *
     * @author Castlgate IT <info@castlegateit.co.uk>
     * @author Andy Reading
     *
     * @return string
     */
    public function getAjax()
    {
        $current = new DateTime($this->year . '-' . $this->month . '-01');

        $data = [
            'year' => $this->year,
            'month' => $this->month,
            'current' => $current->format($this->options['current_month']),
        ];

        if ($this->full) {
            $data['body'] = $this->getFullTableBody();
        } else {
            $data['days'] = $this->getDays();
        }

        return json_encode($data);
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
        $prefix = $this->options['class_prefix'];

        foreach ($classes as $class) {
            if (isset($this->class[trim($class)])) {
                $return[] = $this->options['class_prefix']
                    . $this->class[$class];
            }
        }

        if ($this->full && $index === 'ca') {
            $return[] = $prefix . 'calendar-full';
        }

        return implode(' ', $return);
    }

    // -------------------------------------------------------------------------
}
