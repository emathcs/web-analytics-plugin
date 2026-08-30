<?php
/**
 * Plugin Name:       Web Analytics
 * Plugin URI:        https://www.github.com/emathcs/web-analytics/
 * Description:       Plugin to manage Analytics scripts like GA and GTM.
 * Author:            Carlos E. Alvarez
 * Author URI:        https://www.emathcs.com
 * Version:           1.0.0
 * Tested up to:      7.1
 * Requires at least: 6.3
 * Requires PHP:      8.2.0
 * License:           GNU General Public License v3.0 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Powered by:        https://www.aomath.com
 */

// Prevent direct file access for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registering the plugin into the admin section
 */
function web_analytics_add_admin_menu() {
    add_menu_page(
        'Web Analytics',                      // Page title
        'Web Analytics',                      // Menu title
        'manage_options',                     // Required user capability
        'web-analytics-settings',             // URL slug
        'web_analytics_render_settings_page', // Function that outputs the HTML page
        'dashicons-analytics',                // Left menu icon
        '81'                                  // The left menu link appears after the Settings link
    );
}
add_action( 'admin_menu', 'web_analytics_add_admin_menu' );

/**
 * Initializing and registering the settings via API settings
 */

// Default values for the option
function local_args() {
    return array(
        'default'           => '',
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field', // Automated sanitization
    );
}

// Initializing the settings
function web_analytics_register_settings() {
    /**
     *
     * Google
     *
     */

    // Creating a section in the custom settings page
    add_settings_section(
        'google_main_section',   // Section ID
        'Google Configurations', // Section Title
        '',                      // Callback function to output section text
        'google-settings'        // Page slug where this section goes
    );

    /**
     * Google Analytics
     */

    // Registering the Google Analytics setting option in the wp_options table
    register_setting(
        'google_settings_group',   // Option group name
        'google_analytics_option', // Actual option key name
        local_args()
    );

    // Google Analytics input field
    add_settings_field(
        'google_analytics_id',    // Field ID
        'Google Analytics ID',    // Field label on the left side
        'web_analytics_callback', // Callback function to output the input element
        'google-settings',        // Page slug
        'google_main_section',    // Section ID
        array(
            'company' => 'google',   // Parameter for the callback function
            'service' => 'analytics' // Parameter for the callback function
        )
    );

    /**
     * Google Tag Manager
     */

    // Registering the Google Tag Manager setting option in the wp_options table
    register_setting(
        'google_settings_group',     // Option group name
        'google_tag_manager_option', // Actual option key name
        local_args()
    );

    // Google Tag Manager input field
    add_settings_field(
        'google_tag_manager_id',  // Field ID
        'Google Tag Manager ID',  // Field label on the left side
        'web_analytics_callback', // Callback function to output the input element
        'google-settings',        // Page slug
        'google_main_section',    // Section ID
        array(
            'company' => 'google',     // Parameter for the callback function
            'service' => 'tag_manager' // Parameter for the callback function
        )
    );
}
add_action( 'admin_init', 'web_analytics_register_settings' );

/**
 * Callback function to render the fields
 */
function web_analytics_callback( $args = array() ) {
    $company = isset ( $args['company'] ) ? $args['company'] : null ;
    $service = isset ( $args['service'] ) ? $args['service'] : null ;
    // Verifying if the user sent both parameters
    if ( $company && $service ) {
        $company_service = $company . '_' . $service;
        // Getting the service id
        $service_id = get_option( $company_service . '_option', '' );
        // Generating the input
        echo '<input type="text" id="' . $company_service . '_id" name="' . $company_service . '_option" value="' . esc_attr( $service_id ) . '" class="regular-text" />';
    }
}

/**
 * Injecting scripts
 */

// Injecting into the header the following scripts: Google Analytics and Google Tag Manager.
function inject_plugin_code_to_header() {
    $ga_id  = get_option( 'google_analytics_option', '' );
    $gtm_id = get_option( 'google_tag_manager_option', '' );
    // Checking if there is a Google Analytics ID
    if ( $ga_id ) {
        echo "<!-- Google tag (gtag.js) -->";
        echo "<script async src='https://www.googletagmanager.com/gtag/js?id=" . $ga_id . "'></script>";
        echo "<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '" . $ga_id . "');</script>";
    }
    // Checking if there is a Google Tag Manager ID
    if ( $gtm_id ) {
        echo "<!-- Google Tag Manager -->";
        echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $gtm_id . "');</script>";
        echo "<!-- End Google Tag Manager -->";
    }
}
add_action('wp_head', 'inject_plugin_code_to_header');

// Injecting into the body the following scripts: Google Tag Manager.
function inject_plugin_code_to_body_open() {
    $gtm_id = get_option( 'google_tag_manager_option', '' );
    // Parameter for the callback function
    if ( $gtm_id ) {
        echo '<!-- Google Tag Manager (noscript) -->';
        echo '<noscript>';
        echo '    <iframe src="https://www.googletagmanager.com/ns.html?id=' . $gtm_id . '" height="0" width="0" style="display:none;visibility:hidden"></iframe>';
        echo '</noscript>';
        echo '<!-- End Google Tag Manager (noscript) -->';
    }
}
add_action('wp_body_open', 'inject_plugin_code_to_body_open');

/**
 * Rendering the fields in the admin section
 */
function web_analytics_render_settings_page() {
    // Verify if the user actually has permission to view this
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    echo '<div class="wrap">';
    echo '    <h1>' . esc_html( get_admin_page_title() ) . '</h1>';
    echo '    <!-- Standard WordPress admin form targeting options.php -->';
    echo '    <form action="options.php" method="post">';
    // Output nonce, action, and option group hidden fields for security
    settings_fields( 'google_settings_group' );
    // Output all sections registered to this page
    do_settings_sections( 'google-settings' );
    // Output standard submission button
    submit_button( 'Save Changes' );
    echo '    </form>';
    echo '</div>';
}
