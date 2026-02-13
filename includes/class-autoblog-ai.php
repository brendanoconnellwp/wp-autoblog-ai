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
		}

		// REST API.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Queue processor (front and back end — Action Scheduler can run via cron).
		$processor = new Queue\Queue_Processor();
		$processor->register();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		$controller = new Rest\Rest_Controller();
		$controller->register_routes();
	}
}
