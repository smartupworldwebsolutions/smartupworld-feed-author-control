<?php
/*
Plugin Name: SmartUpWorld Feed & Author Control
Plugin URI: https://smartupworld.com/smartupworld-feed-control/
Description: Disable selected RSS feeds and author archives with options in the admin panel, plus a status page.
Version: 1.2
Author: SmartUpWorld
Author URI: https://smartupworld.com
License: GPL2
*/

// ✅ Default Options
if (!function_exists('smartupworld_feed_control_defaults')) {
    function smartupworld_feed_control_defaults() {
        return [
            'rdf' => 1,
            'rss' => 1,
            'rss2' => 1,
            'atom' => 1,
            'rss2_comments' => 1,
            'atom_comments' => 1,
            'disable_authors' => 1
        ];
    }
}

// ✅ Register Settings
if (!function_exists('smartupworld_feed_control_register_settings')) {
    function smartupworld_feed_control_register_settings() {
        register_setting(
            'smartupworld_feed_control_group',
            'smartupworld_feed_control_options',
            'smartupworld_feed_control_validate'
        );
    }
    add_action('admin_init', 'smartupworld_feed_control_register_settings');
}

// ✅ Add Settings & Status Menus
if (!function_exists('smartupworld_feed_control_menu')) {
    function smartupworld_feed_control_menu() {
        add_options_page(
            'Feed Control Settings',
            'Feed Control',
            'manage_options',
            'smartupworld-feed-control',
            'smartupworld_feed_control_settings_page'
        );
        add_submenu_page(
            'smartupworld-feed-control',
            'Feed Status',
            'Feed Status',
            'manage_options',
            'smartupworld-feed-status',
            'smartupworld_feed_control_status_page'
        );
    }
    add_action('admin_menu', 'smartupworld_feed_control_menu');
}

// ✅ Validate Settings
if (!function_exists('smartupworld_feed_control_validate')) {
    function smartupworld_feed_control_validate($input) {
        $defaults = smartupworld_feed_control_defaults();
        foreach ($defaults as $key => $value) {
            $input[$key] = isset($input[$key]) ? 1 : 0;
        }
        return $input;
    }
}

// ✅ Disable Feeds
if (!function_exists('smartupworld_disable_feeds')) {
    function smartupworld_disable_feeds() {
        $options = get_option('smartupworld_feed_control_options', smartupworld_feed_control_defaults());

        if ($options['rdf']) add_action('do_feed_rdf', 'smartupworld_disable_all_feeds', 1);
        if ($options['rss']) add_action('do_feed_rss', 'smartupworld_disable_all_feeds', 1);
        if ($options['rss2']) add_action('do_feed_rss2', 'smartupworld_disable_all_feeds', 1);
        if ($options['atom']) add_action('do_feed_atom', 'smartupworld_disable_all_feeds', 1);
        if ($options['rss2_comments']) add_action('do_feed_rss2_comments', 'smartupworld_disable_all_feeds', 1);
        if ($options['atom_comments']) add_action('do_feed_atom_comments', 'smartupworld_disable_all_feeds', 1);

        if ($options['disable_authors']) {
            add_action('template_redirect', 'smartupworld_disable_author_archives');
            add_action('wp_head', 'smartupworld_add_noindex_to_author_archives');
        }
    }
    add_action('init', 'smartupworld_disable_feeds');
}

// ✅ Redirect Feed Requests
if (!function_exists('smartupworld_disable_all_feeds')) {
    function smartupworld_disable_all_feeds() {
        wp_redirect(home_url(), 301);
        exit;
    }
}

// ✅ Add Noindex to Author Archives
if (!function_exists('smartupworld_add_noindex_to_author_archives')) {
    function smartupworld_add_noindex_to_author_archives() {
        if (is_author()) {
            echo '<meta name="robots" content="noindex, follow" />' . "\n";
        }
    }
}

// ✅ Redirect Author Archives
if (!function_exists('smartupworld_disable_author_archives')) {
    function smartupworld_disable_author_archives() {
        if (is_author()) {
            wp_redirect(home_url(), 301);
            exit;
        }
    }
}

// ✅ Settings Page
if (!function_exists('smartupworld_feed_control_settings_page')) {
    function smartupworld_feed_control_settings_page() {
        $options = get_option('smartupworld_feed_control_options', smartupworld_feed_control_defaults()); ?>
        <div class="wrap">
            <h1>SmartUpWorld Feed Control</h1>
            <form method="post" action="options.php">
                <?php settings_fields('smartupworld_feed_control_group'); ?>
                <table class="form-table">
                    <tr><th>Disable Feeds</th><td>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[rdf]" <?php checked($options['rdf'], 1); ?>> RDF Feed</label><br>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[rss]" <?php checked($options['rss'], 1); ?>> RSS Feed</label><br>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[rss2]" <?php checked($options['rss2'], 1); ?>> RSS2 Feed</label><br>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[atom]" <?php checked($options['atom'], 1); ?>> Atom Feed</label><br>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[rss2_comments]" <?php checked($options['rss2_comments'], 1); ?>> RSS2 Comments Feed</label><br>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[atom_comments]" <?php checked($options['atom_comments'], 1); ?>> Atom Comments Feed</label><br>
                    </td></tr>
                    <tr><th>Author Archives</th><td>
                        <label><input type="checkbox" name="smartupworld_feed_control_options[disable_authors]" <?php checked($options['disable_authors'], 1); ?>> Disable Author Archives</label>
                    </td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <hr>
            <p style="margin-top:20px; font-size:14px; color:#555;">
                Plugin developed by <a href="https://smartupworld.com" target="_blank">SmartUpWorld</a>.
            </p>
        </div>
    <?php }
}

// ✅ Status Page
if (!function_exists('smartupworld_feed_control_status_page')) {
    function smartupworld_feed_control_status_page() {
        $options = get_option('smartupworld_feed_control_options', smartupworld_feed_control_defaults());
        ?>
        <div class="wrap">
            <h1>Feed & Archive Status</h1>
            <table class="widefat fixed">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>RDF Feed</td><td><?php echo $options['rdf'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                    <tr><td>RSS Feed</td><td><?php echo $options['rss'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                    <tr><td>RSS2 Feed</td><td><?php echo $options['rss2'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                    <tr><td>Atom Feed</td><td><?php echo $options['atom'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                    <tr><td>RSS2 Comments Feed</td><td><?php echo $options['rss2_comments'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                    <tr><td>Atom Comments Feed</td><td><?php echo $options['atom_comments'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                    <tr><td>Author Archives</td><td><?php echo $options['disable_authors'] ? '❌ Disabled' : '✅ Enabled'; ?></td></tr>
                </tbody>
            </table>
        </div>
    <?php }
}
