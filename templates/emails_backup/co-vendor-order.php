<?php
/**
 * Co-Vendor Order Notification Email Template
 *
 * @package wp-plugin-by-al
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Hello %s,', 'wp-plugin-by-al' ), esc_html( $co_vendor->display_name ) ); ?></p>

<p><?php esc_html_e( 'Great news! A product you co-authored has been ordered.', 'wp-plugin-by-al' ); ?></p>

<h2><?php esc_html_e( 'Order Details', 'wp-plugin-by-al' ); ?></h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Order Number:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;">#<?php echo esc_html( $order->get_order_number() ); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Order Date:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Order Status:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Order Total:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
        </tr>
    </table>
</div>

<h2><?php esc_html_e( 'Co-Authored Product', 'wp-plugin-by-al' ); ?></h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Product Name:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;">
                <?php if ( $product ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" target="_blank"><?php echo esc_html( $product->get_name() ); ?></a>
                <?php else : ?>
                    <?php esc_html_e( 'Unknown Product', 'wp-plugin-by-al' ); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Main Vendor:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo esc_html( $main_vendor ? $main_vendor->display_name : __( 'Unknown Vendor', 'wp-plugin-by-al' ) ); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Your Role:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php esc_html_e( 'Co-Author', 'wp-plugin-by-al' ); ?></td>
        </tr>
    </table>
</div>

<h2><?php esc_html_e( 'Customer Information', 'wp-plugin-by-al' ); ?></h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Customer:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Email:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo esc_html( $order->get_billing_email() ); ?></td>
        </tr>
        <tr>
            <th class="td" scope="row" style="text-align: left;"><?php esc_html_e( 'Phone:', 'wp-plugin-by-al' ); ?></th>
            <td class="td" style="text-align: left;"><?php echo esc_html( $order->get_billing_phone() ); ?></td>
        </tr>
    </table>
</div>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
