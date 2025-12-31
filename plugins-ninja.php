<?php
/**
 * Plugin Name: Plugins Ninja
 * Description: Conecte seu site ao ecossistema Plugins Ninja para gerenciamento remoto e sincronização.
 * Version: 0.0.2
 * Author: Paulo Marreira
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PLUGINS_NINJA_PATH', plugin_dir_path(__FILE__));
define('PLUGINS_NINJA_URL', plugin_dir_url(__FILE__));

require_once PLUGINS_NINJA_PATH . 'includes/class-admin-page.php';
require_once PLUGINS_NINJA_PATH . 'includes/class-api-endpoints.php';
require_once PLUGINS_NINJA_PATH . 'includes/class-data-sync.php';
require_once PLUGINS_NINJA_PATH . 'includes/class-installer.php';

class Plugins_Ninja
{
    public function __construct()
    {
        new Plugins_Ninja_Admin_Page();
        new Plugins_Ninja_API_Endpoints();
        new Plugins_Ninja_Data_Sync();
    }
}

new Plugins_Ninja();
