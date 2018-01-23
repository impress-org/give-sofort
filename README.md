# Give - Sofort Payment Gateway

## Description



## Requirements

- WordPress 4.8 or greater
- PHP version 7.0 or greater
- MySQL version 5.6. or greater
- Give WP 1.8. or greater
- Sofort. Banking requires fsockopen support, libcurl and openssl (for IPN access)

## Installation
1. Download the plugin
2. Upload to wp-content/plugins/
3. Activate in the backend
4. Set the options in Donations -> Settings -> Payment Gateways -> Sofort
5. Done!!

## Usage
1. Login into www.sofort.com/payment/users/login
2. Create a new project or edit a existing project
3. Copy the Configuration key for your shop system at the bottom of the general settings section
4. Paste the Configuration key into the field for the Live Key
5. Add the reason which should appear at the customers payment banking receipt 
6. Enable the payment gateway under Donations -> Settings -> Payment Gateways -> Gateways
7. Done!!

## Optional

- You can choose to collect billing details if you need it for the donation process. The Sofort. Banking doesn't need this billing details.
- The callback status Pending payments from Sofort. Banking means that the payment is authorized, but the money transfer is not completed yet. In the most cases you can trust pending payments.

## Languages
- English
- German

## Disclaimer

We are not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.