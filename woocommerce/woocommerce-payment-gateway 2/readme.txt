=== WooCommerce Payment Gateway - Inspire ===
Contributors: innerfire, shulmang
Donate link: http://www.inspirecommerce.com/woocommerce/
Tags: WooCommerce, Payment, Gateway, Credit Cards, Shopping Cart, Inspire, Inspire Commerce, Extension, Subscriptions, Recurring Billing, Membership
Requires at least: 3.0.0
Tested up to: 4.7.1
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept all major credit cards directly on your WooCommerce site via this seamless and secure WooCommerce gateway.

== Description ==

Inspire Commerce is the all-in-one solution that allows you to turn your beautiful WooCommerce backed shopping experience into a selling machine.  This WooCommerce gateway plugin, combined with a [merchant account](http://www.inspirecommerce.com/woocommerce/ "merchant account") from Inspire Commerce, will allow you to accept transactions from all major credit cards including Visa, MasterCard, American Express, Discover, Diners Club, and JCB.  

Using Inspire Commerce's innovative and secure [.pay™ developer framework](http://www.getdotpay.com ".pay"), this plugin supports both card storage as well as integration with the WooCommerce subscriptions module.  Inspire Commerce meets the highest standards of security and reliability and is PCI Level 1 compliant.  

= For US Based Merchants =
You will need to setup a [credit card processing account with Inspire Commerce](http://www.inspirecommerce.com/woocommerce/ "credit card processing account with Inspire Commerce").  This account will allow you to accept payments from anyone in the world using all major card brands.  There is no processing minimum volume for US based merchants.  Account setup typically takes two to three business days.  Details are available on the [Inspire Commerce website](http://www.inspirecommerce.com/woocommerce/ "Inspire Commerce website").

= For Merchants outside of the US =
At this time, because of the complexity of international credit card processing, we are only accepting applicants for merchants that process over $40,000 USD per month for the WooCommerce Payment Gateway.  We can handle processing in most currencies throughout the world, but do require European business registration in most cases.  Please contact [Inspire Commerce](http://www.inspirecommerce.com/contact-us/ "Inspire Commerce") for more information.

= Key Features =
* __Seamless integration__ into the WooCommerce checkout page.
* __Customers never leave your site__
* Accept __all major credit cards__
* __Prevent Fraud__ – Identify suspicious transactions with our value added products and built-in fraud tools.
* __Risk Management__ – Our gateway is PCI Level 1 compliant (geek for secure as hell).  So all card data is stored securely.
* __Receive Payments Quickly__ – Your funds are automatically deposited into your merchant bank account within two business day.
* __Free Help__ – Inspire Commerce provides free technical and account support to merchants, as well as access to online documentation and user guides.

= Value Added Feature =
* __Mobile Payments__ - Easily accept credit cards on the go, at trade shows, in store, or at festivals year round.  All processing data is synchronized for consolidated reporting and accounting.

__Note: This extension requires an SSL Certificate to be installed on your site to ensure your customer’s credit card details are safe.__

== Installation ==

= Installing The Payment Gateway Plugin =
* Download the plugin zip file.
* Login to your WordPress Admin. Click on Plugins | Add New from the left hand menu.
* Click on the “Upload” option, then click “Choose File” to select the zip file from your computer. Once selected, press “OK” and press the "Install Now" button.
* Activate the plugin.
* Open the settings page for WooCommerce and click the "Payment Gateways," tab.
* Click on the sub tab for "Inspire."
* Configure your Inspire Commerce Gateway settings. See below for details.

__Note: This plugin requires that you have an SSL certificate installed and active on your site.__

= Obtain Credentials from Inspire Commerce Gateway =
To setup your Inspire Commerce Gateway you will need to enter your account Username and Password.

= Connect to WooCommerce =
To configure the plugin, go to __WooCommerce > Settings__ from the left hand menu, then the top tab “Payment Gateways”. You should see __"Inspire"__ as an option at the top of the screen. 
__*You can select the radio button next to this option to make it the default gateway.*__

* __Enable/Disable__  – check the box to enable Inspire Commerce.
* __Title__  – allows you to determine what your customers will see this payment option as on the checkout page.  
* __Description__  – controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept. 
* __Username__  – enter the API username you created in your Inspire Commerce Gateway account. 
* __Password__  – enter the API password you created 
* __Sale Method__  – select the sale method you prefer – your options are: ‘Authorize Only’ or ‘Authorize & Capture.  ‘Authorize Only’ will authorize the customer’s card for the purchase amount only.  ‘Authorize & Capture’ will authorize the customer’s card and collect funds.
* __Card Types__  - make sure to highlight all the credit card options in blue (using either the shift or control button) 
* __CVV__  – check the box to require customers to enter their credit card CVV code
* __Billing Information Storage__  – this is an optional feature of the gateway.  In order to use this feature you will have to sign up for our Customer Vault.
* __Save Changes.__ 

== Screenshots ==

1. Easily accept credit card payments on your own branded page.

2. Here is a close up of the payment form as seen by your customers (it is fully editable and already branded to perfection for all WooThemes.com themes).  Notice that advanced features such as saving credit cards securely via the Inspire Commerce card vault are available if you choose to activate them in the admin area.  The module even works flawlessly with the WooCommerce subscriptions module.

3. While you can do a LOT on the back end, here is a simple screen shot of the two most important data points... your API username and password which are used to connect the gateway to your shopping cart. 

== Frequently Asked Questions ==

= Do I need a merchant account before I can use the Inspire Commerce gateway plugin? =
Yes.  In order to use this plugin you will need a merchant services account.  Inspire Commerce offers merchant accounts. For more information, please visit: http://www.inspirecommerce.com. If you already have a merchant account set up, chances are our gateway will integrate with it.  Send us an email: support@inspirecommerce.com or call our office: 800-261-3173 to find out more.

= What is the cost for the gateway plugin? =
This plugin is a FREE download, however it does have monthly and per transaction costs.  The gateway is $10/month plus $0.10 per transaction for the basic gateway features.  We do also offer value added features for an additional fee.  For more information: support@inspirecommerce.com 

= Inspire Commerce is not showing up as a payment method - help! =
For security purposes, this plugin requires an active SSL connection via a secure https page to view this option on your payment pages.

= Does the plugin work with the WooCommerce subscription extension? =
Yes.  It works as long as the card vault functionality is activated in the gateway, and is turned on inside the WooCommerce payment gateways settings for Inspire Commerce.

== Changelog ==

= 2.0.0 =
* Compatibility with WooCommerce 2.6.x and Subscriptions 2.x

= 1.7.6 =
* WooCommerce 2.1 notice API

= 1.7.5 =
* WooCommerce 2.2 refund functionality added

= 1.7.4 =
* Extended subscriptions functionality

= 1.7.3 =
* Billing email now sends to gateway

= 1.7.2 =
* Fixed minor UI Bugs

= 1.7.1 =
* Refactored Vault Functionality

= 1.7 =
* WooCommerce 2.0 compliant.

= 1.6 =
* Fixed logic issues with new customers.

= 1.5 =
* Fixed issue with purchasing subscriptions using different billing methods.
* Fixed JavaScript issue in checkout.

= 1.4 =
* Added option to store customer payment data for future orders.

= 1.3 =
* Added Subscriptions support.
* Revised code for better adherence to WordPress style guidelines.
* Improved error notifications.

= 1.2 =
* Converted payment processing to .pay Direct Post API.
* Removed Test Mode (incompatible with Direct Post).
* Removed debugging emails.

= 1.1 =
* Removed unused options.
* Switched logging to be email based for added security. Old logs should be deleted - delete the 'logs' directory in the plugin folder if it exists.
* Logging requires test mode on.
 
= 1.0 =
* First Release.

== Upgrade Notice ==

= 1.7.3 =
* Billing email now sends to gateway

= 1.7.2 =
* Fixed minor UI Bugs

= 1.7.1 =
* Refactored Vault Functionality

= 1.7 =
* WooCommerce 2.0 compliant.

= 1.6 =
* Fixed logic issues with new customers.

= 1.5 =
* Fixed issue with purchasing subscriptions using different billing methods.
* Fixed JavaScript issue in checkout.

= 1.4 =
* Added option to store customer payment data for future orders.

= 1.3 =
* Added Subscriptions support.
* Revised code for better adherence to WordPress style guidelines.
* Improved error notifications.

= 1.2 =
* Converted payment processing to .pay Direct Post API.
* Removed Test Mode (incompatible with Direct Post).
* Removed debugging emails.

= 1.1 =
* Removed unused options.
* Switched logging to be email based for added security. Old logs should be deleted - delete the 'logs' directory in the plugin folder if it exists.
* Logging requires test mode on.
 
= 1.0 =
* First Release.
