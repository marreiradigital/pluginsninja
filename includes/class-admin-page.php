<?php

class Plugins_Ninja_Admin_Page
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_notices', [$this, 'render_admin_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function add_menu_page()
    {
        add_menu_page(
            'Plugins Ninja',
            'Plugins Ninja',
            'manage_options',
            'plugins-ninja',
            [$this, 'render_page'],
            'dashicons-shield',
            2
        );
    }

    public function enqueue_styles( $hook ) {
        // Enqueue if we are on the plugin page OR if we are not connected (for the global notice)
        if ( 'toplevel_page_plugins-ninja' === $hook || ! get_option( 'pluginninja_site_id' ) ) {
            wp_enqueue_style( 'plugins-ninja-admin', PLUGINS_NINJA_URL . 'assets/css/admin.css', [], '1.0.1' );
        }
    }

    public function get_auth_url()
    {
        $admin_url = admin_url('authorize-application.php');
        return add_query_arg(
            [
                'app_name' => 'PluginsNinja',
                'success_url' => 'https://ltd.marreira.site/connect-site/',
                'reject_url' => 'https://marreira.site/connect-site/reject/'
            ],
            $admin_url
        );
    }

    public function render_admin_notice()
    {
        // Only show if not connected and user has ability to manage options
        if (get_option('pluginninja_site_id') || !current_user_can('manage_options')) {
            return;
        }

        $auth_url = $this->get_auth_url();
        ?>
        <div class="notice notice-info is-dismissible plugins-ninja-notice-dark">
            <p><strong>Bem vindo a PluginsNinja</strong></p>
            <p>Estamos quase lá! Para aproveitar todos os benefícios da PluginsNinja, conecte seu site à sua conta.</p>
            <p>
                <a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary">Conectar site</a>
            </p>
        </div>
        <?php
    }

    public function render_page()
    {
        $site_id = get_option('pluginninja_site_id');
        $auth_url = $this->get_auth_url();

        ?>
        <div class="wrap">
            <h1>Plugins Ninja</h1>

            <?php if ($site_id): ?>
                <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-yes-alt"
                            style="color: #10b981; font-size: 28px; width: 28px; height: 28px; vertical-align: middle;"></span>
                        Site Conectado
                    </h2>
                    <p>Seu site está conectado com sucesso ao ecossistema PluginsNinja.</p>
                    <p><strong>Site ID:</strong> <code><?php echo esc_html($site_id); ?></code></p>
                    <hr>
                    <p class="description">A sincronização de dados e gerenciamento remoto estão ativos.</p>
                </div>
            <?php else: ?>
                <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                    <h2>Conecte seu site</h2>
                    <p>Para começar a usar o Plugins Ninja, você precisa autorizar a conexão.</p>
                    <p>
                        <a href="<?php echo esc_url($auth_url); ?>" class="button button-primary button-hero">
                            Conectar Site
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
