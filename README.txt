=== instashipping ===
Contributors: Insta Dispatch
Donate link: https://www.instadispatch.com/
Tags: Insta Shipping
Requires at least: 5.7.3
Tested up to: 5.9.3
Stable tag: 4.3.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin allows you to Automatically Import Shipment Tracking Details directly from InstaDispacth and update it in your WooCommerce Orders.

== Description ==

Instashipping plugin lets you access the lowest-possible rates from multiple carriers. Display accurate real-time shipping rates at the checkout page.

Instashipping automates the shipping flow in your WooCommerce shop. In one click your orders will be directly converted into shipments and are ready to ship, a label is generated, and your tracking information is available directly in the woo-commerce backend.

To use this plugin, you must have an account with <a href="https://www.instadispatch.com/" target="_blank">InstaDispatch courier management software</a> and get your API key and also an account with the supported carrier.

This plugin uses live APIs and enables you ship from your store anywhere in the world.

Key Features:-

* Unlimited shipping methods and rates calculation
* Possibility of adding the titles and descriptions to orders
* Show live shipping rates, and configure customizable shipping options for your customers during checkout.
* Option to display the selected shipping methods only for logged-in users
* Set preferred carrier & service to book shipments
* Get shipping labels for multiple orders in one click
* Automatically complete orders when shipping label is created
* Automatically update “fulfilled” orders with tracking numbers


Connection Requirements:
1.An active account with WooCommerce.
2.Integration installed in WooCommerce.
3.Your InstaDispatch authentication key.
4.Your Store URL.

Order Import Requirements:
1.Include a Ship To address with all the valid required fields (e.g., address line 1, postcode, etc.)
2.At least one physical product in the order.


== Installation ==

The plugin is simple to install:

1. Download `instaship.zip`
2. Unzip
3. Upload `instaship` directory to your `/wp-content/plugins` directory
4. Go to the plugin management page and enable the plugin

Full documentation can be found on the instaship configuration after installation.(https://drive.google.com/file/d/1duvD4vwpmrNQYCZS3CvVP3wvrcl-uOKm/view?usp=sharing)


== Description of third party service==

*The below endpoint is being used within the plugin and this is required to book the shipment and print the label pdf.

https://api.instadispatch.com/live/restservices/getQuotation
https://api.instadispatch.com/live/restservices/bookQuotation

*We are not sharing any personal information with any third party. 

*The external URLs belong to the same author who has built this plugin.

*This link will help you to understand more.(https://www.instadispatch.com/e-commerce-delivery-management-solution/)


== Frequently Asked Questions ==

= What do I need to install this plugin? =
Stable internet connection & an account with InstaDispatch. It’s a woocommerce dependent plugin to ensure you have installed a woocommerce plugin on your wordpress site.

= How do I get my API keys? =
To get your key, you should create an account with <a href="https://www.instadispatch.com/" target="_blank">InstaDispatch courier management software</a>, navigate to customers on your InstaDispatch account, scroll and find your API Key in the authorization key section.

= How do I use the API keys? =
After copying your key from InstaDispatch, come back to your wordpress dashboard, navigate to Instashipping settings, enable it by checking the live check box. Paste your API Key and then click on save button.

= What if I need some help? =
You can directly contact us at Phone: +44 (0) 203-890-3158 & drop your emails to help@Instadispatch.com

= Sell Anywhere, Ship Using Instashipping:- =
You should have your own carrier account to ship and we also suggest you the best rates and let you connect to our one of the partners to get the best rates for DHL, DHL parcels & DSV etc. Carriers supported are as follows:-


== Screenshots ==

1. Set Live api key.
2. Create shipment of order.
3. order grid view.
4. Select desire service to book shipment.
5. Edit view of the shipment. 
6. Booked shipment with selected service.
7. Download or print label pdf.

== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.
