=== WPEC Targetpay for WP E-Commerce ===
Contributors: seuser
Author: seuser
Author URI: scotteuser.com
Tags: wpsc, wp-e-commerce, targetpay, ideal
Requires at least: 4.0.0.0
Tested up to: 4.9
License: GPL2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

This plugin adds iDEAL TargetPay as a payment gateway to your Wp E-Commerce (WPSC / GetShopped) store.

== Description ==
Add this plugin to your site to extend Wp E-Commerce (WPSC / GetShopped) with a new payment gateway: iDEAL TargetPay.

When you install this plugin, you can navigate to Store Settings and into the Payments tab. There you will find a new payment gateway "WP E-Commerce Targetpay iDeal".

You will need your "Layout Code" from targetpay.com at a minimum.

= How it works =

1. When a visitor goes to checkout they will see the new payment option which will be "iDEAL via Targetpay" by default (but you can name it as you would like).
2. If a visitor chooses that payment method a responsive popup window will appear prompting them to choose their bank first.
3. Once they have chosen their bank they will be taken to the bank's iDEAL payment page.
4. TargetPay will let the website know how the transaction when and that information is used to update the Store Sale and adds that information to the WP E-Commerce purchase logs.

= If something goes wrong =

You can specify an email address you would like to be notified at for any TargetPay errors. The full error information will be automatically emailed to you. For instance, when you first set up your TargetPay account, TargetPay normally waits until you have tried a first test transaction before finalising your activation and would return an error saying you need to activate still.

= Testing mode =

You can specify that you want the payment gateway to only be available if logged in as an administrator. That allows you to test the payments and ensure your TargetPay account is set up and activated prior to letting your customers see the payment option.

= Liability =

I assume no responsibility for any issues or errors you have with this plugin. While I will do my best to support it, by using it you use it at your own risk. I am not affiliated with TargetPay in any way.

== Installation ==

From your WordPress dashboard
Visit 'Plugins > Add New'

1. Search for 'Targetpay for WP E-Commerce'
2. Activate WP E-Commerce Targetpay from your Plugins page.

From WordPress.org

1. Download Targetpay for WP E-Commerce.
2. Upload the 'targetpay-for-commerce' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate WP E-Commerce Targetpay from your Plugins page.

Once Activated

1. Navigate to Settings > Store > Payments and check the "WP E-Commerce Targetpay iDeal" box.
2. This will bring up the settings which you can fill in to set up the payment gateway.
3. You must at least fill in your Layout Code.
4. You will find the new payment method available as an option in your checkout process.

== Screenshots ==

1. This screenshot shows the options available for this payment gateway in your Store settings > Payment gateways area.
