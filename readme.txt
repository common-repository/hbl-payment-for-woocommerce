=== Add Himalayan Bank Payment In WooCommerce (HBL Payment) ===
Contributors: sanzeeb3, lijnam
Tags: hbl-payment, woocommerce
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 8.1.0
Stable tag: 2.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds Himalayan Bank Payment Gateway for WooCommerce.

== Description ==

### HIMALAYAN BANK PAYMENT GATEWAY PLUGIN

Accept the Himalayan Bank payment from your store.

> <strong>IMPORTANT</strong><br />
> The 1.x version of this plugin doesn't work anymore.
> You should update to version 2.x. However, perform some tests before setting up in the live environment.


### REQUIREMENTS

* WooCommerce Plugin.
* PHP 8.1.0 or higher.
* PHP [Sodium Extension](https://www.php.net/manual/en/book.sodium.php)


The plugin simply doesn't work if the above requirements aren't met.
 
<strong>Follow [the setup instructions](https://sanjeebaryal.com.np/accept-himalayan-bank-payment-from-your-woocommerce-site/).</strong>


### Intro
[Himalayan Bank Limited](https://www.himalayanbank.com/) is one of the largest private banks of Nepal. If you do not know about the Himalayan Bank, you probably do not need this plugin.

Developer of this plugin is not associated with Himalayan Bank in anyway.

Namaste!


== Screenshots ==

1. Checkout

== Frequently Asked Questions ==

= Why the payment status remains at "Processing"? =

That's fine. "Processing" doesn't mean it's processing payment -- it means it's being processed/fulfilled by the site owner. [Read More](https://sanjeebaryal.com.np/why-is-my-woocommerce-order-status-always-processing/)

= The plugin is awesome. Can I contribute?

Yes, join the [GitHub Repository](https://github.com/sanzeeb3/hbl-payment-for-woocommerce). Contributions of any kind are welcome, not just the codes.

= I have other questions =

Feel free to ask at [https://sanjeebaryal.com.np/](https://sanjeebaryal.com.np/)


== Screenshots ==

1. Settings
2. Checkout

== Changelog ==

= 2.1.0 - 01/29/2023 =
* Stable Version.

= 2.0.9 - 01/13/2023 =
* Fix - Redirect to HBL payment instead of on-site payment.

= 2.0.8 - 01/13/2023 =
* Fix - Various request args.

= 2.0.7 - 01/10/2023 =
* Fix - Access Token Issue & Proper Response

= 2.0.6 - 01/09/2023 =
* Fix - Case sensitive directory issue.

= 2.0.5 - 01/07/2023 =
* PHP 8.1 is required for composer dependencies.

= 2.0.4 - 01/08/2023 =
* Live Mode.

= 2.0.3 - 12/18/2022 =
* Fix - Amount mismatch.

= 2.0.2 - 12/18/2022 =
* Fix - Remove CC details from logs.
* Fix - Remove PHP 8.0 requirement, the plugin works on PHP 7.x as well.
* Add - WordPress.org assets

= 2.0.1 - 12/17/2022 =
* Fix - Update payment status on success.

= 2.0.0 - 12/17/2022 =
* Update to new API

= 1.0.2 - 06/28/2021 =
* Fix - Redirection after cancelling order

= 1.0.0 - 06/26/2021 =
* Initial Release
