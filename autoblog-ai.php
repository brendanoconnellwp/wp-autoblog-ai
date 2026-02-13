<?php
/**
 * Plugin Name:       AutoBlog AI
 * Plugin URI:        https://example.com/autoblog-ai
 * Description:       AI-powered bulk article generator using the WordPress AI Client SDK. Supports OpenAI, Anthropic, and Google Gemini for text, plus DALL-E and Stability AI for images.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            AutoBlog AI
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       autoblog-ai
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AUTOBLOG_AI_VERSION', '1.0.0' );
define( 'AUTOBLOG_AI_PLUGIN_FILE', __FILE__ );
define( 'AUTOBLOG_AI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUTOBLOG_AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AUTOBLOG_AI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Composer autoloader.
if ( file_exists( AUTOBLOG_AI_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once AUTOBLOG_AI_PLUGIN_DIR . 'vendor/autoload.php';
}

// Plugin class autoloader.
spl_autoload_register( function ( $class ) {
	$prefix    = 'Autoblog_AI\\';
	$base_dir  = AUTOBLOG_AI_PLUGIN_DIR . 'includes/';

	if ( strpos( $class, $prefix ) !== 0 ) {
		return;
	}

	$relative = substr( $class, strlen( $prefix ) );
	$parts    = explode( '\\', $relative );
	$filename = 'class-' . strtolower( str_replace( '_', '-', array_pop( $parts ) ) ) . '.php';

	$subdir = '';
	if ( ! empty( $parts ) ) {
		$subdir = strtolower( implode( '/', $parts ) ) . '/';
	}

	$file = $base_dir . $subdir . $filename;

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Activation / deactivation hooks.
register_activation_hook( __FILE__, array( 'Autoblog_AI\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Autoblog_AI\\Deactivator', 'deactivate' ) );

/**
 * Boot the plugin after all plugins are loaded.
 */
function autoblog_ai_init() {
	Autoblog_AI\Autoblog_AI::get_instance();
}
add_action( 'plugins_loaded', 'autoblog_ai_init' );
