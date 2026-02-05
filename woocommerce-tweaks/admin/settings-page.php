<?php
/**
 * WooCommerce Tweaks Settings Page
 *
 * @package WooCommerceTweaks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the menu item.
 */
function satodu_tweaks_add_admin_menu()
{
    add_submenu_page(
        'woocommerce',
        __('WC Tweaks', 'tweaks-for-woocommerce'),
        __('WC Tweaks', 'tweaks-for-woocommerce'),
        'manage_options',
        'satodu-tweaks-settings',
        'satodu_tweaks_settings_page_html'
    );
}
add_action('admin_menu', 'satodu_tweaks_add_admin_menu');

/**
 * Register settings.
 */
function satodu_tweaks_settings_init()
{
    // Register settings with distinct option groups per tab to prevent overwriting

    // Financial Tab Options
    register_setting('satodu_tweaks_financial', 'satodu_tweaks_pix_discount', array(
        'type' => 'number',
        'sanitize_callback' => 'absint',
        'default' => 2,
    ));

    register_setting('satodu_tweaks_financial', 'satodu_tweaks_priority_gateways', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'asaas-pix, asaas-credit-card',
    ));

    // Statuses Tab Options
    register_setting('satodu_tweaks_statuses', 'satodu_tweaks_custom_statuses', array(
        'type' => 'string',
        'sanitize_callback' => 'satodu_tweaks_sanitize_statuses',
        'default' => "wc-em-separacao|Em Separação\nwc-enviado|Enviado",
    ));

    // Determine active tab - Use wp_unslash before sanitization
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin tab navigation does not require nonce verification for display.
    $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'financial';

    // Register Sections and Fields based on active tab
    if ('financial' === $active_tab) {
        add_settings_section(
            'satodu_tweaks_financial_section',
            __('Financeiro', 'tweaks-for-woocommerce'),
            'satodu_tweaks_financial_section_callback',
            'satodu-tweaks-settings'
        );

        add_settings_field(
            'satodu_tweaks_pix_discount',
            __('Desconto Pix (%)', 'tweaks-for-woocommerce'),
            'satodu_tweaks_pix_discount_callback',
            'satodu-tweaks-settings',
            'satodu_tweaks_financial_section'
        );

        add_settings_field(
            'satodu_tweaks_priority_gateways',
            __('Gateways Prioritários (IDs)', 'tweaks-for-woocommerce'),
            'satodu_tweaks_priority_gateways_callback',
            'satodu-tweaks-settings',
            'satodu_tweaks_financial_section'
        );
    } elseif ('statuses' === $active_tab) {
        add_settings_section(
            'satodu_tweaks_status_section',
            __('Status do Pedido', 'tweaks-for-woocommerce'),
            'satodu_tweaks_status_section_callback',
            'satodu-tweaks-settings'
        );

        add_settings_field(
            'satodu_tweaks_custom_statuses',
            __('Status Personalizados (slug|Label)', 'tweaks-for-woocommerce'),
            'satodu_tweaks_custom_statuses_callback',
            'satodu-tweaks-settings',
            'satodu_tweaks_status_section'
        );
    }
}
add_action('admin_init', 'satodu_tweaks_settings_init');

/**
 * Section Callbacks
 */
function satodu_tweaks_financial_section_callback()
{
    // Escaped output
    echo '<p>' . esc_html__('Configure as opções financeiras e de descontos.', 'tweaks-for-woocommerce') . '</p>';
}

function satodu_tweaks_status_section_callback()
{
    // Escaped output
    echo '<p>' . esc_html__('Gerencie os status personalizados de pedido.', 'tweaks-for-woocommerce') . '</p>';
}

/**
 * Field Callbacks
 */
function satodu_tweaks_pix_discount_callback()
{
    $value = get_option('satodu_tweaks_pix_discount', 2);
    echo '<input type="number" name="satodu_tweaks_pix_discount" value="' . esc_attr($value) . '" min="0" max="100" step="1" />';
    // Escaped output
    echo '<p class="description">' . esc_html__('Percentual de desconto para o método Asaas Pix.', 'tweaks-for-woocommerce') . '</p>';
}

function satodu_tweaks_priority_gateways_callback()
{
    $value = get_option('satodu_tweaks_priority_gateways', 'asaas-pix, asaas-credit-card');
    echo '<input type="text" name="satodu_tweaks_priority_gateways" value="' . esc_attr($value) . '" class="regular-text" />';
    // Escaped output
    echo '<p class="description">' . esc_html__('IDs dos gateways separados por vírgula para exibir primeiro.', 'tweaks-for-woocommerce') . '</p>';
}

function satodu_tweaks_custom_statuses_callback()
{
    $value = get_option('satodu_tweaks_custom_statuses', "wc-em-separacao|Em Separação\nwc-enviado|Enviado");
    echo '<textarea name="satodu_tweaks_custom_statuses" rows="5" cols="50" class="large-text code">' . esc_textarea($value) . '</textarea>';
    // Escaped output
    echo '<p class="description">' . esc_html__('Um por linha. Formato: slug|Rótulo', 'tweaks-for-woocommerce') . '</p>';
}

/**
 * Sanitize Statuses
 *
 * @param string $input Raw input.
 * @return string Sanitized input.
 */
function satodu_tweaks_sanitize_statuses($input)
{
    $lines = explode("\n", $input);
    $sanitized_lines = array();

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        $parts = explode('|', $line);
        if (count($parts) >= 2) {
            $slug = sanitize_key(trim($parts[0]));
            $label = sanitize_text_field(trim($parts[1]));

            // Ensure slug starts with wc-
            if (strpos($slug, 'wc-') !== 0) {
                $slug = 'wc-' . $slug;
            }

            // Ensure slug is not longer than 20 chars (WP post_status limit)
            if (strlen($slug) > 20) {
                $slug = substr($slug, 0, 20);
            }

            $sanitized_lines[] = $slug . '|' . $label;
        }
    }

    return implode("\n", $sanitized_lines);
}

/**
 * Settings Page HTML with Tabs
 */
function satodu_tweaks_settings_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Use wp_unslash before sanitization
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin tab navigation does not require nonce verification for display.
    $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'financial';
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <nav class="nav-tab-wrapper">
            <a href="?page=satodu-tweaks-settings&tab=financial"
                class="nav-tab <?php echo 'financial' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Financeiro', 'tweaks-for-woocommerce'); ?></a>
            <a href="?page=satodu-tweaks-settings&tab=statuses"
                class="nav-tab <?php echo 'statuses' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Status do Pedido', 'tweaks-for-woocommerce'); ?></a>
        </nav>

        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting group
            if ('financial' === $active_tab) {
                settings_fields('satodu_tweaks_financial');
            } elseif ('statuses' === $active_tab) {
                settings_fields('satodu_tweaks_statuses');
            }

            do_settings_sections('satodu-tweaks-settings');
            submit_button(__('Salvar Configurações', 'tweaks-for-woocommerce'));
            ?>
        </form>
    </div>
    <?php
}
