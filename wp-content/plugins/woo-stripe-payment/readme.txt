=== Stripe For WooCommerce ===
Contributors: mr.clayton
Tags: stripe, ach, klarna, credit card, apple pay, google pay, ideal, sepa, sofort
Requires at least: 3.0.1
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 3.3.9
Copyright: Payment Plugins
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Accept Credit Cards, Google Pay, ApplePay, Afterpay, ACH, Klarna, iDEAL and more all in one plugin for free!

= Official Stripe Partner = 
Payment Plugins is an official partner of Stripe. 

= Boost conversion by offering product and cart page checkout =
Stripe for WooCommerce is made to supercharge your conversion rate by decreasing payment friction for your customer.
Offer Google Pay, Apple Pay, and Stripe's Browser payment methods on product pages, cart pages, and at the top of your checkout page.

= Visit our demo site to see all the payment methods in action = 
[Demo Site](https://demos.paymentplugins.com/wc-stripe/product/pullover/)

To see Apple Pay, visit the site using an iOS device. Google Pay will display for supported browsers like Chrome.

= Features =
- Credit Cards
- Google Pay
- Apple Pay
- Afterpay
- ACH Payments
- 3DS 2.0
- Local Payment Methods
- WooCommerce Subscriptions
- WooCommerce Pre-Orders
- WooCommerce Blocks

== Frequently Asked Questions ==
= How do I test this plugin? = 
 You can enable the plugin's test mode, which allows you to simulate transactions.
 
= Does your plugin support WooCommerce Subscriptions? = 
Yes, the plugin supports all functionality related to WooCommerce Subscriptions.

= Where is your documentation? = 
https://docs.paymentplugins.com/wc-stripe/config/#/

= Why isn't the Payment Request button showing on my local machine? = 
If you're site is not loading over https, then Stripe won't render the Payment Request button. Make sure you are using https.

== Screenshots ==
1. Let customers pay directly from product pages
2. Apple pay on the cart page
3. Custom credit card forms
4. Klarna on checkout page
5. Local payment methods like iDEAL and P24
6. Configuration pages
7. Payment options at top of checkout page for easy one click checkout
8. Edit payment gateways on the product page

== Changelog ==
= 3.3.9 =
* Added - OXXO payment method support for standard checkout and [WooCommerce Blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/)
* Added - WeChat support for currencies CNY, DKK, NOK, SEC, CHF
* Added - Validate that Boleto CPF / CNPJ field has been populated before placing order
* Added - Filter "wc_stripe_local_element_options" which can be used to customize the locale of the local payment method
* Updated - Don't rely on WooCommerce Setting "Number of decimals" when converting amount to cents.
= 3.3.8 =
* Added - Boleto payment method support for standard checkout and [WooCommerce Blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/)
* Added - Better support for WooCommerce One Page Checkout plugin. 3DS is now requested for cards when required on product page.
* Added - Currencies with 3 decimal places (BHD, IQD, JOD, KWD, LYD, OMR, TND) to function wc_stripe_get_currencies
* Updated - WC Tested up to 5.6
* Fixed - Mini cart unblock if there is a payment processing error
= 3.3.7 =
* Added - If locale is "fi" change to "fi-FI"
* Added - Filter "wc_stripe_get_klarna_args"
* Updated - PHP8, replace round with wc_format_decimal for GPay
= 3.3.6 =
* Added - WooFunnels integration that supports credit cards, Apple Pay, GPay, and Payment Request gateway
* Added - Support for "WooCommerce All Products For Subscriptions" plugin
* Added - Round GPay totals to 2 decimals points since GPay will only accept a maximum of 2 decimals
* Fixed - Klarna ensure item totals always add up to order total to prevent "Bad Value" error.
= 3.3.5 =
* Fixed - BECS not always redirecting to the order received thank you page
* Fixed - Mini-cart issue where GPay button wasn't rendering due to styling change
* Fixed - Apple Pay all white button which was rendering as a black button
* Fixed - FPX "return_url" required when confirming payment intent error that could occasionally occur in test mode.
* Fixed - Invalid argument supplied for foreach() notice if no credit card icons selected for the Credit Card Gateway
* Fixed - If a saved SEPA payment method exists on checkout page, it was being selected instead of a new account.
* Fixed - Credit card icons not rendering in IE11 browser.
= 3.3.4 =
* Fixed - Plaid charge.succeeded webhook
* Fixed - Warning message when all credit card icons are disabled on checkout page
* Added - Afterpay messaging to mini-cart
* Added - Afterpay option to hide messaging if the items in the cart are not eligible for Afterpay
* Updated - Afterpay can only process payments in the currency that maps to the Stripe account's registered country. Examples: US > USD AU > AUD. This is a requirement of Afterpay.
* Updated - Made improvements to [Cartflows](https://wordpress.org/plugins/cartflows/) integration
= 3.3.3 =
* Added - [Cartflows](https://wordpress.org/plugins/cartflows/) support added for Credit Cards, Apple Pay, and Google Pay
* Fixed - Klarna order status remaining as pending in some scenarios and checkout page would not redirect to thank you page
* Fixed - Klarna [WooCommerce Blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/) object comparison error
= 3.3.2 =
* Added - Render Clearpay logo when billing country is GB or currency is GBP
* Updated - GPay buttonSizeMode set to fill
* Fixed - Notice "handleCardAction: The PaymentIntent supplied is not in the requires_action state" if payment method uses 3DS and there are insufficient funds
= 3.3.1 =
* Added - Afterpay payment integration. Afterpay messaging can be added to the product pages, cart page, and checkout page
* Fixed - Compatibility between new WooCommerce Blocks integration, Elementor, & Divi.
* Updated - Converted P24 to use Payment Intents. Previously the integration used Sources. Stripe's API now requires that P24 use email address.
* Updated - Only load WooCommerce Blocks integration if WooCommerce Blocks is installed as a feature plugin
* Updated - Klarna disable place order button while payment options load. [Support request](https://wordpress.org/support/topic/klarna-problem-loading-frame/)
= 3.3.0 =
* Added - [WooCommerce Blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/) plugin support. All Stripe plugin payment methods are supported.
= 3.2.15 =
* Fixed - Setup intent confirmation error if order contains subscription trail period and checkout fields fail validation.
* Fixed - CheckoutWC plugin compatibility on checkout page load.
* Added - Shortcode [wc_stripe_payment_buttons] for payment buttons so they can be rendered anywhere on product or cart pages.
= 3.2.14 =
* Added - Klarna pink background image
* Added - Klarna locale prevent unsupported formats. Example: convert de-DE-formal to de-DE
* Added - Klarna support for Spain, Belgium, and Italy
* Added - Filter wc_stripe_klarna_get_required_parameters so available country and currencies can be  customized
* Added - Filter wc_stripe_get_card_custom_field_options so attributes like placeholder can be added to custom forms
* Fixed - Payment Wallets: split full name by spaces and pass last value as last name. Prevents issue where some users have name entered as Mr. John Smith and order name comes out as Mr John.
= 3.2.13 =
* Added - Save new payment method to subscription when failed renewal order is paid for.
* Added - Permission check "manage_woocommerce" added to Edit Product page for rendering Stripe settings
* Added - If shipping phone field exists and is empty, populate using GPay/Apple Pay phone number returned
* Updated - WC Tested up to 5.0
* Updated - Stripe PHP SDK 7.72.0
* Updated - Fix change_payment_method request logic so it's processed in the process_payment function
* Updated - Adjust cart page buttons so last button doesn't have bottom margin
* Updated - Performance improvement on checkout page when saving a payment method and processing order
* Updated - Don't show payment method buttons on external products
= 3.2.12 =
* Added - billing_details property to local payment methods
* Added - Improved support for multisite. Replaced use of get_user_meta with get_user_option
* Added - Filter wc_stripe_get_customer_id
* Added - Filter wc_stripe_save_customer
* Added - Stripe token object added to filter wc_stripe_get_token_formats
* Added - Payment method formats (Credit Card, Apple Pay, Google Pay)
* Added - Added address property to customer create and update API call
* Added - New GPay rounded corners icon
* Added - Additional credit card form error messages for translation.
* Added - Guest checkout for Pre-Order products where payment method must be saved.
* Updated - Removed stripe customer Id from metadata since it's redundant. Customer ID is already associated with charge object
* Updated - Admin Pay for order - display message if order is not created or doesn't have pending payment status
= 3.2.11 =
* Added - WC tested up to: 4.9.0
* Added - Compatibility for Cartflows redirect to global checkout when local payment method used.
* Added - Refund webhook: if refunded total equals order total, re-stock product(s)
* Fixed - Apple Pay and Payment Request button disabled on product page when variable product has multiple variations
* Fixed - 3DS issue on order pay page if credit card gateway not pre-selected
* Fixed - Setup intent not created if 100% coupon entered on checkout page for subscription
* Updated - If any required fields missing on product or cart page checkout, redirect to checkout page where customer can enter required fields then click Place Order
= 3.2.10 =
* Added - Refund webhook support so refunds created in Stripe dashboard sync with WooCommerce store
* Added - Option to control when local payment methods are visible on checkout page based on billing country
* Added - wc_stripe_refund_args filter
* Added - Support for SEPA subscription amount changes
* Added - SEPA to subscription customer change payment method page
* Added - Filter so frontend error html can be customized
* Added - Authorize option for Klarna payments so they can be authorized or captured
* Added - Handle SCA for subscriptions with free trial
* Fixed - Sorting of Apple Pay payment methods in Apple Wallet
* Updated - GPay gateway converted to payment intents for SCA.
* Updated - Credit card form moved Saved Card label to right of checkbox
= 3.2.9 =
* Updated - WP tested to 5.6
* Updated - Tested with PHP 8
* Added - GrabPay gateway
* Added - Promise polyfill for older browsers
* Added - GPay added SCA required fields (https://developers.google.com/pay/api/web/guides/resources/sca)
* Added - Order button text option for local payment methods
* Added - new filter wc_stripe_force_save_payment_method
= 3.2.8 =
* Updated - Changed function wc_stripe to stripe_wc because WooCommerce Stripe Payment Gateway introduced a function with same name in version 4.5.4 which caused a fatal error.
* Updated - Apple Pay and GPay - if billing address already populated, don't request it in wallet.
* Updated - Convert long version of state/province for GPay to abbreviation. California = CA
* Updated - Updated Klarna checkout flow for improved user experience.
= 3.2.7 =
* Added - Better support for mixed cart/checkout page using Elementor
* Added - wc_stripe_save_order_meta action added so custom data can be added to order
* Added - Improved support for WooCommerce Multilingual
= 3.2.6 =
* Fixed - Apple Pay duplicate button in express checkout
* Added - Transaction url in order details
* Added - Check for existence of WC queue when loading
= 3.2.5 =
* Added - Support for right to left (RTL) languages
* Added - WPML gateway country availability compatibility
* Added - WPML product page currency change
* Fixed - Change event for ship_to_different_address
* Fixed - Update required fields on checkout page load
= 3.2.4 =
* Fixed - Payment request button disappearing on variable product page
* Updated - Only validate visible fields on checkout page
* Update - WC tested up to 4.6.0
* Added - SEPA WooCommerce Subscriptions support
* Added - Autofocus on custom credit card forms
= 3.2.3 =
* Fixed - 3DS pop up on order pay page
* Fixed - One time use coupon error when 3DS triggered on checkout page
* Fixed - Formatting in class-wc-stripe-admin-notices.php
* Added - Apple Pay, GPay, Payment Request, do not request shipping address or shipping options on checkout page if customer has already filled out shipping fields
= 3.2.2 =
* Fixed - 403 for logged out user when link-token fetched on checkout page
* Added - Payment method format for GPay. Example: Visa 1111 (Google Pay)
* Added - Filter for product and cart page checkout so 3rd party plugins can add custom fields to checkout process
* Updated - Stripe PHP lib version to 7.52.0
= 3.2.1 =
* Updated - Plaid Link integration to use new Link Token
* Updated - Convert state long name i.e. Texas = TX in case address is not abbreviated in wallet
* Updated - On checkout page, only request phone and email in Apple Pay and GPay if fields are empty
* Fixed - Issue where JS error triggered if cart/checkout page combined using Elementor
* Fixed - Apple Pay and Payment Request wallet requiring shipping address for variable virtual products
= 3.2.0 =
* Fixed - Conflict with Checkout Field Editor for WooCommerce and JS checkout field variable
* Fixed - Mini-cart html
* Fixed - SEPA JS error on checkout page
* Added - WC tested to 4.4.1
* Updated - removed selectWoo as form-handler.js dependency
= 3.1.9 =
* Fixed - WP 5.5 rest permission_callback notice
* Fixed - Conflict with SG Optimizer plugin
* Fixed - Conflict with https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/ button on checkout page
= 3.1.8 =
* Fixed - Do not redirect to order received page if customer has not completed local payment process
* Fixed - Disable Apple Pay button on variation product when no default product is selected
* Added - Mini-cart integration for GPay and Apple Pay.
* Added - Filter wc_stripe_get_source_args added
* Added - Email validation for local payment methods
* Added - WC tested up to: 4.3.2
* Updated - Stripe PHP lib version 7.45.0
= 3.1.7 =
* Fixed - SEPA payment flow
* Added - BECS payment method
* Updated - Stripe php lib version 7.40.0
* Updated - AliPay display logic
= 3.1.6 = 
* Updated - WC tested to 4.3.0
* Updated - Bumped PHP min version to 5.6
* Updated - Stripe php lib version 7.39.0
* Updated - Apple domain registration check for existing domain
* Fixed - Notice on cart page when payment request button active and cart emptied
* Fixed - Google Pay fee line item in wallet
* Added - New filters for API requests
= 3.1.5 = 
* Fixed - Capture type on product page checkout
* Fixed - WP 5.4.2 deprecation message for namespaces
* Fixed - PHP 7.4 warning message
* Updated - Error message API uses $.scroll_to_notices when available
* Updated - Apple Pay and Payment Request require phone and email based on required billing fields
* Updated - Webkit autofill text color
* Added - Non numerical check to wc_stripe_add_number_precision function
= 3.1.4 = 
* Updated - WC 4.2.0 support
* Added - Validation for local payment methods to ensure payment option cannot be empty
* Added - Account ID to Stripe JS initialization
* Added - Local payment method support for manual subscriptions
* Fixed - Exception that occurs after successful payment of a renewal order retry. 
= 3.1.3 = 
* Added - WC 4.1.1 support
* Added - Klarna payment categories option
* Added - Order ID filter added
* Added - Local payment button filter so text on buttons can be changed
* Update - Webhook controller returns 401 for failed signature
= 3.1.2 = 
* Added - Merchants can now control which payment buttons appear for each product and their positioning
* Added - VAT tax display for Apple Pay, GPay, Payment Request
* Added - Optional Stripe email receipt
* Updated - Stripe API version to 2020-03-02
* Fixed - iDEAL not redirecting on order pay page.
= 3.1.1 = 
* Fixed - Error when changing WCS payment method to new ACH payment method
* Fixed - Error when payment_intent status 'success' and order cancelled status applied
* Added - Recipient email for payment_intent
* Added - Translations for credit card decline errors
* Added - Option to force 3D secure for all transactions
* Added - Option to show generic credit card decline error
* Added - SEPA mandate message on checkout page
* Updated - Google Pay documentation
= 3.1.0 = 
* Added - FPX payment method
* Added - Alipay payment method
* Updated - Stripe connect integration
* Updated - WeChat support for other countries besides CN
* Updated - CSS so prevent theme overrides
* Fixed - WeChat QR code
= 3.0.9 = 
* Added - Payment methods with payment sheets like Apple Pay now show order items on order pay page instead of just total.
* Fixed - Error if 100% off coupon is used on checkout page.
= 3.0.8 = 
* Updated - billing phone and email check added for card payment
* Updated - template checkout/payment-method.php name changed to checkout/stripe-payment-method.php
* Updated - cart checkout button styling
* Added - Connection test in admin API settings
* Misc - WC 3.9.1
= 3.0.7 = 
* Added - WPML support for gateway titles and descriptions
* Added - ACH fee option
* Added - Webhook registration option in Admin
* Updated - Cart one click checkout buttons
* Updated - WC 3.9
= 3.0.6 = 
* Added - ACH subscription support
* Updated - Top of checkout styling
* Updated =Positioning of cart buttons. They are now below cart checkout button
= 3.0.5 =
* Added - ACH payment support
* Added - New credit card form
* Fixed - Klarna error if item totals don't equal order total.
* Updated - API version to 2019-12-03
* Updated - Local payment logic.
= 3.0.4 =
* Added - Bootstrap form added
* Updated - WC 3.8.1
* Fixed - Check for customer object in Admin pages for local payment methods
= 3.0.3 = 
* Fixed - Check added to wc_stripe_order_status_completed function to ensure capture charge is only called when Stripe is the payment gateway for the order.
* Updated - Stripe API version to 2019-11-05
= 3.0.2 = 
* Added - Klarna payments now supported
* Added - Bancontact
* Updated - Local payments webhook
= 3.0.1 = 
* Updated - Google Pay paymentDataCallbacks in JavaScript
* Updated - Text domain to match plugin slug
* Added - Dynamic price option for Google Pay
* Added - Pre-orders support
= 3.0.0 = 
* First commit

== Upgrade Notice ==
= 3.0.0 = 