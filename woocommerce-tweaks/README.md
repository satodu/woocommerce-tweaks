# Tweaks for WooCommerce
Contributors: satodu
Tags: woocommerce, checkout, payments, pix, tweaks
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.1.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A lightweight helper plugin for WooCommerce that adds various custom functionalities and improvements.

## Features

### 1. Coupon via URL
Automatically apply coupons by visiting a URL with the parameter `coupon_code`.
- **Usage**: `https://yourdomain.com/?coupon_code=WELCOME10`
- If successful, the user is redirected to the checkout page with a success message.

### 2. Remove Item Link in Checkout
Adds a "Remove" link nicely formatted in the checkout order review table, allowing users to remove items without going back to the cart.

### 3. Direct Checkout
Redirects users from the Cart page directly to the Checkout page if the cart is not empty, streamlining the purchase flow.

### 4. Pix Payment Discount
Automatically applies a percentage discount (configurable) when the customer selects **Asaas Pix** as the payment method.

### 5. Payment Gateway Reordering
Prioritizes specific payment methods (configurable) by moving them to the top of the payment options list.

### 6. Custom Order Statuses
Adds custom order statuses (configurable) to better manage fulfillment.
- Defaults: **Em Separação** and **Enviado**.

### 7. Automated Emails for Custom Statuses
- **Em Separação**: Triggers the "Processing Order" email.
- **Enviado**: Triggers the "Completed Order" email.

## Installation

1. Upload the `woocommerce-tweaks` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

Go to **WooCommerce > WC Tweaks** to access the settings.

- **Pix Discount (%)**: Set the percentage discount for the Asaas Pix payment method (Default: 2).
- **Priority Gateways**: Enter a comma-separated list of Gateway IDs to show first (Default: `asaas-pix, asaas-credit-card`).
- **Custom Statuses**: Add custom statuses, one per line, in the format `slug|Label`.
  - Example:
    ```
    wc-em-separacao|Em Separação
    wc-enviado|Enviado
    wc-pronto-retirada|Pronto para Retirada
    ```

## Development & Testing (Docker)

This repository includes a `docker-compose.yml` file to quickly spin up a testing environment with WordPress and WooCommerce fully configured.

### 1. Start the Environment
Run the following command to start the stack and automatically provision WordPress:
```bash
docker-compose up -d
```
*Note: The first run might take a minute as it downloads WooCommerce and installs WordPress.*

### 2. Access the Site
-   **Frontend**: [http://localhost:8500](http://localhost:8500)
-   **Admin**: [http://localhost:8500/wp-admin](http://localhost:8500/wp-admin)
    -   **User**: `admin`
    -   **Password**: `password`

### 3. Verify
WooCommerce, the Plugin, **Payment Gateways**, and a **Test Product** should already be set up.

## Requirements
- WooCommerce 3.0+
- WordPress 5.0+

## Changelog

### 1.1.3
- Fix: Resolved issue where saving settings on one tab would reset settings on other tabs by splitting option groups.
- Fix: Enforced `wc-` prefix and character limit (20) for custom order statuses to prevent orders from reverting to "Pending Payment".
- Fix: Renamed internal functions and options to `satodu_tweaks_` to avoid conflicts.
- Add: Added `Requires Plugins` header to support WordPress 6.5+ dependency checking.
