<?php

/**
 * Static classes for global variable access
 */
class cgit_wp_events {
    private const FILTER = 'cgit_wp_events_options';
    private const PREFIX = 'cgit_wp_events_post_type_support_';

    /**
     * Generic options and their default values
     *
     * @var array
     */
    private static $args = [
        'category' => true,
        'editor' => true,
        'excerpt' => true,
        'author' => true,
        'thumbnail' => true,
        'comments' => false,
        'page-attributes' => false,
    ];

    /**
     * Option labels
     *
     * @var array
     */
    private static $labels = [
        'category' => 'Categories',
        'editor' => 'Content editor',
        'excerpt' => 'Excerpt',
        'author' => 'Author',
        'thumbnail' => 'Thumbnail',
        'comments' => 'Comments',
        'page-attributes' => 'Page attributes',
    ];

    /**
     * Return generic options and values
     *
     * If a filter exists, use that to edit the option values. Otherwise, load
     * the option values from the database.
     *
     * @return array
     */
    private static function args() {
        $args = self::$args;

        if (self::has_filter()) {
            $args = apply_filters(self::FILTER, $args);
        } else {
            foreach ($args as $key => $value) {
                $args[$key] = get_option(self::PREFIX . $key, $value);
            }
        }

        $args = array_intersect_key($args, self::$args);
        $args = array_merge(self::$args, $args);

        return $args;
    }

    /**
     * Return database option values
     *
     * @return array
     */
    public static function get_options() {
        $options = [];

        foreach (self::args() as $key => $value) {
            $options[self::PREFIX . $key] = $value ? '1' : '';
        }

        return $options;
    }

    /**
     * Return post type supports parameters
     *
     * @return array
     */
    public static function get_post_type_supports_args() {
        $args = array_filter(self::args());
        $args = array_diff_key($args, ['category' => null]);
        $args = array_keys($args);

        $args = array_merge([
            'title',
            'revisions',
        ], $args);

        return $args;
    }

    /**
     * Return label for an option key
     *
     * @return string|null
     */
    public static function get_label($key) {
        $keys = (array) $key;

        if (strpos($key, self::PREFIX) === 0) {
            $keys[] = substr($key, strlen(self::PREFIX));
        } else {
            $keys[] = self::PREFIX . $key;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, self::$labels)) {
                return self::$labels[$key];
            }
        }

        return null;
    }

    /**
     * Option filter has been set?
     *
     * @return bool
     */
    public static function has_filter() {
        return has_filter(self::FILTER);
    }

    /**
     * Category taxonomy has been enabled?
     *
     * @return bool
     */
    public static function has_category_taxonomy() {
        return (bool) (self::args()['category'] ?? false);
    }
}


/**
 * Register plugin settings
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_register_settings()
{
    foreach (cgit_wp_events::get_options() as $key => $value) {
        register_setting('cgit-events', $key);
    }
}


/**
 * Register plugin settings menu item
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_add_settings_page()
{
    // Add page
    add_submenu_page(
        'options-general.php',
        'Event Settings',
        'Event Settings',
        'manage_options',
        'cgit-events',
        'cgit_wp_events_render_settings_page'
    );

    // Register settings
    add_action('admin_init', 'cgit_wp_events_register_settings');

}
add_action('admin_menu', 'cgit_wp_events_add_settings_page');


/**
 * Render settings page content
 *
 * @author Castlgate IT <info@castlegateit.co.uk>
 * @author Andy Reading
 *
 * @return void
 */
function cgit_wp_events_render_settings_page() {
    ?>

    <div class="wrap">
        <h2>Events Settings</h2>

        <?php

        if (cgit_wp_events::has_filter()) {
            ?>
            <p><i>Events options have been set via a filter and cannot be edited here.</i></p>
            <?php
        } else {
            ?>
            <form action="options.php" method="post">
                <?php settings_fields('cgit-events') ?>

                <h3>Interface</h3>

                <table class="form-table">
                    <tr>
                        <th>Enable</th>

                        <td>
                            <?php

                            foreach (cgit_wp_events::get_options() as $key => $value) {
                                $label = cgit_wp_events::get_label($key) ?: $key;

                                ?>
                                <p><label><input type="checkbox" name="<?= $key ?>" value="1" <?= $value ? 'checked' : '' ?>> <?= $label ?></label></p>
                                <?php
                            }

                            ?>
                        </td>
                    </tr>
                </table>

                <?php submit_button() ?>
            </form>
            <?php
        }

        ?>
    </div>

    <?php
}
