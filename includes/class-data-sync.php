<?php

class Plugins_Ninja_Data_Sync
{

    public function __construct()
    {
        add_action('admin_init', [$this, 'sync_data']);
    }

    public function sync_data()
    {
        $site_id = get_option('pluginninja_site_id');

        if (!$site_id) {
            return;
        }

        // Avoid running on AJAX requests or if we just ran recently (optional, but good practice)
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        // Prepare Data
        $data = $this->collect_data($site_id);

        // Send Data (Fire and Forget)
        $url = 'https://ltd.marreira.site/wp-json/pluginsninja/json/sincronizar/sites/dados/';

        wp_remote_post($url, [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 5,
            'blocking' => false, // Don't wait for response
            'sslverify' => false,
        ]);
    }

    private function collect_data($site_id)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!function_exists('get_theme_updates')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }

        $all_plugins = get_plugins();
        $active_plugins_slugs = get_option('active_plugins', []);
        $plugin_updates = get_plugin_updates();

        $plugins_formatted = [];
        $active_count = 0;
        $inactive_count = 0;
        $update_count_plugins = count($plugin_updates);

        foreach ($all_plugins as $file => $p) {
            $is_active = in_array($file, $active_plugins_slugs);
            if ($is_active) {
                $active_count++;
            } else {
                $inactive_count++;
            }

            $plugins_formatted[] = [
                'name' => $p['Name'],
                'version' => $p['Version'],
                'slug' => dirname($file), // approximated slug
                'active' => $is_active,
                // Logo URL is hard to get efficiently without querying API for each.
            ];
        }

        $all_themes = wp_get_themes();
        $current_theme = wp_get_theme();
        $theme_updates = get_theme_updates();

        $themes_formatted = [];
        foreach ($all_themes as $slug => $theme) {
            $themes_formatted[] = [
                'name' => $theme->get('Name'),
                'version' => $theme->get('Version'),
                'active' => $slug === $current_theme->get_stylesheet(),
                'screenshot' => $theme->get_screenshot(),
            ];
        }

        $server_info = [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php' => phpversion(),
            'mysql' => $GLOBALS['wpdb']->db_version(),
        ];

        return [
            'site_id' => $site_id,
            'url' => get_site_url(),
            'ssl' => is_ssl(),
            'wp_version' => get_bloginfo('version'),
            'php_version' => phpversion(),
            'server_type' => $server_info['software'],
            'plugins_stats' => [
                'total' => count($all_plugins),
                'active' => $active_count,
                'inactive' => $inactive_count,
                'updates' => $update_count_plugins
            ],
            'themes_stats' => [
                'total' => count($all_themes),
                'updates' => count($theme_updates) // Approximate
            ],
            'current_theme' => [
                'name' => $current_theme->get('Name'),
                'url' => $current_theme->get_screenshot()
            ],
            'plugins_list' => $plugins_formatted,
            'themes_list' => $themes_formatted,
            'last_sync' => current_time('mysql')
        ];
    }
}
