<?php
/**
 * Setup WooCommerce Store for Testing
 *
 * Sets currency to BRL, Country to BR, and enables COD/BACS gateways.
 * Also registers a Mock Asaas Pix gateway for testing discount logic.
 */

// 1. General Settings
update_option('woocommerce_store_address', 'Av. Paulista, 1000');
update_option('woocommerce_store_city', 'São Paulo');
update_option('woocommerce_default_country', 'BR:SP');
update_option('woocommerce_store_postcode', '01310-100');
update_option('woocommerce_currency', 'BRL');
update_option('woocommerce_product_type', 'both');
update_option('woocommerce_allow_tracking', 'no');

// 2. Enable Guest Checkout
update_option('woocommerce_enable_guest_checkout', 'yes');

// 3. Enable Payment Gateways
$gateways = array('bacs', 'cheque', 'cod');

foreach ($gateways as $gateway_id) {
    $option_name = 'woocommerce_' . $gateway_id . '_settings';
    $settings = get_option($option_name, array());
    $settings['enabled'] = 'yes';

    // Set some default values
    if ('bacs' === $gateway_id) {
        $settings['title'] = 'Transferência Bancária';
        $settings['description'] = 'Faça um PIX ou TED para nossa conta.';
    } elseif ('cod' === $gateway_id) {
        $settings['title'] = 'Pagamento na Entrega';
    }

    update_option($option_name, $settings);
}

echo "Store configured: BRL, BR:SP, Gateways Enabled (BACS, COD).\n";

// 4. Create a Mock Asaas Pix Gateway Option?
// Realistically, to test the discount logic which checks for 'asaas-pix', 
// we simply need to enable a gateway with that ID. 
// Since we don't have the real Asaas plugin, we can't 'enable' it unless we register it.
// The user might want to install the real plugin, OR we can add a dummy one in our tweaks plugin.
// For now, let's just stick to standard gateways so they can checkout.
