<?php

class Plugins_Ninja_API_Endpoints
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        // Endpoint to receive Site ID
        register_rest_route('pluginsninja', '/receber-id/', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_receive_id'],
            'permission_callback' => '__return_true', // Assuming public or handled by headers/secrets
        ]);

        // Endpoint for synchronization/commands
        register_rest_route('pluginsninja', '/sincronizar/', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_sync_command'],
            'permission_callback' => function () {
                return current_user_can('install_plugins');
            },
        ]);
    }

    public function handle_receive_id($request)
    {
        $site_id = $request->get_param('site_id');

        if (empty($site_id)) {
            return new WP_Error('missing_id', 'Site ID is required', ['status' => 400]);
        }

        update_option('pluginninja_site_id', sanitize_text_field($site_id));

        return rest_ensure_response([
            'success' => true,
            'message' => 'Site ID saved successfully.',
            'site_id' => $site_id
        ]);
    }

    public function handle_sync_command($request)
    {
        $command = $request->get_param('command'); // install, activate...
        $type = $request->get_param('type'); // plugin, theme
        $slug = $request->get_param('slug');
        $url = $request->get_param('url');

        if (empty($command)) {
            return new WP_Error('missing_command', 'Command is required', ['status' => 400]);
        }

        $installer = new Plugins_Ninja_Installer();

        switch ($command) {
            case 'install':
                $result = $installer->install($type, $slug, $url);
                break;
            // Add other cases like activate, delete if needed
            default:
                return new WP_Error('invalid_command', 'Invalid command', ['status' => 400]);
        }

        return rest_ensure_response($result);
    }
}
