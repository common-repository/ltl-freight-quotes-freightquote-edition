=== LTL Freight Quotes - FreightQuote Edition ===
Contributors: enituretechnology
 Tags: eniture,FreightQuote,LTL freight rates,LTL freight quotes, shipping estimates
Requires at least: 6.4
Tested up to:  6.6.2
Stable tag: 2.3.8
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Real-time LTL freight quotes from FreightQuote. Fifteen day free trial.

== Description ==

The FreightQuote LTL Freight Quotes plugin retrieves your negotiated LTL rates, takes action on them according to the plugin settings, and displays the results as shipping options in the WooCommerce cart or checkout page. FreightQuote (freightquote.com ) is a online broker of freight services acquired by C.H. Robinson in 2015. FreightQuote provides LTL freight rates from many carriers through a single account relationship. To establish a FreightQuote account click here[https://www.freightquote.com/create-account] to access the new account request form.

**Key Features**

* Three rating options: Cheapest, Cheapest Options and Average.
* Custom label results displayed in the shopping cart.
* Control the number of options displayed in the shopping cart.
* Display transit times with returned quotes.
* Restrict the carrier list to omit specific carriers.
* Product specific freight classes.
* Support for variable products.
* Option to determine a product's class by using the built in density calculator.
* Option to include residential delivery fees.
* Option to include fees for lift gate service at the destination address.
* Option to mark up quoted rates by a set dollar amount or percentage.

**Requirements**

* WooCommerce 6.4 or newer.
* Your username and password to FreightQuote online shipping system.
* A license from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* Your username and password to FreightQuote's online shipping system.


If you need assistance obtaining any of the above information, contact your local FreightQuote office
or visit [FreightQuote](http://freightquote.com) 

A more extensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-freightquote-ltl-freight-quotes/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "eniture ltl freight quotes", and click Install Now.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get a license from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-freightquote-ltl-freight-quotes/) and pick a
subscription package. When you complete the registration process you will receive an email containing your Eniture API Key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your licenses and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription to the license. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => FreightQuote. Use the *Connection* link to create a connection to your FreightQuote account.

**4. Identify the carriers**
Go to WooCommerce => Settings => FreightQuote. Use the *Carriers* link to identify which carriers you want to include in the 
dataset used as input to arrive at the result that is displayed in your cart. Including all carriers is highly recommended.

**5. Select the plugin settings**
Go to WooCommerce => Settings => FreightQuote. Use the *Quote Settings* link to enter the required information and choose
the optional settings.

**6. Define warehouses and drop ship locations**
Go to WooCommerce => Settings => FreightQuote. Use the *Warehouses* link to enter your warehouses and drop ship locations.  You should define at least one warehouse, even if all of your products ship from drop ship locations. Products are quoted as shipping from the warehouse closest to the shopper unless they are assigned to a specific drop ship location. If you fail to define a warehouse and a product isn’t assigned to a drop ship location, the plugin will not return a quote for the product. Defining at least one warehouse ensures the plugin will always return a quote.

**7. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click on the Shipping Zones link. Add a US domestic shipping zone if one doesn’t already exist. Click the “+” sign to add a shipping method to the US domestic shipping zone and choose SEFL from the list.

**8. Configure your products**
Assign each of your products and product variations a weight, Shipping Class and freight classification. Products shipping LTL freight should have the Shipping Class set to “LTL Freight”. The Freight Classification should be chosen based upon how the product would be classified in the NMFC Freight Classification Directory. If you are unfamiliar with freight classes, contact the carrier and ask for assistance with properly identifying the freight classes for your  products.

== Third-Party Services ==

This plugin relies on the following third-party services to provide some functionalities:

Eniture Technology APIs (https://eniture.com): Used for functionalities like retrieving shipping rates and managing connections.
Terms of Service: link https://eniture.com/eniture-technology-terms-of-use/
Privacy Policy: https://eniture.com/eniture-llc-privacy-policy/

FreightDesk.Online (https://freightdesk.online): Used for functionalities like applying promo code status. Only when a user have an account on freightdesk.online.
Privacy Policy: https://freightdesk.online/privacy-statement

Validate Addresses (https://validate-addresses.com): Used for functionalities like applying and using promo codes, only when user have an acount on validate addresses portal. 
Terms of Service: https://validate-addresses.com/terms-of-use
Privacy Policy: https://validate-addresses.com/privacy-statement

== Frequently Asked Questions ==

= What happens when my shopping cart contains products that ship LTL and products that would normally ship FedEx or UPS? =

If the shopping cart contains one or more products tagged to ship LTL freight, all of the products in the shopping cart 
are assumed to ship LTL freight. To ensure the most accurate quote possible, make sure that every product has a weight 
and dimensions recorded.

= What happens if I forget to identify a freight classification for a product? =

In the absence of a freight class, the plugin will determine the freight classification using the density calculation method. 
To do so the products weight and dimensions must be recorded.

= Why was the invoice I received from FreightQuote.com more than what was quoted by the plugin? =

One of the shipment parameters (weight, dimensions, freight class) is different, or additional services (such as residential 
delivery, lift gate, delivery by appointment and others) were required. Compare the details of the invoice to the shipping 
settings on the products included in the shipment. Consider making changes as needed. Remember that the weight of the packaging 
materials, such as a pallet, is included by the carrier in the billable weight for the shipment.

= How do I find out what freight classification to use for my products? =

Contact your local FreightQuote office for assistance. You might also consider getting a subscription to ClassIT offered 
by the National Motor Freight Traffic Association (NMFTA). Visit them online at classit.nmfta.org.

= How do I get a FreightQuote account? =

Contact FreightQuote at 800.323.5441.

= Where do I find my FreightQuote username and password? =

Usernames and passwords to FreightQuote’s online shipping system are issued by FreightQuote. Contact the FreightQuote at 800.323.5441 if you need assistance.


= How do I get a Eniture API Key for my plugin? =

You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or 
purchased a license outright. At the conclusion of the registration process an email will be sent to you that will include the 
Eniture API Key. You can also login to eniture.com using the username and password you created during the registration process 
and retrieve the Eniture API Key from the My Licenses tab.

= How do I change my plugin license from the trail version to one of the paid subscriptions? =

Login to eniture.com and navigate to the My Licenses tab. There you will be able to manage the licensing of all of your 
Eniture Technology plugins.

= How do I install the plugin on another website? =

The plugin has a single site license. To use it on another website you will need to purchase an additional license. 
If you want to change the website with which the plugin is registered, login to eniture.com and navigate to the My Licenses tab. 
There you will be able to change the domain name that is associated with the Eniture API Key.

= Do I have to purchase a second license for my staging or development site? =

No. Each license allows you to identify one domain for your production environment and one domain for your staging or 
development environment. The rate estimates returned in the staging environment will have the word “Sandbox” appended to them.

= Why isn’t the plugin working on my other website? =

If you can successfully test your credentials from the Connection page (WooCommerce > Settings > Freightquote > Connections) 
then you have one or more of the following licensing issues:

1) You are using the Eniture API Key on more than one domain. The licenses are for single sites. You will need to purchase an additional license.
2) Your trial period has expired.
3) Your current license has expired and we have been unable to process your form of payment to renew it. Login to eniture.com and go to the My Licenses tab to resolve any of these issues.

== Screenshots ==

1. Carrier inclusion page
2. Quote settings page
3. Quotes displayed in cart

== Changelog ==

= 2.3.8 =
* Update: Introduced new feature "Liftgate pickup required" 

= 2.3.7 =
* Fix: Fixed conflict with small package quotes plugins.

= 2.3.6 =
* Fix: Fixed issues reported by WordPress team

= 2.3.5 =
* Fix: Resolved critical error in FreightQuote logs

= 2.3.4 =
* Update: Introduced capability to suppress parcel rates once the weight threshold has been reached.
* Update: Compatibility with WordPress version 6.5.3
* Update: Compatibility with PHP version 8.2.0
* Fix:  Incorrect product variants displayed in the order widget.

= 2.3.3 =
* Update: Replaced UPS Freight log with TForce. 
* Fix: Corrected truckload services names 

= 2.3.2 =
* Update: Fixed a JS issue conflicting with MailChimp plugin  

= 2.3.1 =
* Update: Changed required plan from standard to basic for delivery estimate options.

= 2.3.0 =
* Update: Display "Free Shipping" at checkout when handling fee in the quote settings is  -100% .
* Update: Introduced the Shipping Logs feature.
* Update:  Introduced “product level markup” and “origin level markup”.

= 2.2.9 =
* Update: Compatibility with WooCommerce HPOS(High-Performance Order Storage)

= 2.2.8 =
* Update: Introduced option "Don't offer lift gate delivery as an option if an item is longer than given value"

= 2.2.7 =
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”. 
* Fix: Inherent Flat Rate value of parent to variations 

= 2.2.6 =
* Update:Allow multiple shipping quotes of truckload on cart and checkout. 

= 2.2.5 =
* Fix:Fixed issue in carriers selection.

= 2.2.4 =
* Update: Added compatibility with "Address Type Disclosure" in Residential address detection 

= 2.2.3 =
* Fix: liftgate selection issue for some customers. 

= 2.2.2 =
* Update: Compatibility with WordPress version 6.1
* Update: Compatibility with WooCommerce version 7.0.1

= 2.2.1 =
* Update: On Test Connection, display error message returned by FreightQuote API.

= 2.2.0 =
* Update: Introduced of tabs for freightdesk.online and validate-addresses.com.
* Update: Introduced connectivity from the plugin to FreightDesk.Online using Company ID
* Update: Compatibility with WordPress multisite network

= 2.1.6 =
* Update: By default mark all carriers checked. 

= 2.1.5 =
* Update: Removed client id and client secret fields.

= 2.1.4 =
* Fix: The name of the API connection changed to C. H. Robinson API.

= 2.1.3 =
* Fix: Fixed support link.
* Fix: Fixed Cron scheduling.

= 2.1.2 =
* Update: Compatibility with PHP version 8.1.
* Update: Compatibility with WordPress version 5.9.

= 2.1.1 =
* Update: Relocation of NMFC Number field along with freight class.

= 2.1.0 =
* Update: Updated compatibility with the Pallet Packaging plugin and analytics.

= 2.0.0 =
* Update: Compatibility with PHP version 8.0.
* Update: Compatibility with WordPress version 5.8.
* Fix: Corrected product page URL in connection settings tab.

= 1.3.1 =
* Update: Added feature "Weight threshold limit".
* Update: Added feature In-store pickup with terminal information.

= 1.3.0 =
* Update: Cuttoff Time.
* Update: Added images URL for FDO portal.
* Update: CSV columns updated.

= 1.2.2 =
* Update: Introduced new features, Order detail widget for draft orders, improved order detail widget for Freightdesk.online, compatibly with Shippable add-on, compatibly with Account Details(ET) add-don(Capturing account number on checkout page).

= 1.2.1 =
* Update: Added condition for showing one quote on cart page

= 1.2.0 =
* Update: Compatibility with WordPress 5.6

= 1.1.0 =
* Update: Added features a) Product nesting b) Compatibility with FreightDesk.onlie c) Compatibility with microwarehouse  

= 1.0.5 =
* Update: Compatibility with WordPress 5.5

= 1.0.4 =
* Update: Compatibility with WordPress 5.4

= 1.0.3 = 
* Update: Introduced Weight of Handling Unit and Maximum Weight per Handling Unit

= 1.0.2 = 
* Update: Modifications in carriers added in version 1.0.0

= 1.0.1 =
* Update: Added New carriers in carriers tab. a) FREIGHTQUOTE.COM, INC (Service code: ABHB) b) Freightquote Refrigerated Logistics (Service code: REEF) c) FREIGHTQUOTE.COM, INC (Flatbed Logistics) (Service code: TSM).

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

