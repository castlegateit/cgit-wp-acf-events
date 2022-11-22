# Castlegate IT WP Events #

An events management plugin for WordPress. Installing and activating the plugin will create an event post type, which is available to users in the WordPress admin panel. If either [Advanced Custom Fields](http://www.advancedcustomfields.com/) or [Human Made Custom Meta Boxes plugin](https://github.com/humanmade/Custom-Meta-Boxes) is installed, this plugin will provide relevant fields for event dates, times, locations, and prices. It also defines category and tag taxonomies.

## Events ##

The plugin creates an `event` post type, which is managed via the WordPress admin panel. By default, the main archive for events can be viewed at `/?post_type=event`, but this will depend on your permalink settings. Events can have categories and tags, depending on the interface settings (see below). Custom fields for the event details use Custom Meta Boxes.

## Interface settings ##

The Events Settings menu, which appears within the main Settings menu, lets you enable or disable various standard WordPress fields for the `event` post type, including the content editor, excerpt, featured image, etc.

## Filters ##

You can use filters to amend the plugin output:

*   `cgit_wp_events_format_day`: [format](http://php.net/manual/en/function.date.php) for days in the calendar
*   `cgit_wp_events_class_prefix`: class name prefix for the events calendar
*   `cgit_wp_events_format_current_month`: [format](http://php.net/manual/en/function.date.php) for the current month in the calendar
*   `cgit_wp_events_format_next_year`: next year calendar text
*   `cgit_wp_events_format_prev_year`: previous year calendar text
*   `cgit_wp_events_format_next_month`: next month calendar text
*   `cgit_wp_events_format_prev_month`: previous month calendar text

## Functions ##

### Calendar ###

The plugin provides the `cgit_wp_events_calendar()` function to return the full HTML events calendar. The necessary JavaScript will be enqueued automatically for the next and previous links.

### Archives ###

To display a list of links to archive pages, you may use the `cgit_wp_events_archive()` function. The function returns a formatted array containing all the information required to generate archive lists:

    array(1) {
        [2016]=> array(2) {
            ["03"]=> array(3) {
                ["date"]=> object(DateTime)
                ["link"]=> string(15) "/event/2016/03/"
                ["count"]=> int(1)
            }
            ["04"]=> array(3) {
                ["date"]=> object(DateTime)
                ["link"]=> string(15) "/event/2016/04/"
                ["count"]=> int(1)
            }
        }
    }

Example list generation:

    <?php foreach (cgit_wp_events_archive() as $years => $months) : ?>
        <h3><?=$years?></h3>
        <ul>
            <?php foreach ($months as $month) : ?>
                <li>
                    <a href="<?=$month['link']?>">
                        <?=$month['date']->format('F Y')?>
                        (<?=$month['count']?>)
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    <?php endforeach ?>

Note: Event counts include the number of events running within a given month, therefore you cannot total this count to display a count per year.

## Widget ##

If your theme supports widgets, the events calendar can also be added as a widget.

## UK dates ##

If the [CMB UK date field plugin](https://github.com/castlegateit/cgit-wp-cmb-ukdate) plugin is installed and you are using the Custom Meta Boxes plugin, the start and end dates will use that field type. If not, they will use the standard Unix date field.

## License

Released under the [MIT License](https://opensource.org/licenses/MIT). See [LICENSE](LICENSE) for details.
