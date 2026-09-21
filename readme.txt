=== EU2673 Withdrawal Button ===
Contributors: adaptatuweb
Author: Álvaro Martínez - AdaptaTuWeb.com
Tags: withdrawal, woocommerce, ecommerce, eu-directive, desistimiento
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.1.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Legal withdrawal button for WooCommerce. EU Directive 2023/2673 compliant. Works for registered customers and guests. 1 click, automatic acknowledgement.

== Description ==

**EU2673 Withdrawal Button** adds a legal withdrawal button to your WooCommerce store, allowing customers to exercise their right of withdrawal in 1 click and receive an automatic acknowledgement email.

**EU Directive 2023/2673** is mandatory from **June 19, 2026** for all online stores selling to consumers in the European Union.

= How it works =

1. The button appears on the order confirmation page and in "My Account → order detail"
2. It is only shown within the legal period of 14 calendar days
3. The customer clicks the button, confirms in a modal and optionally provides a reason
4. The request is recorded in your database
5. The customer receives an acknowledgement email
6. You receive a notification with a direct link to the order
7. The order status changes automatically (configurable)

= Included features =

* ✅ Withdrawal button on order detail and thank-you page
* ✅ Modal with mandatory legal text
* ✅ Acknowledgement email to the customer
* ✅ Notification email to the administrator
* ✅ Status update emails (approved, rejected, completed)
* ✅ Request management panel with filters and CSV export
* ✅ Automatic order status change (configurable)
* ✅ Compatible with guest orders (guest checkout)
* ✅ **Public withdrawal page** — shortcode [eu2673_withdrawal_form] for customers without an account
* ✅ **Product-level withdrawal status** — Standard / Excluded (Art. 16) / Digital consent (Art. 16m)
* ✅ **Digital content consent** — mandatory checkbox at checkout for digital/downloadable products
* ✅ **Product and category exclusions** — hide the button for specific products or entire categories
* ✅ Configurable deadline (minimum 14 calendar days)
* ✅ Optional grace days on top of the legal deadline
* ✅ Available in 16 languages: ES, EN, FR, DE, IT, PT, NL, PL, RO, SV, CA, EU, GL, AR, ZH, JA

= Public withdrawal page for guests =

Add the shortcode **[eu2673_withdrawal_form]** to any WordPress page to create a public withdrawal form. Customers without an account can exercise their right of withdrawal by entering their order number and purchase email — no login required.

Add a link to this page in your footer so it is always visible and accessible, as required by the Directive.

= Pro version =

The **[Pro version (€39, one-time payment)](https://adaptatuweb.com/withdrawal-button-es/)** adds:

* 📄 **Legal PDF** with official acknowledgement attached to the customer email
* 🔐 **SHA-256 hash** verification for legal proof in case of disputes
* 📋 **Official Annex B form** pre-filled in PDF (required by the Directive)
* 🔄 **Reverse automation** — the request status syncs automatically with the WooCommerce order
* 📊 **Legal email traceability** of all emails sent with exact date and time
* 🛡️ **Anti-fraud system** — detects abuse patterns by email, IP and systematic behaviour, with risk alerts for the administrator
* 🔔 **Automatic updates** from the WordPress dashboard
* Compatible with **PrestaShop** too

= Legal basis =

* **EU Directive 2023/2673** of the European Parliament and of the Council
* **Art. 102 TRLGDCU** — Right of withdrawal in distance contracts
* **Art. 107 TRLGDCU** — Refund within maximum 14 calendar days

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the plugin to `/wp-content/plugins/eu2673-withdrawal-button/` or install it directly from the WordPress repository.
2. Activate the plugin from the WordPress "Plugins" menu.
3. WooCommerce must be installed and active.
4. Go to **Withdrawal → Settings** to adjust the options for your store.
5. Optionally, create a page with the shortcode **[eu2673_withdrawal_form]** and add a link to it in your footer.

== Frequently Asked Questions ==

= Is this plugin mandatory from June 19, 2026? =

EU Directive 2023/2673 requires online stores selling to EU consumers to provide a clear and accessible mechanism for exercising the right of withdrawal. This plugin helps you meet that requirement in a simple and documented way.

= Does it work with guest orders (no account)? =

Yes. Guests can use the withdrawal button on the thank-you page, or use the public form [eu2673_withdrawal_form] by entering their order number and purchase email — no login required.

= Is the customer required to provide a reason? =

No. The law does not require the consumer to justify the withdrawal. The reason field is optional and configurable from the plugin settings.

= Can I change the 14-day deadline? =

You can extend it by adding optional grace days. You cannot reduce it below 14 calendar days, which is the legal minimum established by the Directive.

= When do the 14 days start? =

You can choose between the order date or the date the order moves to "Completed" status (recommended for physical products). For services and digital products always use the order date.

= What is the difference between Lite and Pro? =

The Lite version covers basic compliance. The Pro version adds the official legal PDF acknowledgement, SHA-256 verification hash, Annex B form, email traceability, reverse automation and an anti-fraud system. More information at [adaptatuweb.com/withdrawal-button-es](https://adaptatuweb.com/withdrawal-button-es/).

= Where can I get support? =

For the free version, use the WordPress.org support forum. For the Pro version, direct support at proyectos@adaptatuweb.com.

== Screenshots ==

1. Withdrawal button on the order detail page
2. Confirmation modal with legal text
3. Acknowledgement email sent to the customer
4. Request management panel in the admin area
5. Settings page
6. Help page with Lite and Pro features
7. My Account — withdrawal history
8. Status update email to the customer
9. Admin notification email
10. Pro upgrade page

== Changelog ==

= 1.1.1 =
* Added admin notice with review request and Pro upgrade prompt
* Added plugin row meta links (rate, upgrade, support)
* Added Pro upsell section in settings and requests pages

= 1.1.0 =
* Added public withdrawal form shortcode [eu2673_withdrawal_form] for guest customers (no account required)
* Added product-level withdrawal status selector (Standard / Excluded / Digital consent)
* Added mandatory digital consent checkbox at checkout for digital/downloadable products (Art. 16m)
* Added product and category exclusions
* Updated Tested up to 6.8

= 1.0.0 =
* Initial release on WordPress.org
* Withdrawal button on order detail and thank-you page
* Modal with mandatory legal text
* Acknowledgement email to customer and admin notification
* Status update emails (approved, rejected, completed)
* Request panel with filters and CSV export
* Guest checkout compatible
* Available in Spanish and English

== Upgrade Notice ==

= 1.1.0 =
Adds public withdrawal form for guests ([eu2673_withdrawal_form] shortcode), product exclusions and digital consent checkbox.

= 1.0.0 =
First release. Thank you for installing EU2673 Withdrawal Button!
