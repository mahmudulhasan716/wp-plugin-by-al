<?php

class CoAuthor {

    public function __construct() {
        // Add field to Dokan product form (frontend)
        add_action('dokan_new_product_form', array($this, 'co_author_field_in_form'), 90, 2);
        add_action('dokan_new_product_after_product_tags', array($this, 'co_author_field_in_form'));

        // Save field data when product is created or edited
        add_action('dokan_new_product_added', array($this, 'save_co_author_field'), 10, 2);
        add_action('dokan_product_updated', array($this, 'save_co_author_field'), 10, 2);
        
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

}
