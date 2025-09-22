<?php

class ProductMaterial {
    public function __construct() {

        // Render field on Dokan product forms (new/edit)
        add_action('dokan_new_product_form', array($this, 'render_field_in_form'), 90, 2);
        // Render field in Dokan quick add popup form
        add_action('dokan_new_product_after_product_tags', array($this, 'render_field_in_popup'));

        // Save on create/update via Dokan
        add_action('dokan_new_product_added', array($this, 'save_product_material_on_create'), 20, 2);
        add_action('dokan_product_updated', array($this, 'save_product_material_on_update'), 20, 2);

        // Add column to vendor dashboard product list table
        add_action('dokan_product_list_table_after_status_table_header', array($this, 'add_list_table_header'));
        add_action('dokan_product_list_table_after_status_table_data', array($this, 'add_list_table_cell'), 10, 4);

        // Display on single product page
        add_action('woocommerce_product_meta_end', array($this, 'render_on_single_product'));
    }

    private function get_meta_key() {
        return '_product_material';
    }

    private function get_product_material($product_id) {
        $value = get_post_meta($product_id, $this->get_meta_key(), true);
        return is_string($value) ? $value : '';
    }

    private function sanitize_material_from_request() {
        if (!isset($_POST['product_material'])) {
            return null;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $raw = wp_unslash($_POST['product_material']);
        return sanitize_text_field($raw);
    }

    public function render_field_in_form($post, $post_id) {
        $product_id = is_numeric($post_id) ? (int) $post_id : 0;
        $current = $product_id ? $this->get_product_material($product_id) : '';
        ?>
        <div class="dokan-form-group">
            <label for="product_material" class="form-label"><?php echo esc_html__('Product Material', 'wp-plugin-by-al'); ?></label>
            <input type="text" class="dokan-form-control" name="product_material" id="product_material" value="<?php echo esc_attr($current); ?>" placeholder="<?php echo esc_attr__('e.g., Cotton, Leather, Steel', 'wp-plugin-by-al'); ?>" />
            <small class="help-block"><?php echo esc_html__('Describe the primary material of the product. Applies to Simple and Variable products.', 'wp-plugin-by-al'); ?></small>
        </div>
        <?php
    }

    public function render_field_in_popup() {
        $current = '';
        ?>
        <div class="dokan-form-group">
            <label for="product_material" class="form-label"><?php echo esc_html__('Product Material', 'wp-plugin-by-al'); ?></label>
            <input type="text" class="dokan-form-control" name="product_material" id="product_material" value="<?php echo esc_attr($current); ?>" placeholder="<?php echo esc_attr__('e.g., Cotton, Leather, Steel', 'wp-plugin-by-al'); ?>" />
        </div>
        <?php
    }

    public function save_product_material_on_create($product_id, $postdata) {
        $this->update_material_meta($product_id);
    }

    public function save_product_material_on_update($product_id, $postdata) {
        $this->update_material_meta($product_id);
    }

    private function update_material_meta($product_id) {
        $material = $this->sanitize_material_from_request();
        if ($material === null) {
            return;
        }
        if ($material === '') {
            delete_post_meta($product_id, $this->get_meta_key());
            return;
        }
        update_post_meta($product_id, $this->get_meta_key(), $material);
    }

    public function add_list_table_header() {
        echo '<th>' . esc_html__('Material', 'wp-plugin-by-al') . '</th>';
    }

    public function add_list_table_cell($post, $product, $tr_class, $row_actions) {
        $material = $this->get_product_material($post->ID);
        echo '<td data-title="' . esc_attr__('Material', 'wp-plugin-by-al') . '">';
        echo $material !== '' ? esc_html($material) : '<span class="na">&ndash;</span>';
        echo '</td>';
    }

    public function render_on_single_product() {
        global $product;
        if (!$product instanceof WC_Product) {
            return;
        }
        $material = $this->get_product_material($product->get_id());
        if ($material === '') {
            return;
        }
        echo '<span class="product-material">' . esc_html__('Material:', 'wp-plugin-by-al') . ' <strong>' . esc_html($material) . '</strong></span>';
    }
}