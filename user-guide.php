<h3>Events</h3>

<p>Events can be added and edited in the same way as posts or pages. You can view and edit all the events on the site using the &ldquo;Events&rdquo; menu on the left side of the admin panel. In addition to the default fields, events have fields for their time, date, and location.<?php

    $supports = array();

    if (get_option('cgit_wp_events_post_type_support_category')) {
        $supports[] = 'categories';
    }

    if (get_option('cgit_wp_events_post_type_support_tag')) {
        $supports[] = 'tags';
    }

    if ($supports) {
        echo ' You can also use ' . implode(' and ', $supports) . ' to organise your events.';
    }

?></p>

<figure>
    <img src="<?php echo plugin_dir_url(__FILE__); ?>images/fields.png" alt="" />
    <figcaption>Events have more fields than posts or pages. The &ldquo;Where&rdquo; fields let you add information about the location of the event. The &ldquo;When&rdquo; fields let you add information about the time, date, and duration of the event.</figcaption>
</figure>
