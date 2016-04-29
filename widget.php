<?php

/**
 * Add calendar widget
 */
class CGIT_Events_Calendar_Widget extends WP_Widget {

    /**
     * Register widget
     */
    function __construct() {
        parent::__construct('cgit_events_calendar_widget', __( 'Events Calendar', 'text_domain' ));
    }

    /**
     * Display widget content
     */
    public function widget($args, $instance) {

        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        echo cgit_wp_events_calendar();
        echo $args['after_widget'];

    }

    /**
     * Display widget settings
     */
    public function form($instance) {

        $title = !empty( $instance['title'] ) ? $instance['title'] : __('Events Calendar', 'text_domain');
        $id = $this->get_field_id('title');
        $name = $this->get_field_name('title');
        $label = __('Title:');
        $value = esc_attr($title);

        echo "<p><label for='$id'>$label</label><input type='text' name='$name' id='$id' class='widefat' value='$value' /></p>";

    }

    /**
     * Save widget settings
     */
    public function update($new_instance, $old_instance) {

        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;

    }

}

/**
 * Register widget
 */
function cgit_wp_events_register_widget() {
    register_widget('CGIT_Events_Calendar_Widget');
}

add_action('widgets_init', 'cgit_wp_events_register_widget');
