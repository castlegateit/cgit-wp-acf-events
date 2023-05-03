<?php

/**
 * Add custom fields using Advanced Custom Fields
 *
 * Advanced Custom Fields uses functions to add fields, so this file should
 * return if the Advanced Custom Fields plugin is not installed.
 */
add_action(
    'acf/init',
    function () {
        // Generate list of times
        $start = mktime(0, 0, 0);
        $times = array();

        for ($i = 0; $i < 86400; $i += 1800) {
            $time = date('H:i', $start + $i);
            $times[$time] = $time;
        }

        // Default map location
        $location = apply_filters('cgit_wp_acf_events_default_map_location', [0, 0]);
        $center_lat = $location[0];
        $center_lng = $location[1];

        // Add date and time fields
        $date_time_fields = array(
            'key' => 'cgit_wp_events_when',
            'title' => 'When',
            'fields' => array(
                array(
                    'key' => 'start_date',
                    'name' => 'start_date',
                    'label' => 'Start date',
                    'type' => 'date_picker',
                    'required' => true,
                ),
                array(
                    'key' => 'start_time',
                    'name' => 'start_time',
                    'label' => 'Start time',
                    'type' => 'select',
                    'choices' => $times,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'all_day',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'end_date',
                    'name' => 'end_date',
                    'label' => 'End date',
                    'type' => 'date_picker',
                ),
                array(
                    'key' => 'end_time',
                    'name' => 'end_time',
                    'label' => 'End time',
                    'type' => 'select',
                    'choices' => $times,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'all_day',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'all_day',
                    'name' => 'all_day',
                    'label' => 'All day event?',
                    'type' => 'true_false',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => CGIT_EVENTS_POST_TYPE,
                    ),
                ),
            ),
            'position' => 'side',
        );

        $date_time_fields = apply_filters('cgit_wp_acf_fields_date_time', $date_time_fields);
        acf_add_local_field_group($date_time_fields);

        // Add location fields
        $location_fields = array(
            'key' => 'cgit_wp_events_where',
            'title' => 'Where',
            'fields' => array(
                array(
                    'key' => 'location_name',
                    'name' => 'location_name',
                    'label' => 'Location name',
                    'type' => 'text',
                ),
                array(
                    'key' => 'location_address',
                    'name' => 'location_address',
                    'label' => 'Address',
                    'type' => 'textarea',
                ),
                array(
                    'key' => 'location',
                    'name' => 'location',
                    'label' => 'Location',
                    'type' => 'google_map',
                    'center_lat' => $center_lat,
                    'center_lng' => $center_lng,
                ),
                array(
                    'key' => 'price',
                    'name' => 'price',
                    'label' => 'Price',
                    'type' => 'number',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => CGIT_EVENTS_POST_TYPE,
                    ),
                ),
            ),
        );

        $location_fields = apply_filters('cgit_wp_acf_fields_location', $location_fields);
        acf_add_local_field_group($location_fields);
    }
);
