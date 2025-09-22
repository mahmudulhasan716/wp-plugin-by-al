<?php

namespace WeDevs\Dokan\Emails;

use WC_Email;

/**
 * Co-Vendor Order Notification Email.
 *
 * An email sent to co-vendors when their co-authored products are ordered.
 *
 * @class       Dokan_Email_Co_Vendor_Order
 * @version     1.0.0
 * @author      wp-plugin-by-al
 * @extends     WC_Email
 */
class CoVendorOrder extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'co_vendor_order';
        $this->title          = __( 'Dokan Co-Vendor Order Notification', 'wp-plugin-by-al' );
        $this->description    = __( 'Co-Vendor Order emails are sent to co-vendors when their co-authored products are ordered.', 'wp-plugin-by-al' );
        $this->template_html  = 'emails/co-vendor-order.php';
        $this->template_plain = 'emails/plain/co-vendor-order.php';
        $this->template_base  = DOKAN_DIR . '/templates/';
        $this->placeholders   = [
            '{order_number}'     => '',
            '{order_date}'       => '',
            '{order_status}'     => '',
            '{order_total}'      => '',
            '{product_title}'    => '',
            '{product_url}'      => '',
            '{main_vendor_name}' => '',
            '{co_vendor_name}'   => '',
            '{customer_name}'    => '',
            '{customer_email}'   => '',
            '{customer_phone}'   => '',
            '{site_name}'        => $this->get_from_name(),
            '{site_url}'         => '',
        ];

        // Triggers for this email
        add_action( 'dokan_co_vendor_order_notification', array( $this, 'trigger' ), 30, 3 );

        // Call parent constructor
        parent::__construct();

        // Other settings
        $this->recipient = 'co-vendor@ofthe.product';
    }

    /**
     * Get email subject.
     *
     * @since  1.0.0
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] Co-Author Product Order Notification - Order #{order_number}', 'wp-plugin-by-al' );
    }

    /**
     * Get email heading.
     *
     * @since  1.0.0
     * @return string
     */
    public function get_default_heading() {
        return __( 'Co-Author Product Order Notification', 'wp-plugin-by-al' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @since 1.0.0
     *
     * @param object $co_vendor Co-vendor user object
     * @param object $order WooCommerce order object
     * @param int    $product_id Product ID
     */
    public function trigger( $co_vendor, $order, $product_id ) {
        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            $this->log( 'Co-vendor order email is disabled or no recipient set.' );
            return;
        }

        $this->setup_locale();

        $product = wc_get_product( $product_id );
        $main_vendor = get_userdata( $order->get_user_id() );

        $this->object = (object) [
            'co_vendor'     => $co_vendor,
            'order'         => $order,
            'product'       => $product,
            'main_vendor'   => $main_vendor,
        ];

        $this->placeholders['{order_number}']     = $order->get_order_number();
        $this->placeholders['{order_date}']       = $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
        $this->placeholders['{order_status}']     = wc_get_order_status_name( $order->get_status() );
        $this->placeholders['{order_total}']      = $order->get_formatted_order_total();
        $this->placeholders['{product_title}']    = $product ? $product->get_name() : __( 'Unknown Product', 'wp-plugin-by-al' );
        $this->placeholders['{product_url}']      = $product ? get_permalink( $product->get_id() ) : '#';
        $this->placeholders['{main_vendor_name}'] = $main_vendor ? $main_vendor->display_name : __( 'Unknown Vendor', 'wp-plugin-by-al' );
        $this->placeholders['{co_vendor_name}']   = $co_vendor->display_name;
        $this->placeholders['{customer_name}']    = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $this->placeholders['{customer_email}']   = $order->get_billing_email();
        $this->placeholders['{customer_phone}']   = $order->get_billing_phone();
        $this->placeholders['{site_url}']        = home_url();

        $this->send( $co_vendor->user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'order'         => $this->object->order,
                'co_vendor'     => $this->object->co_vendor,
                'product'       => $this->object->product,
                'main_vendor'   => $this->object->main_vendor,
                'email_heading' => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin' => false,
                'plain_text'    => false,
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Get content plain.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'order'         => $this->object->order,
                'co_vendor'     => $this->object->co_vendor,
                'product'       => $this->object->product,
                'main_vendor'   => $this->object->main_vendor,
                'email_heading' => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin' => false,
                'plain_text'    => true,
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Initialise settings form fields.
     *
     * @since 1.0.0
     */
    public function init_form_fields() {
        $placeholder_text = sprintf( __( 'Available placeholders: %s', 'wp-plugin-by-al' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );

        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __( 'Enable/Disable', 'wp-plugin-by-al' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'wp-plugin-by-al' ),
                'default' => 'yes',
            ),
            'recipient'          => array(
                'title'       => __( 'Recipient(s)', 'wp-plugin-by-al' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'wp-plugin-by-al' ), esc_attr( get_option( 'admin_email' ) ) ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'wp-plugin-by-al' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email heading', 'wp-plugin-by-al' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'wp-plugin-by-al' ),
                'description' => __( 'Text to appear below the main email content.', 'wp-plugin-by-al' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'wp-plugin-by-al' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type'         => array(
                'title'       => __( 'Email type', 'wp-plugin-by-al' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'wp-plugin-by-al' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Get default additional content.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_default_additional_content() {
        return __( 'This notification is sent to keep you informed about orders for products you\'ve co-authored.', 'wp-plugin-by-al' );
    }
}
