<p align="center">
  <img src="imgs/logo.jpg" alt="Tweaks for WooCommerce Logo" width="200" style="border-radius: 20px;">
</p>

# Tweaks for WooCommerce

<p align="center">
  <img src="https://img.shields.io/badge/version-1.1.1-blue.svg" alt="Version">
  <img src="https://img.shields.io/badge/wordpress-6.0%2B-blue.svg" alt="WordPress">
  <img src="https://img.shields.io/badge/woocommerce-3.0%2B-purple.svg" alt="WooCommerce">
  <img src="https://img.shields.io/badge/license-GPLv2-green.svg" alt="License">
</p>

**Tweaks for WooCommerce** is a comprehensive toolkit designed to optimize the WooCommerce checkout flow and order management, specifically tailored for the Brazilian market.

It simplifies the buying process with direct checkout options, offers payment incentives (Pix), and enhances order tracking with custom statuses.

---

## 🚀 Key Features

*   **🎟️ Coupon via URL**: Automatically apply coupons by simply visiting a link (e.g., `yoursite.com/?coupon_code=DEAL`).
*   **🛒 Direct Checkout**: Skip the cart page entirely! Redirects users straight to checkout when they add products.
*   **💸 Pix Discount**: Automatically applies a percentage discount (configurable) for customers paying via **Asaas Pix**.
*   **🗑️ Easy Remove**: Adds a convenient "Remove" link directly in the checkout order summary.
*   **📦 Custom Order Statuses**: adds **"Em Separação"** (Processing) and **"Enviado"** (Shipped) statuses to better communicate progress to customers.
*   **💳 Gateway Reordering**: Pin your preferred payment methods (like Pix) to the top of the list.
*   **✉️ Automated Emails**: Triggers standard WooCommerce emails when custom statuses are changed.

---

## 🛠️ Installation

### Manual Installation
1.  Download the latest `.zip` file from the [Releases](https://github.com/satodu/woocommerce-helpers/releases) page.
2.  Go to your WordPress Admin.
3.  Navigate to **Plugins > Add New > Upload Plugin**.
4.  Select the `tweaks-for-woocommerce.zip` file and install.

### Developer Installation (Docker)
This repository comes with a full Docker development environment.

1.  Clone the repository:
    ```bash
    git clone https://github.com/satodu/woocommerce-helpers.git
    cd woocommerce-helpers
    ```

2.  Start the environment:
    ```bash
    docker-compose up -d --build
    ```

3.  Access the site:
    *   **URL**: [http://localhost:8500](http://localhost:8500)
    *   **Admin**: `admin` / `password`

---

## ⚙️ Configuration

Go to **WooCommerce > WC Tweaks** in your admin dashboard.

| Setting | Description | Default |
| :--- | :--- | :--- |
| **Pix Discount** | Percentage discount for Asaas Pix payments. | `2%` |
| **Priority Gateways** | Comma-separated list of Gateway IDs to show first. | `asaas-pix, asaas-credit-card` |
| **Custom Statuses** | Define statuses as `slug|Label` (one per line). | `wc-em-separacao`, `wc-enviado` |

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1.  Fork the project
2.  Create your feature branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

---

<p align="center">
  Made with ❤️ by <a href="https://satodu.com">Satodu</a>
</p>
