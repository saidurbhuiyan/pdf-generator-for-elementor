<?php
/**
 * Plugin Name: PDF Generator for Elementor Pro
 * Description: Generates a PDF from Elementor Pro form submissions and attaches it to the email.
 * Version: 1.0
 * Author: Saidur Bhuiyan
 */

use ElementorPro\PDFGenerator\Email3Action;
use ElementorPro\PDFGenerator\HubspotMailListAction;
use ElementorPro\PDFGenerator\PDFGenerateAction;
use ElementorPro\PDFGenerator\PDFGenerator;
use ElementorPro\PDFGenerator\SettingsPage;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define("ELEMENTOR_PDF_GENERATOR_PATH", plugin_dir_path(__FILE__));
define( 'ELEMENTOR_PDF_GENERATOR_URL', plugin_dir_url( __FILE__ ) );

// Autoload classes with Composer
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Initialize core functionality
new PDFGenerator();

// Register custom Elementor action
add_action( 'elementor_pro/forms/actions/register', function ( $form_actions_registrar ) {
    $form_actions_registrar->register( new PDFGenerateAction() );
   // $form_actions_registrar->register( new Email3Action() );
    $form_actions_registrar->register( new HubspotMailListAction() );
});

// Add Admin Settings Page
//add_action( 'admin_menu', function() {
//    new SettingsPage();
//});

/**
 * Activation Hook
 * Set default template on plugin activation.
 */
register_activation_hook( __FILE__, static function() {
    $default_template = [
        [
            'id'       => 1,
            'title'     => 'Default Template',
            'name'     => 'application',
            'template' => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/default/default-template.php',
            'header'   => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/default/default-header.php',
            'footer'   => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/default/default-footer.php',
            'preview'  => ELEMENTOR_PDF_GENERATOR_URL . 'templates/default/default-preview.png',
            'is_active'=> true,
        ],
        [
            'id'       => 2,
            'title'     => 'Membership Template',
            'name'     => 'membership-application',
            'template' => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/membership/membership-template.php',
            'header'   => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/membership/membership-header.php',
            'footer'   => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/membership/membership-footer.php',
            'preview'  => ELEMENTOR_PDF_GENERATOR_URL . 'templates/membership/membership-preview.png',
            'is_active'=> true,
        ]

    ];

    update_option( 'pdf_generator_templates', $default_template );
});

/**
 * Deactivation Hook (Optional Cleanup)
 * Remove the templates option on plugin deactivation.
 */
register_deactivation_hook( __FILE__, static function() {
    delete_option( 'pdf_generator_templates' );
});
