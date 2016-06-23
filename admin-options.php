<?php

/**
 * Static classes for global variable access
 */
class cgit_wp_events {

    public static $options = array(
        'cgit_wp_events_post_type_support_category' => '1',
        'cgit_wp_events_post_type_support_tag' => '',
        'cgit_wp_events_post_type_support_editor' => '1',
        'cgit_wp_events_post_type_support_excerpt' => '1',
        'cgit_wp_events_post_type_support_author' => '',
        'cgit_wp_events_post_type_support_thumbnail' => '',
        'cgit_wp_events_post_type_support_comments' => '',
        'cgit_wp_events_post_type_support_page-attributes' => ''
    );
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
    foreach (cgit_wp_events::$options as $option => $v) {
        register_setting('cgit-events', $option);
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

    <form action="options.php" method="post">

        <?php settings_fields('cgit-events'); ?>

        <h3>Interface</h3>

        <table class="form-table">

            <tr>
                <th rowspan="10">
                    Enable
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_category" value="1"<?php echo get_option('cgit_wp_events_post_type_support_category') ? ' checked="checked"' : ''; ?> />
                        Categories
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_tag" value="1"<?php echo get_option('cgit_wp_events_post_type_support_tag') ? ' checked="checked"' : ''; ?> />
                        Tags
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_editor" value="1"<?php echo get_option('cgit_wp_events_post_type_support_editor') ? ' checked="checked"' : ''; ?> />
                        Content editor
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_excerpt" value="1"<?php echo get_option('cgit_wp_events_post_type_support_excerpt') ? ' checked="checked"' : ''; ?> />
                        Excerpt
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_author" value="1"<?php echo get_option('cgit_wp_events_post_type_support_author') ? ' checked="checked"' : ''; ?> />
                        Author
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_thumbnail" value="1"<?php echo get_option('cgit_wp_events_post_type_support_thumbnail') ? ' checked="checked"' : ''; ?> />
                        Thumbnail
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_comments" value="1"<?php echo get_option('cgit_wp_events_post_type_support_comments') ? ' checked="checked"' : ''; ?> />
                        Comments
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>
                        <input type="checkbox" name="cgit_wp_events_post_type_support_page-attributes" value="1"<?php echo get_option('cgit_wp_events_post_type_support_page-attributes') ? ' checked="checked"' : ''; ?> />
                        Page attributes
                    </label>
                </td>
            </tr>

        </table>

        <?php submit_button(); ?>

    </form>

</div>

<?php
}
