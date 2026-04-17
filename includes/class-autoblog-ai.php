<?php

namespace Autoblog_AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class. Singleton that wires all hooks.
 */
final class Autoblog_AI {

	/** @var self|null */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	private function init_hooks(): void {
		// Admin.
		if ( is_admin() ) {
			$admin = new Admin\Admin();
			$admin->register();

			add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );
		}

		// REST API.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Queue processor (front and back end — Action Scheduler can run via cron).
		$processor = new Queue\Queue_Processor();
		$processor->register();

		// Multisite: bootstrap new sites when the plugin is network-activated.
		add_action( 'wp_initialize_site', array( $this, 'on_new_site' ), 200 );
	}

	/**
	 * Show an admin notice if the WP AI Client dependency is missing.
	 */
	public function maybe_show_dependency_notice(): void {
		if ( class_exists( '\WordPress\AiClient\AiClient' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! str_contains( $screen->id, 'autoblog-ai' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'AutoBlog AI requires the WordPress AI Client to generate articles. Please upgrade to WordPress 7.0+ or install the wp-ai-client plugin.', 'autoblog-ai' )
		);
	}

	/**
	 * Bootstrap the plugin on a newly created multisite site.
	 *
	 * @param \WP_Site $site New site object.
	 */
	public function on_new_site( \WP_Site $site ): void {
		if ( ! is_plugin_active_for_network( AUTOBLOG_AI_PLUGIN_BASENAME ) ) {
			return;
		}

		switch_to_blog( (int) $site->blog_id );
		Activator::activate();
		restore_current_blog();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		$controller = new Rest\Rest_Controller();
		$controller->register_routes();
	}
}
