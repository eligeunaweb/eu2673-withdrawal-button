# EU2673 Withdrawal Button

Withdrawal function for **WooCommerce**, as required by **Article 11a of Directive 2011/83/EU**, inserted by **Directive (EU) 2023/2673**. Article 11a applies from **19 June 2026** to distance contracts concluded through an online interface with consumers in the European Union.

- **Install from WordPress.org:** <https://wordpress.org/plugins/eu2673-withdrawal-button/>
- **Current version:** 1.2.4 (identical to the code published on WordPress.org)
- **Requirements:** WordPress 5.8+, WooCommerce 6.0+, PHP 7.4+
- **License:** GPL-2.0-or-later

## What it does

- Shows a **"Withdraw from contract here"** button, using the wording of the Directive in each language, in *My account → order details*, on the order received page and on a public withdrawal page for customers without an account.
- Keeps the button available during the whole withdrawal period. The period is counted in calendar days from delivery (the order is marked as Completed) or from the order date. It is never shorter than 14 days.
- Before confirming, the customer sees their **name, order and email** (Art. 11a(2)), then presses **"Confirm withdrawal"** (Art. 11a(3)).
- Sends the customer an **acknowledgement of receipt** with the content, date and time of the withdrawal (Art. 11a(4)), and notifies the store.
- Lets the store manage requests in *Withdrawal → Requests*. Status changes (approved, rejected, completed) are emailed to the customer with an optional note.
- Creates the **public withdrawal page** on activation. Optional links to it are added at the bottom of the store and in the WooCommerce order emails.
- Shows a pre-filled, printable **model withdrawal form** (Annex I.B), using the official wording in each EU language.
- Adds a per-product **withdrawal status**: Standard, Excluded (Art. 16 exceptions) or Digital content. Digital content shows a mandatory consent checkbox in both the classic checkout and the checkout block.
- Never refunds automatically. An automatic order status change is optional and never sets the order to Refunded.
- Is compatible with High-Performance Order Storage (HPOS).
- Is translated into 16 languages: ES, EN, FR, DE, IT, PT, NL, PL, RO, SV, CA, EU, GL, AR, ZH, JA. Catalan, Basque, Galician, Arabic, Japanese and Chinese have no official EU text, so they are the author's translations.

This plugin implements the withdrawal function. It does not replace legal advice about your store.

## Repository layout

The repository root is the plugin folder, the same as the `trunk` published on WordPress.org. To try it, copy it to `wp-content/plugins/eu2673-withdrawal-button/`, or install the ZIP from WordPress.org.

## Changes

See the changelog in [`readme.txt`](readme.txt).

## Pro version

A paid Pro version adds a PDF acknowledgement, a SHA-256 hash of each request, an email log, order status sync and anti-fraud alerts: <https://adaptatuweb.com/withdrawal-button-es/>.

## Support

Use the [WordPress.org support forum](https://wordpress.org/support/plugin/eu2673-withdrawal-button/).
