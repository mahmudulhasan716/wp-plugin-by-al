<?php

class CoAuthor {

    public function __construct() {
        // Add field to Dokan product form (frontend)
        add_action('dokan_new_product_form', array($this, 'co_author_field_in_form'), 90, 2);
        add_action('dokan_new_product_after_product_tags', array($this, 'co_author_field_in_form'));

        // Save field data when product is created or edited
        add_action('dokan_new_product_added', array($this, 'save_co_author_field'), 10, 2);
        add_action('dokan_product_updated', array($this, 'save_co_author_field'), 10, 2);
        
        // Add co-vendor dashboard menu and content
        add_filter('dokan_query_var_filter', array($this, 'add_query_var'));
        add_filter('dokan_get_dashboard_nav', array($this, 'add_co_vendor_menu'));
        add_action('dokan_load_custom_template', array($this, 'co_vendor_dashboard_content'));
        add_action('dokan_render_co_vendor_products_template', array($this, 'render_co_vendor_products_template'), 11);
        
        // Email notifications for co-vendors using Dokan's email system
        add_action('woocommerce_order_status_processing', array($this, 'send_co_vendor_order_notification'));
        add_action('woocommerce_order_status_completed', array($this, 'send_co_vendor_order_notification'));
        
        // Register Dokan email class
        add_filter('dokan_email_classes', array($this, 'register_co_vendor_email_class'));
        
        // Flush rewrite rules on activation
        add_action('init', array($this, 'maybe_flush_rewrite_rules'));
        
        // Make get_co_authored_products function available globally
        add_action('init', array($this, 'register_global_functions'));
        
    }

    /**
     * Show Co-Author field in Dokan product form
     */
    public function co_author_field_in_form($post, $post_id = 0) {
        $current_vendor_id = get_current_user_id();

        // Get all vendors except current one
        $args = array(
            'role'    => 'seller', // Dokan vendor role
            'exclude' => array($current_vendor_id),
            'orderby' => 'display_name',
            'order'   => 'ASC',
        );
        $vendors = get_users($args);

        // Get saved co-author (for edit case)
        $saved_co_author = $post_id ? get_post_meta($post_id, '_co_author', true) : '';
        ?>
        <div class="dokan-form-group">
            <label for="co_author" class="form-label">
                <?php echo esc_html__('Co-Author (Vendor)', 'wp-plugin-by-al'); ?>
            </label>
            <select name="co_author" id="co_author" class="dokan-form-control">
                <option value=""><?php esc_html_e('Select a Co-Author Vendor', 'wp-plugin-by-al'); ?></option>
                <?php foreach ($vendors as $vendor): ?>
                    <option value="<?php echo esc_attr($vendor->ID); ?>" 
                        <?php selected($saved_co_author, $vendor->ID); ?>>
                        <?php echo esc_html($vendor->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Save Co-Author vendor ID to product meta
     */
    public function save_co_author_field($product_id, $postdata) {
        if (isset($_POST['co_author']) && !empty($_POST['co_author'])) {
            update_post_meta($product_id, '_co_author', intval($_POST['co_author']));
        } else {
            delete_post_meta($product_id, '_co_author');
        }
    }

    /**
     * Add query var for co-vendor products page
     */
    public function add_query_var($query_vars) {
        $query_vars[] = 'co-vendor-products';
        return $query_vars;
    }

    /**
     * Flush rewrite rules if needed
     */
    public function maybe_flush_rewrite_rules() {
        $option_name = 'dokan_co_vendor_products_flush_rules';
        if (get_option($option_name) !== '1') {
            flush_rewrite_rules();
            update_option($option_name, '1');
        }
    }

    /**
     * Register global functions
     */
    public function register_global_functions() {
        if (!function_exists('get_co_authored_products')) {
            function get_co_authored_products($vendor_id) {
                global $co_authored_products_cache;
                
                // Check if we already have cached results for this vendor
                if (isset($co_authored_products_cache[$vendor_id])) {
                    return $co_authored_products_cache[$vendor_id];
                }
                
                $args = array(
                    'post_type'      => 'product',
                    'post_status'    => array('publish', 'draft', 'pending'),
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => '_co_author',
                            'value'   => $vendor_id,
                            'compare' => '='
                        )
                    ),
                    'orderby'        => 'date',
                    'order'          => 'DESC'
                );
                
                $products = get_posts($args);
                
                // Cache the results globally
                $co_authored_products_cache[$vendor_id] = $products;
                
                return $products;
            }
        }
    }

    /**
     * Register co-vendor email class with Dokan
     */
    public function register_co_vendor_email_class($email_classes) {
        require_once plugin_dir_path(__FILE__) . '../Emails/CoVendorOrder.php';
        $email_classes['Dokan_Email_Co_Vendor_Order'] = new \WeDevs\Dokan\Emails\CoVendorOrder();
        return $email_classes;
    }

    /**
     * Add Co-Vendor menu to Dokan dashboard
     */
    public function add_co_vendor_menu($menus) {
        $current_user_id = get_current_user_id();
        
        // Check if current user has any co-authored products
        $co_authored_products = $this->get_co_authored_products($current_user_id);
        
        // Always add the menu for testing, regardless of co-authored products
        $menus['co-vendor-products'] = array(
            'title'      => __('Co-Vendor Products', 'wp-plugin-by-al'),
            'icon'       => '<i class="fas fa-users"></i>',
            'url'        => dokan_get_navigation_url('co-vendor-products'),
            'pos'        => 35,
            'permission' => 'dokan_view_product_menu',
        );
        
        return $menus;
    }

    /**
     * Display co-vendor dashboard content
     */
    public function co_vendor_dashboard_content($query_vars) {
        if (isset($query_vars['co-vendor-products'])) {
            if (!current_user_can('dokan_view_product_menu')) {
                dokan_get_template_part('global/no-permission');
            } else {
                // Use the same structure as products page
                do_action('dokan_render_co_vendor_products_template', 'listing');
            }
        }
    }

    /**
     * Render co-vendor products template (same structure as products page)
     */
    public function render_co_vendor_products_template($action) {
        $current_user_id = get_current_user_id();
        $co_authored_products = $this->get_co_authored_products($current_user_id);
        
        // Create a custom template that mimics Dokan's products listing
        $this->render_co_vendor_products_listing($co_authored_products);
    }

    /**
     * Render co-vendor products listing (mimics Dokan's products-listing.php)
     */
    public function render_co_vendor_products_listing($co_authored_products) {
        ?>
        <?php do_action('dokan_dashboard_wrap_start'); ?>

        <div class="dokan-dashboard-wrap">
            <?php
            /**
             * Adding dokan_dashboard_content_before hook
             * @hooked get_dashboard_side_navigation
             * @since 2.4
             */
            do_action('dokan_dashboard_content_before');
            ?>

            <div class="dokan-dashboard-content dokan-product-listing">
                <?php
                /**
                 * Adding dokan_dashboard_content_inside_before hook
                 * @since 2.4
                 */
                do_action('dokan_dashboard_content_inside_before');
                do_action('dokan_before_listing_product');
                ?>

                <article class="dokan-product-listing-area">
                    <div class="dokan-product-listing-top">
                        <div class="dokan-product-listing-top-left">
                            <h3 class="dokan-product-listing-title"><?php esc_html_e('Co-Vendor Products', 'wp-plugin-by-al'); ?></h3>
                        </div>
                        <div class="dokan-product-listing-top-right">
                            <span class="dokan-product-count"><?php printf(esc_html__('Total: %d', 'wp-plugin-by-al'), count($co_authored_products)); ?></span>
                        </div>
                    </div>

                    <?php if (empty($co_authored_products)): ?>
                        <div class="dokan-no-products">
                            <div class="dokan-no-products-icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <h3><?php esc_html_e('No Co-Vendor Products', 'wp-plugin-by-al'); ?></h3>
                            <p><?php esc_html_e('You are not assigned as a co-author for any products yet.', 'wp-plugin-by-al'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="dokan-product-listing-table">
                            <form method="post" class="dokan-product-listing-form">
                                <table class="dokan-table dokan-product-listing-table">
                                    <thead>
                                        <tr>
                                            <th class="dokan-product-listing-table-checkbox">
                                                <input type="checkbox" class="dokan-check-all" />
                                            </th>
                                            <th><?php esc_html_e('Product', 'wp-plugin-by-al'); ?></th>
                                            <th><?php esc_html_e('Main Vendor', 'wp-plugin-by-al'); ?></th>
                                            <th><?php esc_html_e('Status', 'wp-plugin-by-al'); ?></th>
                                            <th><?php esc_html_e('Price', 'wp-plugin-by-al'); ?></th>
                                            <th><?php esc_html_e('Date', 'wp-plugin-by-al'); ?></th>
                                            <th><?php esc_html_e('Actions', 'wp-plugin-by-al'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($co_authored_products as $product): ?>
                                            <?php 
                                            $main_vendor = get_userdata($product->post_author);
                                            $product_obj = wc_get_product($product->ID);
                                            $tr_class = ($product->post_status === 'pending') ? 'danger' : '';
                                            ?>
                                            <tr class="<?php echo esc_attr($tr_class); ?>">
                                                <td class="dokan-product-listing-table-checkbox">
                                                    <input type="checkbox" name="bulk_product_ids[]" value="<?php echo esc_attr($product->ID); ?>" />
                                                </td>
                                                <td>
                                                    <div class="dokan-product-title">
                                                        <a href="<?php echo get_permalink($product->ID); ?>" target="_blank">
                                                            <?php echo esc_html($product->post_title); ?>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="dokan-vendor-name">
                                                        <?php echo esc_html($main_vendor->display_name); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="dokan-product-status status-<?php echo esc_attr($product->post_status); ?>">
                                                        <?php echo esc_html(ucfirst($product->post_status)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($product_obj): ?>
                                                        <?php echo $product_obj->get_price_html(); ?>
                                                    <?php else: ?>
                                                        <?php esc_html_e('N/A', 'wp-plugin-by-al'); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($product->post_date))); ?>
                                                </td>
                                                <td>
                                                    <div class="dokan-product-actions">
                                                        <a href="<?php echo get_permalink($product->ID); ?>" target="_blank" class="dokan-btn dokan-btn-sm dokan-btn-default" title="<?php esc_attr_e('View Product', 'wp-plugin-by-al'); ?>">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    <?php endif; ?>
                </article>

                <?php
                do_action('dokan_after_listing_product');
                ?>
            </div>
        </div>

        <style>
        .dokan-product-listing-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #e1e1e1;
        }
        .dokan-product-listing-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .dokan-product-count {
            color: #666;
            font-size: 14px;
        }
        .dokan-no-products {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
        }
        .dokan-no-products-icon {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        .dokan-no-products h3 {
            margin-bottom: 10px;
            color: #333;
        }
        .dokan-no-products p {
            color: #666;
            font-size: 16px;
        }
        .dokan-product-listing-table {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            overflow: hidden;
        }
        .dokan-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .dokan-table th,
        .dokan-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }
        .dokan-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .dokan-table tr:hover {
            background: #f8f9fa;
        }
        .dokan-table tr.danger {
            background: #f8d7da;
        }
        .dokan-product-title a {
            color: #0073aa;
            text-decoration: none;
            font-weight: 500;
        }
        .dokan-product-title a:hover {
            text-decoration: underline;
        }
        .dokan-vendor-name {
            color: #666;
        }
        .dokan-product-status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-publish {
            background: #d4edda;
            color: #155724;
        }
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        .status-pending {
            background: #cce5ff;
            color: #004085;
        }
        .dokan-product-actions {
            display: flex;
            gap: 5px;
        }
        .dokan-btn {
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        .dokan-btn-default {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #e1e1e1;
        }
        .dokan-btn-default:hover {
            background: #e9ecef;
            color: #333;
        }
        </style>
        <?php
    }

    /**
     * Send email notification to co-vendors when their products are ordered using Dokan's email system
     */
    public function send_co_vendor_order_notification($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        $co_vendors_notified = array();
        
        // Get all items in the order
        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_data()['product_id'];
            $co_author_id = get_post_meta($product_id, '_co_author', true);
            
            // Check if this product has a co-author and we haven't notified them yet
            if ($co_author_id && !in_array($co_author_id, $co_vendors_notified)) {
                $co_vendor = get_userdata($co_author_id);
                
                if ($co_vendor && $co_vendor->user_email) {
                    // Use Dokan's email system
                    do_action('dokan_co_vendor_order_notification', $co_vendor, $order, $product_id);
                    $co_vendors_notified[] = $co_author_id;
                }
            }
        }
    }

    /**
     * Get products where current vendor is assigned as co-author
     */
    public function get_co_authored_products($vendor_id) {
        global $co_authored_products_cache;
        
        // Check if we already have cached results for this vendor
        if (isset($co_authored_products_cache[$vendor_id])) {
            return $co_authored_products_cache[$vendor_id];
        }
        
        $args = array(
            'post_type'      => 'product',
            'post_status'    => array('publish', 'draft', 'pending'),
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_co_author',
                    'value'   => $vendor_id,
                    'compare' => '='
                )
            ),
            'orderby'        => 'date',
            'order'          => 'DESC'
        );
        
        $products = get_posts($args);
        
        // Cache the results globally
        $co_authored_products_cache[$vendor_id] = $products;
        
        return $products;
    }

}
