<?php
/**
 * Co-Vendor Order Notification Email Template (Plain Text)
 *
 * @package wp-plugin-by-al
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

printf( esc_html__( 'Hello %s,', 'wp-plugin-by-al' ), esc_html( $co_vendor->display_name ) );
echo "\n\n";

esc_html_e( 'Great news! A product you co-authored has been ordered.', 'wp-plugin-by-al' );
echo "\n\n";

echo esc_html__( 'Order Details', 'wp-plugin-by-al' );
echo "\n";

echo esc_html__( 'Order Number:', 'wp-plugin-by-al' ) . ' #' . esc_html( $order->get_order_number() ) . "\n";
echo esc_html__( 'Order Date:', 'wp-plugin-by-al' ) . ' ' . esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . "\n";
echo esc_html__( 'Order Status:', 'wp-plugin-by-al' ) . ' ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . "\n";
echo esc_html__( 'Order Total:', 'wp-plugin-by-al' ) . ' ' . wp_strip_all_tags( $order->get_formatted_order_total() ) . "\n\n";

echo esc_html__( 'Co-Authored Product', 'wp-plugin-by-al' );
echo "\n";

if ( $product ) {
    echo esc_html__( 'Product Name:', 'wp-plugin-by-al' ) . ' ' . esc_html( $product->get_name() ) . "\n";
    echo esc_html__( 'Product URL:', 'wp-plugin-by-al' ) . ' ' . esc_url( get_permalink( $product->get_id() ) ) . "\n";
} else {
    echo esc_html__( 'Product Name:', 'wp-plugin-by-al' ) . ' ' . esc_html__( 'Unknown Product', 'wp-plugin-by-al' ) . "\n";
}

echo esc_html__( 'Main Vendor:', 'wp-plugin-by-al' ) . ' ' . esc_html( $main_vendor ? $main_vendor->display_name : __( 'Unknown Vendor', 'wp-plugin-by-al' ) ) . "\n";
echo esc_html__( 'Your Role:', 'wp-plugin-by-al' ) . ' ' . esc_html__( 'Co-Author', 'wp-plugin-by-al' ) . "\n\n";

echo esc_html__( 'Customer Information', 'wp-plugin-by-al' );
echo "\n";

echo esc_html__( 'Customer:', 'wp-plugin-by-al' ) . ' ' . esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) . "\n";
echo esc_html__( 'Email:', 'wp-plugin-by-al' ) . ' ' . esc_html( $order->get_billing_email() ) . "\n";
echo esc_html__( 'Phone:', 'wp-plugin-by-al' ) . ' ' . esc_html( $order->get_billing_phone() ) . "\n\n";

echo "\n----------------------------------------\n\n";

if ( $additional_content ) {
    echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
    echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
