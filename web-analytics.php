<?php
/**
 * Plugin Name:       Web Analytics
 * Plugin URI:        https://www.github.com/emathcs/web-analytics/
 * Description:       Plugin to manage Analytics script like GA and GTM.
 * Author:            Carlos E. Alvarez
 * Author URI:        https://www.emathcs.com
 * Version:           0.1.0
 * Tested up to:      7.0
 * Requires at least: 7.0
 * Requires PHP:      8.0
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
 * Rendering the fields in the admin section
 */
function web_analytics_render_settings_page() {
    // Verify if the user actually has permission to view this
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    echo '<div class="wrap">';
    echo '    <h1>' . esc_html( get_admin_page_title() ) . '</h1>';
    echo '</div>';
}
