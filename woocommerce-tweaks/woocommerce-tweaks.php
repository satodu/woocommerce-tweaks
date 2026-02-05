<?php
/**
 * Plugin Name: Tweaks for WooCommerce
 * Plugin URI:  https://github.com/satodu/woocommerce-helpers
 * Description: A collection of custom tweaks and enhancements for WooCommerce, including Pix discounts, custom order statuses, and checkout improvements.
 * Version:     1.1.3
 * Author:      Satodu
 * Author URI:  https://satodu.com
 * License:     GPL-2.0+
 * Text Domain: tweaks-for-woocommerce
 * Requires Plugins: woocommerce
 *
 * @package TweaksForWooCommerce
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include Settings Page
require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';

/**
 * Apply a personalized coupon via URL parameter.
 *
 * Checks for 'coupon_code' in the URL and applies it to the cart.
 * Redirects to the checkout page upon success.
 *
 * Usage: domain.com/?coupon_code=YOURCODE
 *
 * @return void
 */
function satodu_tweaks_apply_custom_coupon()
{
    // Verifica se o parâmetro 'coupon_code' está presente na URL
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Publicly accessible URL parameter for marketing purposes.
    if (isset($_GET['coupon_code'])) {
        // WordPress Security: Use wp_unslash before sanitization
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Validated above.
        $coupon_code = sanitize_text_field(wp_unslash($_GET['coupon_code']));

        // Adiciona o cupom ao carrinho
        if (WC()->cart && !empty($coupon_code)) {
            $applied = WC()->cart->apply_coupon($coupon_code);

            if ($applied) {
                wc_add_notice(__('Cupom aplicado com sucesso!', 'tweaks-for-woocommerce'), 'success');
            } else {
                wc_add_notice(__('Erro ao aplicar o cupom. Verifique o código.', 'tweaks-for-woocommerce'), 'error');
            }
        }

        // Redireciona o usuário de volta ao carrinho ou checkout
        // WordPress Security: Use wp_safe_redirect
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}
add_action('template_redirect', 'satodu_tweaks_apply_custom_coupon');

/**
 * Display a "Remove" link in the checkout order review table.
 *
 * @param array $item_data Existing item data.
 * @param array $cart_item Cart item details.
 * @return array Modified item data with the remove link.
 */
function satodu_tweaks_display_remove_link_checkout($item_data, $cart_item)
{
    $product_id = $cart_item['product_id'];
    $cart_item_key = $cart_item['key'];

    $remove_link = sprintf(
        '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
        esc_url(wc_get_cart_remove_url($cart_item_key)),
        esc_attr__('Remover este item', 'tweaks-for-woocommerce'),
        esc_attr($product_id),
        esc_attr($cart_item_key),
        esc_html__('Remover', 'tweaks-for-woocommerce')
    );

    $item_data[] = array(
        'key' => __('Ação', 'tweaks-for-woocommerce'),
        'value' => $remove_link,
    );

    return $item_data;
}
add_filter('woocommerce_get_item_data', 'satodu_tweaks_display_remove_link_checkout', 10, 2);

/**
 * Redirect Cart page to Checkout.
 *
 * Skips the cart page entirely if the cart is not empty.
 *
 * @return void
 */
function satodu_tweaks_redirect_cart_to_checkout()
{
    // Verifica se estamos na página do carrinho e se o carrinho não está vazio
    if (is_cart() && !WC()->cart->is_empty()) {
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}
add_action('template_redirect', 'satodu_tweaks_redirect_cart_to_checkout');

/**
 * Apply discount for Asaas Pix payments.
 *
 * Applies a percentage discount to the cart total if the chosen payment method is Asaas Pix.
 *
 * @param WC_Cart $cart The WooCommerce cart object.
 * @return void
 */
function satodu_tweaks_discount_for_asaas_pix($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // ID do método de pagamento ASAAS PIX
    $payment_method_id = 'asaas-pix';
    $discount_percentage = get_option('satodu_tweaks_pix_discount', 2); // Get from settings, default 2%

    // Verifica o método de pagamento escolhido
    if (WC()->session && WC()->session->get('chosen_payment_method') === $payment_method_id) {
        $discount = $cart->subtotal * ($discount_percentage / 100);

        // Ensure discount is not negative
        if ($discount > 0) {
            $cart->add_fee(__('Desconto por pagamento via PIX', 'tweaks-for-woocommerce'), -$discount);
        }
    }
}
add_action('woocommerce_cart_calculate_fees', 'satodu_tweaks_discount_for_asaas_pix');

/**
 * Reorder payment gateways.
 *
 * Moves configured payment methods to the top of the list.
 *
 * @param array $gateways List of available payment gateways.
 * @return array Reordered payment gateways.
 */
function satodu_tweaks_reorder_payment_gateways($gateways)
{
    $settings_value = get_option('satodu_tweaks_priority_gateways', 'asaas-pix, asaas-credit-card');
    $new_order = array_map('trim', explode(',', $settings_value));

    $ordered_gateways = array();

    // Add prioritized gateways first
    foreach ($new_order as $gateway_id) {
        foreach ($gateways as $gateway) {
            if (is_object($gateway) && isset($gateway->id) && $gateway->id === $gateway_id) {
                $ordered_gateways[] = $gateway;
            }
        }
    }

    // Add remaining gateways
    foreach ($gateways as $gateway) {
        if (is_object($gateway) && isset($gateway->id) && !in_array($gateway->id, $new_order, true)) {
            $ordered_gateways[] = $gateway;
        }
    }

    return $ordered_gateways;
}
add_filter('woocommerce_payment_gateways', 'satodu_tweaks_reorder_payment_gateways');

/**
 * Register Custom Order Statuses dynamically.
 *
 * @return void
 */
function satodu_tweaks_register_custom_statuses()
{
    $statuses_setting = get_option('satodu_tweaks_custom_statuses', "wc-em-separacao|Em Separação\nwc-enviado|Enviado");
    $lines = explode("\n", $statuses_setting);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $parts = explode('|', $line);
        if (count($parts) >= 2) {
            $slug = trim($parts[0]);
            $label = trim($parts[1]);

            // Ensure slug starts with wc-
            if (strpos($slug, 'wc-') !== 0) {
                $slug = 'wc-' . $slug;
            }

            // Ensure slug is not longer than 20 chars (WP post_status limit)
            if (strlen($slug) > 20) {
                $slug = substr($slug, 0, 20);
            }

            register_post_status($slug, array(
                'label' => $label,
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                // translators: %s: count of orders with this status
                'label_count' => array(
                    'singular' => $label . ' <span class="count">(%s)</span>',
                    'plural' => $label . ' <span class="count">(%s)</span>',
                    'context' => null,
                    'domain' => 'tweaks-for-woocommerce',
                ),
            ));
        }
    }
}
add_action('init', 'satodu_tweaks_register_custom_statuses');

/**
 * Add custom statuses to WooCommerce order status list.
 *
 * @param array $order_statuses Existing order statuses.
 * @return array Modified order statuses.
 */
function satodu_tweaks_add_custom_statuses_to_wc($order_statuses)
{
    $statuses_setting = get_option('satodu_tweaks_custom_statuses', "wc-em-separacao|Em Separação\nwc-enviado|Enviado");
    $lines = explode("\n", $statuses_setting);

    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if (count($parts) >= 2) {
            $slug = trim($parts[0]);
            $label = trim($parts[1]);

            // Ensure slug starts with wc-
            if (strpos($slug, 'wc-') !== 0) {
                $slug = 'wc-' . $slug;
            }

            // Ensure slug is not longer than 20 chars
            if (strlen($slug) > 20) {
                $slug = substr($slug, 0, 20);
            }

            $order_statuses[$slug] = $label;
        }
    }
    return $order_statuses;
}
add_filter('wc_order_statuses', 'satodu_tweaks_add_custom_statuses_to_wc');

/**
 * Send email when status changes to "Em Separação".
 *
 * Triggers the "Customer Processing Order" email.
 * Applies only if 'wc-em-separacao' is defined in the system.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function satodu_tweaks_email_for_status_separating($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    if ($order && 'em-separacao' === $order->get_status()) {
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();
        if (isset($emails['WC_Email_Customer_Processing_Order'])) {
            $emails['WC_Email_Customer_Processing_Order']->trigger($order_id);
        }
    }
}
add_action('woocommerce_order_status_em-separacao', 'satodu_tweaks_email_for_status_separating');

/**
 * Send email when status changes to "Enviado".
 *
 * Triggers the "Customer Completed Order" email.
 * Applies only if 'wc-enviado' is defined in the system.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function satodu_tweaks_email_for_status_shipped($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    if ($order && 'enviado' === $order->get_status()) {
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();
        if (isset($emails['WC_Email_Customer_Completed_Order'])) {
            $emails['WC_Email_Customer_Completed_Order']->trigger($order_id);
        }
    }
}
add_action('woocommerce_order_status_enviado', 'satodu_tweaks_email_for_status_shipped');
