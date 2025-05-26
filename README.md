# Ecommpay Payments Plugin for Magento 2 – Hyvä Checkout Integration

This module provides compatibility between [Hyvä Checkout](https://www.hyva.io/hyva-checkout.html) and the Ecommpay Payments module for Magento 2, enabling seamless payment processing within the Hyvä Checkout environment.

## Dependencies

This module depends on:
- Ecommpay Payments Plugin for Magento 2, v2.1.6 or higher
- The Hyvä Default Theme
- `Hyva_Checkout` Magento 2 module

## Installation

### 1. Install the Hyvä Checkout dependencies
Ensure the required Hyvä packages are installed via Composer:

```json
"hyva-themes/magento2-default-theme": "^1.3",
"hyva-themes/magento2-hyva-checkout": "^1.3"
```

### 2. Install the Ecommpay Hyvä module
Run the following command to install the module:

```sh
composer config repositories.ecommpay/hyva-magento git https://github.com/ITECOMMPAY/hyva-magento.git
composer require ecommpay/hyva-magento
```

This will automatically install the `ecommpay/module-payments` module as a dependency.

### 3. Enable the module
Run the following Magento CLI commands to enable and update the module:

```sh
bin/magento setup:upgrade
bin/magento cache:flush
bin/magento cache:clean
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

## Basic Setup

## Prerequisites
Ensure the following are installed and properly configured:
1. Hyvä Theme is installed and enabled for the store view.
2. Hyvä Checkout is installed and set as the store view’s checkout method.
3. Ecommpay Payments Plugin for Magento 2 is installed and configured.
More information on Ecommpay Payments plugin for Magento2 can be found at [Ecommpay Developer Portal](https://developers.ecommpay.com/en/en_CMS__magento.html#en_CMS__magento) and [Adobe Commerce Marketplace Listing](https://commercemarketplace.adobe.com/ecommpay-module-payments.html)

## Configuration

This module does not introduce any additional configuration options. It seamlessly integrates with the default Ecommpay Payments module and the Hyvä Checkout module.

## More Information

For detailed documentation, installation instructions, and configuration options for the Ecommpay Payments Plugin for Magento 2, refer to:

- [Ecommpay Developer Portal](https://developers.ecommpay.com/en/en_CMS__magento.html#en_CMS__magento)
- [Adobe Commerce Marketplace Listing](https://commercemarketplace.adobe.com/ecommpay-module-payments.html)

---
