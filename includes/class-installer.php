<?php

class Plugins_Ninja_Installer
{

    public function __construct()
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
    }

    public function install($type, $slug, $url = null)
    {
        // If no URL, try to find it in repository
        if (empty($url) && !empty($slug)) {
            $url = $this->get_repo_url($type, $slug);
            if (is_wp_error($url)) {
                return $url;
            }
        }

        if (empty($url)) {
            return new WP_Error('missing_source', 'No URL or valid Slug provided.');
        }

        $skin = new WP_Ajax_Upgrader_Skin();

        if ('theme' === $type) {
            $upgrader = new Theme_Upgrader($skin);
        } else {
            $upgrader = new Plugin_Upgrader($skin);
        }

        $result = $upgrader->install($url);

        if (is_wp_error($result)) {
            return $result;
        }

        if (!$result) {
            return new WP_Error('install_failed', 'Installation failed.');
        }

        // Try to activate if it's a plugin
        if ('plugin' === $type) {
            $plugin_file = $this->get_plugin_file_by_slug($slug);
            if ($plugin_file) {
                activate_plugin($plugin_file);
                return ['success' => true, 'message' => 'Installed and activated', 'file' => $plugin_file];
            }
        }

        return ['success' => true, 'message' => 'Installed successfully'];
    }

    private function get_repo_url($type, $slug)
    {
        if ('plugin' === $type) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            $api = plugins_api('plugin_information', ['slug' => $slug, 'fields' => ['sections' => false]]);
            if (is_wp_error($api)) {
                return $api;
            }
            return $api->download_link;
        }
        // Theme logic similar if needed, for now focusing on plugins or direct generic URL
        return new WP_Error('repo_not_supported', 'Repository search for themes not implemented yet.');
    }

    private function get_plugin_file_by_slug($slug)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        foreach ($plugins as $file => $data) {
            if (strpos($file, $slug) !== false) {
                return $file;
            }
        }
        return false;
    }
}
