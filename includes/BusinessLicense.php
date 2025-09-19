<?php

class BusinessLicense {

    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('dokan_seller_wizard_store_setup_after_address_field', array($this, 'add_business_license_field'));
        add_action('dokan_seller_wizard_store_field_save', array($this, 'save_business_license_field'));
        add_action('dokan_seller_wizard_store_setup_before_map_field', array($this, 'validate_business_license_field'));
    }

    public function init() {
        // Try multiple logging methods
        error_log('BusinessLicense init() called');
    }

    /**
     * Add Business License ID field to Dokan setup wizard
     */
    public function add_business_license_field($setup_wizard) {
        $store_info = dokan_get_store_info(dokan_get_current_user_id());
        $business_license_id = isset($store_info['business_license_id']) ? esc_attr($store_info['business_license_id']) : '';
        $request_data = wc_clean(wp_unslash($_POST));
        $has_error = !empty($request_data['error_business_license_id']) || ( isset($_GET['bl_error']) && '1' === sanitize_text_field(wp_unslash($_GET['bl_error'])) );
        ?>
        <tr>
            <th scope="row">
                <label for="business_license_id">
                    <?php esc_html_e('Business License ID', 'wp-plugin-by-al'); ?>
                    <span class='required'>*</span>
                </label>
            </th>
            <td>
                <input type="text" id="business_license_id" name="business_license_id" value="<?php echo $business_license_id; ?>" required/>
                <span class="error-container">
                    <?php
                    if ($has_error) {
                        echo '<span class="required">' . __('This is required', 'wp-plugin-by-al') . '</span>';
                    }
                    ?>
                </span>
            </td>
        </tr>
        <?php
    }

    /**
     * Save Business License ID field
     */
    public function save_business_license_field($setup_wizard) {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'dokan-seller-setup')) {
            return;
        }

        $store_id = dokan_get_current_user_id();
        $dokan_settings = dokan_get_store_info($store_id);

        // Validate Business License ID field
        $business_license_id = isset($_POST['business_license_id']) ? sanitize_text_field(wp_unslash($_POST['business_license_id'])) : '';
        
        if (empty($business_license_id)) {
            $_POST['error_business_license_id'] = 'error';
            // Redirect back to current step to prevent progressing to the next step
            $base_url = add_query_arg(
                array(
                    'page' => 'dokan-seller-setup',
                    'step' => 'store',
                    '_admin_sw_nonce' => isset($_GET['_admin_sw_nonce']) ? sanitize_key(wp_unslash($_GET['_admin_sw_nonce'])) : '',
                    'bl_error' => 1,
                ),
                site_url( '/' )
            );
            wp_safe_redirect( esc_url_raw( $base_url ) );
            exit;
        }

        // Save Business License ID
        $dokan_settings['business_license_id'] = $business_license_id;
        
        update_user_meta($store_id, 'dokan_profile_settings', $dokan_settings);
        
        do_action('dokan_business_license_saved', $store_id, $business_license_id);
    }

    /**
     * Validate Business License ID field before form submission
     */
    public function validate_business_license_field($setup_wizard) {
        if (!isset($_POST['save_step']) || !isset($_POST['business_license_id'])) {
            return;
        }

        $business_license_id = sanitize_text_field(wp_unslash($_POST['business_license_id']));
        
        if (empty($business_license_id)) {
            $_POST['error_business_license_id'] = 'error';
            // Add JavaScript to prevent form submission
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.store-step-continue').on('click', function(e) {
                    var businessLicenseId = $('input[name="business_license_id"]').val();
                    if (!businessLicenseId || businessLicenseId.trim() === '') {
                        e.preventDefault();
                        $('input[name="business_license_id"]').closest('td').find('.error-container').html('<span class="required"><?php echo esc_js(__('This is required', 'wp-plugin-by-al')); ?></span>');
                        return false;
                    }
                });
            });
            </script>
            <?php
        }
    }
}
