<?php

namespace Castlegate\AcfEvents;

class Fields
{
    /**
     * Initialise
     *
     * @return void
     */
    public static function init(): void
    {
        // Register location fields
        add_action(
            'acf/init',
            [get_called_class(), 'fieldsDateTime']
        );

        // Register location fields
        add_action(
            'acf/init',
            [get_called_class(), 'fieldsLocation']
        );
    }

    /**
     * Define date & time fields
     *
     * @return void
     */
    public static function fieldsDateTime(): void
    {
        // Add date and time fields
        $fields = [
            'key' => 'cgit_wp_events_when',
            'title' => 'When',
            'fields' => [
                [
                    'key' => 'start_date',
                    'name' => 'start_date',
                    'label' => 'Start date',
                    'type' => 'date_picker',
                    'required' => true,
                ],
                [
                    'key' => 'start_time',
                    'name' => 'start_time',
                    'label' => 'Start time',
                    'type' => 'select',
                    'choices' => self::getTimes(),
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'all_day',
                                'operator' => '!=',
                                'value' => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'end_date',
                    'name' => 'end_date',
                    'label' => 'End date',
                    'type' => 'date_picker',
                ],
                [
                    'key' => 'end_time',
                    'name' => 'end_time',
                    'label' => 'End time',
                    'type' => 'select',
                    'choices' => self::getTimes(),
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'all_day',
                                'operator' => '!=',
                                'value' => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'all_day',
                    'name' => 'all_day',
                    'label' => 'All day event?',
                    'type' => 'true_false',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => CGIT_EVENTS_POST_TYPE,
                    ],
                ],
            ],
            'position' => 'side',
        ];

        // Filter
        $fields = apply_filters('cgit_wp_acf_fields_date_time', $fields);

        // Register
        acf_add_local_field_group($fields);
    }

    /**
     * Define location fields
     *
     * @return void
     */
    public static function fieldsLocation(): void
    {
        // Get default lat/lng
        $default_lat_lng = self::getDefaultLatLng();
        $lat = $default_lat_lng[0];
        $lng = $default_lat_lng[1];

        // Add location fields
        $fields = [
            'key' => 'cgit_wp_events_where',
            'title' => 'Where',
            'fields' => [
                [
                    'key' => 'location_name',
                    'name' => 'location_name',
                    'label' => 'Location name',
                    'type' => 'text',
                ],
                [
                    'key' => 'location_address',
                    'name' => 'location_address',
                    'label' => 'Address',
                    'type' => 'textarea',
                ],
                [
                    'key' => 'location',
                    'name' => 'location',
                    'label' => 'Location',
                    'type' => 'google_map',
                    'center_lat' => $lat,
                    'center_lng' => $lng,
                ],
                [
                    'key' => 'price',
                    'name' => 'price',
                    'label' => 'Price',
                    'type' => 'number',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => CGIT_EVENTS_POST_TYPE,
                    ],
                ],
            ]
        ];

        // Filter
        $fields = apply_filters('cgit_wp_acf_fields_location', $fields);

        // Register
        acf_add_local_field_group($fields);
    }

    /**
     * Generate time intervals for use within ACF time fields
     *
     * @return array
     */
    public static function getTimes()
    {
        $start = mktime(0, 0, 0);
        $times = [];

        for ($i = 0; $i < 86400; $i += 1800) {
            $time = date('H:i', $start + $i);
            $times[$time] = $time;
        }

        return apply_filters('cgit_wp_acf_time_field_intervals', $times);
    }

    /**
     * Return the default latitude and longitude to center ACF map fields
     * within the admin screens
     *
     * @return array
     */
    public static function getDefaultLatLng(): array
    {
        return apply_filters('cgit_wp_acf_events_default_map_location', [0, 0]);
    }
}