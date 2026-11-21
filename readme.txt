=== EU2673 Withdrawal Button ===
Contributors: adaptatuweb
Author: Álvaro Martínez - AdaptaTuWeb.com
Tags: withdrawal, woocommerce, ecommerce, eu-directive, desistimiento
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.2.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Withdrawal function for WooCommerce (Art. 11a, Directive 2011/83/EU, added by Directive 2023/2673). For customers with or without an account.

== Description ==

**EU2673 Withdrawal Button** adds the online withdrawal function required by Article 11a of Directive 2011/83/EU (introduced by Directive (EU) 2023/2673) to your WooCommerce store. Customers confirm their withdrawal in a few clicks and receive an acknowledgement of receipt by email.

Article 11a applies from **19 June 2026** to distance contracts concluded through an online interface with consumers in the European Union.

= How it works =

1. The button "Withdraw from contract here" appears in "My Account → order details", on the order received page and on a public withdrawal page for customers without an account
2. It stays available during the whole withdrawal period (14 calendar days or more, counted from delivery or from the order date)
3. The customer reviews their name, order and email, and presses "Confirm withdrawal" (an optional reason field can be enabled)
4. The request is recorded and appears in Withdrawal → Requests
5. The customer receives an acknowledgement of receipt with the date and time
6. You receive a notification with a direct link to the order
7. You change the status of the request; the customer is notified by email

= Included features =

* ✅ Withdrawal button on order details and the order received page, labelled with the wording of the Directive in each language
* ✅ Confirmation window showing the customer's name, order and email (Art. 11a(2) and (3))
* ✅ Acknowledgement of receipt to the customer with date and time (Art. 11a(4))
* ✅ Notification email to the store
* ✅ Request panel with status filters; status update emails with an optional note
* ✅ **Public withdrawal page** created on activation — shortcode [eu2673_withdrawal_form] for customers without an account
* ✅ Optional links to the withdrawal page at the bottom of the store and in WooCommerce order emails
* ✅ **Model withdrawal form (Annex I.B)** pre-filled and printable
* ✅ **Product-level withdrawal status** — Standard / Excluded (Art. 16) / Digital content consent (Art. 16(m))
* ✅ **Digital content consent** checkbox in the classic checkout and the checkout block
* ✅ Deadline counted in calendar days from delivery or from the order date (minimum 14 days, optional grace days)
* ✅ Optional automatic order status change (never to Refunded)
* ✅ Compatible with High-Performance Order Storage (HPOS)
* ✅ Available in 16 languages: ES, EN, FR, DE, IT, PT, NL, PL, RO, SV, CA, EU, GL, AR, ZH, JA

= Public withdrawal page for guests =

The plugin creates a page with the shortcode **[eu2673_withdrawal_form]** when you activate it (on existing stores, a notice offers to create it). Customers without an account withdraw there by entering their order number and purchase email — no login required.

A link to this page can be shown at the bottom of every page and in the WooCommerce order emails, so the function stays visible and available during the whole period.

= Pro version =

The **[Pro version (€39, one-time payment)](https://adaptatuweb.com/withdrawal-button-es/)** adds:

* 📄 **Legal PDF** with official acknowledgement attached to the customer email
* 🔐 **SHA-256 hash** verification for legal proof in case of disputes
* 🔄 **Reverse automation** — the request status syncs automatically with the WooCommerce order
* 📊 **Legal email traceability** of all emails sent with exact date and time
* 🛡️ **Anti-fraud system** — detects abuse patterns by email, IP and systematic behaviour, with risk alerts for the administrator
* 🔔 **Automatic updates** from the WordPress dashboard

= Legal basis =

* **Directive 2011/83/EU**, Article 11a (withdrawal function), inserted by **Directive (EU) 2023/2673**
* Directive 2011/83/EU, Articles 9 (withdrawal period), 13 (refund) and 16 (exceptions), and Annex I.B (model withdrawal form)
* Spain: Art. 102 and 107 TRLGDCU

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the plugin to `/wp-content/plugins/eu2673-withdrawal-button/` or install it directly from the WordPress repository.
2. Activate the plugin from the WordPress "Plugins" menu.
3. WooCommerce must be installed and active.
4. Go to **Withdrawal → Settings** to adjust the options for your store.
5. Check that the public withdrawal page exists (Withdrawal → Settings → Public withdrawal page).

== Frequently Asked Questions ==

= Is this plugin mandatory from June 19, 2026? =

From 19 June 2026, Article 11a of Directive 2011/83/EU (inserted by Directive (EU) 2023/2673) requires traders that conclude distance contracts through an online interface to offer a withdrawal function. This plugin implements that function for WooCommerce. It does not replace legal advice about your store.

= Does it work with guest orders (no account)? =

Yes. Guests can use the button on the order received page, or the public withdrawal page by entering their order number and purchase email — no login required.

= Is the customer required to provide a reason? =

No. The law does not require the consumer to justify the withdrawal. You can enable an optional reason field in the settings; it is never mandatory.

= Can I change the 14-day deadline? =

You can set a longer period or add grace days. It cannot be set below 14 calendar days, the legal minimum.

= When do the 14 days start? =

For goods, from delivery: choose "Delivery date" and mark orders as Completed when they are delivered; until then the button stays available. For services and digital content, choose the order date. The period ends at the end of the last calendar day, in the store time zone.

= What is the difference between Lite and Pro? =

The Lite version implements the withdrawal function. The Pro version adds a PDF acknowledgement, a SHA-256 hash of each request, an email log, order status sync and anti-fraud alerts. More information at [adaptatuweb.com/withdrawal-button-es](https://adaptatuweb.com/withdrawal-button-es/).

= Does the digital content consent work in the checkout block? =

Yes. In recent WooCommerce versions the checkbox only appears, and is only required, when the cart contains digital content. Older versions cannot show checkout fields conditionally: there the checkbox is always visible but optional, and the order is only refused when the cart contains digital content and the box is not ticked.

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

= 1.2.4 =
* Fixed: unticking "Show the button on the order received page" did not hide it, because the order details on that page also showed the button
* Fixed: while no withdrawal page existed, every page view searched the whole site for it; the search now runs at most once an hour, or when a page is saved
* Fixed: the order number field of the guest form opened the numeric keyboard on phones, which made order numbers with letters hard to type
* Readme: explains how the digital content checkbox behaves in older WooCommerce versions

= 1.2.3 =
* Fixed: the Requests page in the admin was always empty (it read a table that does not exist)
* Fixed: changing the status of a request did nothing, so status emails were never sent; status changes now work, add an order note and email the customer with an optional note
* Fixed: the Settings page saved options the plugin never read (deadline, automatic status change); all settings now take effect
* Fixed: orders with status Completed had no withdrawal button
* Fixed: "Delivery date" deadline depended on the language of an order note; it now uses the completion date, and the button stays available until the order is delivered
* Fixed: the deadline is counted in calendar days and ends at the end of the last day
* Fixed: "My withdrawals" showed only the requests of one order
* Fixed: "Print this form" opened the home page; it now prints the model form
* Fixed: the guest form put the customer's email in the URL and could not show errors; it now uses POST and shows errors on the page
* Fixed: logged-in customers could not withdraw orders placed as guests
* Fixed: the confirmation window was printed up to three times on the guest page
* Fixed: the digital content consent checkbox did not appear in the WooCommerce checkout block
* Fixed: "Excluded" products did not hide the button; an order is now excluded when all its products are excluded
* Fixed: store links in the admin email did not work with HPOS
* The confirmation window shows the customer's name, order and email before confirming (Art. 11a(2))
* The acknowledgement of receipt always goes to the customer (Art. 11a(4)) and includes name and email
* Default button text is the wording of the Directive ("Withdraw from contract here") in each language
* A public withdrawal page is created on activation; optional links in the footer and in WooCommerce order emails
* Model withdrawal form (Annex I.B) now lists the products and includes the signature and date lines
* Emails use the WooCommerce sender name and address; the store notification can be answered directly to the customer
* Optional reason field (never mandatory)
* Admin pages rewritten: labelled fields, accurate help, translated into all bundled languages

= 1.2.2 =
* Fixed: customer acknowledgement email and shop notification email were always in Spanish; they now follow the store language
* Fixed: "Processing..." message and email subjects were not translated
* Fixed: dates in emails mixed languages (e.g. "21 de November de 2026"); they now use the store date format and language
* Fixed: guest customers (no account) could not submit the withdrawal from the guest form or the thank-you page
* Fixed: status update emails sent from the admin now use the store language, not the admin user language
* Fixed: settings migration now also runs on stores with WooCommerce active
* Regional locales (fr_BE, fr_CA, de_AT, es_MX...) now use the base language translation
* Legal basis line now cites Article 11a of Directive 2011/83/EU; the Spanish TRLGDCU reference is shown only for stores based in Spain
* Completed customer-facing translations in all bundled languages and added Spanish and English translation files
* Model withdrawal form (Annex I.B) uses the official wording of each EU language
* Removed an old copy of version 1.1.4 that was included by mistake inside the plugin

= 1.2.1 =
* Fixed automatic order status change to Refunded regardless of saved settings

= 1.2.0 =
* Fixed settings migration running too late — now runs immediately on plugin load
* Automatic order status change is now disabled on all existing installations

= 1.1.9 =
* Fixed automatic order status change persisting after update — now migrates existing settings on plugin load
* Changed default order status from Refunded to On Hold
* Changed default for automatic status change to disabled

= 1.1.8 =
* Fixed order status default changed from Refunded to On Hold to prevent automatic payment refund
* Fixed missing translations for button status and error messages in all languages

= 1.1.7 =
* Added missing translations for modal texts in all supported languages (FR, DE, IT, NL, PT, PL, RO, SV, CA, EU, GL)

= 1.1.6 =
* Fixed translations not loading on frontend (French and other languages now display correctly in the modal)

= 1.1.5 =
* Fixed translations not loading on frontend (French and other languages now display correctly in the modal)

= 1.1.4 =
* Fixed critical error on Help page (incorrect file path)

= 1.1.3 =
* Added review request notice after 14 days of active use
* Added Pro upsell notice in the requests panel
* Updated Tested up to 7.1

= 1.1.2 =
* Fixed short description length for WordPress.org

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

= 1.2.4 =
Small fixes: the order received page setting now works, and stores without a withdrawal page no longer run a search on every page view.

= 1.2.3 =
The request panel, status emails and settings now work. Completed orders show the withdrawal button again. Check Withdrawal → Settings after updating.

= 1.2.2 =
Customer emails now follow the store language and guest customers can submit withdrawals again. Recommended update.

= 1.1.0 =
Adds public withdrawal form for guests ([eu2673_withdrawal_form] shortcode), product exclusions and digital consent checkbox.

= 1.0.0 =
First release. Thank you for installing EU2673 Withdrawal Button!
