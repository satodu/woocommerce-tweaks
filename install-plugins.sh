#!/bin/bash

# Ensure executable
# chmod +x install-plugins.sh

echo "Waiting for WordPress to be ready..."
# Simple wait loop
sleep 10

echo "Installing WooCommerce..."
docker-compose run --rm cli wp plugin install woocommerce --activate

echo "Activating WooCommerce Tweaks..."
docker-compose run --rm cli wp plugin activate woocommerce-tweaks

echo "Done! You can access your site at http://localhost:8500"
