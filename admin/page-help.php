<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap eu2673-wrap">
<h1><?php esc_html_e( 'Withdrawal Button — Help', 'eu2673-withdrawal-button' ); ?></h1>

<div class="eu2673-help-grid">

    <section class="eu2673-help-card">
        <h2><?php esc_html_e( 'What the law requires', 'eu2673-withdrawal-button' ); ?></h2>
        <p><?php esc_html_e( 'Article 11a of Directive 2011/83/EU, added by Directive (EU) 2023/2673, applies from 19 June 2026 to distance contracts concluded through an online interface. The store must offer a withdrawal function that:', 'eu2673-withdrawal-button' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'is labelled "withdraw from contract here" or an equivalent unambiguous wording, and is prominently displayed and available throughout the withdrawal period;', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'lets the consumer provide or confirm their name, the contract and the email where the confirmation will be sent;', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'asks for confirmation with a button labelled "confirm withdrawal" or equivalent;', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'sends an acknowledgement of receipt on a durable medium with the content, date and time of the withdrawal.', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </section>

    <section class="eu2673-help-card">
        <h2><?php esc_html_e( 'Where customers find the button', 'eu2673-withdrawal-button' ); ?></h2>
        <ul>
            <li><?php esc_html_e( 'My account → order details, and the My withdrawals section.', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'The order received page, right after buying.', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'The public withdrawal page, for customers without an account: they enter their order number and email.', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Links to that page at the bottom of the store and in the WooCommerce order emails (optional).', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
        <p><?php esc_html_e( 'The button disappears when the period ends, when the order has an excluded status, or when every product in the order is marked as excluded.', 'eu2673-withdrawal-button' ); ?></p>
    </section>

    <section class="eu2673-help-card">
        <h2><?php esc_html_e( 'What happens when the customer withdraws', 'eu2673-withdrawal-button' ); ?></h2>
        <ul>
            <li><?php esc_html_e( 'A window shows their name, order and email, and they confirm.', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'The request is saved and appears in Withdrawal → Requests.', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'The customer receives the acknowledgement of receipt; the store receives a notification.', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'The order is not refunded automatically. Check it and refund it in WooCommerce.', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </section>

    <section class="eu2673-help-card">
        <h2><?php esc_html_e( 'Managing requests', 'eu2673-withdrawal-button' ); ?></h2>
        <p><?php esc_html_e( 'Change the status of each request in Withdrawal → Requests. If status emails are enabled, the customer receives the new status and your note.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php esc_html_e( 'The trader must refund the payments received without undue delay and no later than 14 days from the day it is informed of the withdrawal; for goods, it may withhold the refund until it has received them back or the consumer has supplied evidence of having sent them (Art. 13 of Directive 2011/83/EU).', 'eu2673-withdrawal-button' ); ?></p>
    </section>

    <section class="eu2673-help-card">
        <h2><?php esc_html_e( 'Products without a right of withdrawal', 'eu2673-withdrawal-button' ); ?></h2>
        <p><?php esc_html_e( 'In each product, General tab, "Withdrawal status": mark products covered by an Article 16 exception as excluded, and digital content as requiring consent at checkout. The consent checkbox works in both the classic checkout and the checkout block.', 'eu2673-withdrawal-button' ); ?></p>
    </section>

    <section class="eu2673-help-card">
        <h2><?php esc_html_e( 'Emails', 'eu2673-withdrawal-button' ); ?></h2>
        <p><?php esc_html_e( 'Emails are sent with the sender name and address configured in WooCommerce → Settings → Emails. For reliable delivery, use an SMTP plugin with a real mail server.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php esc_html_e( 'Support for the free version: the WordPress.org support forum.', 'eu2673-withdrawal-button' ); ?></p>
    </section>

</div>
</div>
