/**
 * Frontend JavaScript for Dokan Business License Manager
 *
 * @package Dokan_Business_License
 * @since 1.0.0
 */

(function($) {
    'use strict';

    var DokanBusinessLicense = {
        
        /**
         * Initialize the plugin
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Real-time validation for business license field
            $(document).on('input blur', '#business_license_id', this.validateBusinessLicense);
            
            // Form submission validation
            $(document).on('submit', 'form', this.validateFormSubmission);
        },

        /**
         * Validate business license field in real-time
         */
        validateBusinessLicense: function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value === '') {
                return; // Don't validate empty field
            }
            
            if (!DokanBusinessLicense.isValidLicenseFormat(value)) {
                $field.addClass('error');
            } else {
                $field.removeClass('error');
            }
        },

        /**
         * Check if license format is valid
         */
        isValidLicenseFormat: function(licenseId) {
            // Alphanumeric, 6-20 characters
            var pattern = /^[A-Za-z0-9]{6,20}$/;
            return pattern.test(licenseId);
        },

        /**
         * Validate form submission
         */
        validateFormSubmission: function(e) {
            var $form = $(this);
            var $licenseField = $form.find('#business_license_id');
            
            if ($licenseField.length && $licenseField.prop('required')) {
                var value = $licenseField.val().trim();
                
                if (value === '') {
                    e.preventDefault();
                    alert('Business License ID is required.');
                    $licenseField.focus();
                    return false;
                }
                
                if (!DokanBusinessLicense.isValidLicenseFormat(value)) {
                    e.preventDefault();
                    alert('Please enter a valid Business License ID format.');
                    $licenseField.focus();
                    return false;
                }
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        DokanBusinessLicense.init();
    });

    // Expose to global scope for external use
    window.DokanBusinessLicense = DokanBusinessLicense;

})(jQuery);
