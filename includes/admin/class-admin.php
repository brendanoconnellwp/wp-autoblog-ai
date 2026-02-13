<?php

namespace Autoblog_AI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin menu registration and asset enqueueing.
 */
class Admin {

	/** @var Settings_Page */
	private Settings_Page $settings_page;

	/** @var Generator_Page */
	private Generator_Page $generator_page;

	public function __construct() {
		$this->settings_page  = new Settings_Page();
		$this->generator_page = new Generator_Page();
	}

	/**
	 * Register all admin hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this->settings_page, 'register_settings' ) );
	}

	/**
	 * Add admin menu and submenu pages.
	 */
	public function add_menu_pages(): void {
		add_menu_page(
			__( 'AutoBlog AI', 'autoblog-ai' ),
			__( 'AutoBlog AI', 'autoblog-ai' ),
			'manage_options',
			'autoblog-ai',
			array( $this->generator_page, 'render' ),
			'dashicons-edit-page',
			30
		);

		add_submenu_page(
			'autoblog-ai',
			__( 'Article Generator', 'autoblog-ai' ),
			__( 'Article Generator', 'autoblog-ai' ),
			'manage_options',
			'autoblog-ai',
			array( $this->generator_page, 'render' )
		);

		add_submenu_page(
			'autoblog-ai',
			__( 'Settings', 'autoblog-ai' ),
			__( 'Settings', 'autoblog-ai' ),
			'manage_options',
			'autoblog-ai-settings',
			array( $this->settings_page, 'render' )
		);
	}

	/**
	 * Enqueue admin CSS and JS on plugin pages only.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'autoblog-ai-admin',
			AUTOBLOG_AI_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			AUTOBLOG_AI_VERSION
		);

		wp_enqueue_script(
			'autoblog-ai-admin',
			AUTOBLOG_AI_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			AUTOBLOG_AI_VERSION,
			true
		);

		wp_localize_script( 'autoblog-ai-admin', 'autoblogAI', array(
			'restUrl'  => rest_url( 'autoblog-ai/v1/' ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'i18n'     => array(
				'generating'  => __( 'Generating...', 'autoblog-ai' ),
				'queued'      => __( 'Queued', 'autoblog-ai' ),
				'complete'    => __( 'Complete', 'autoblog-ai' ),
				'failed'      => __( 'Failed', 'autoblog-ai' ),
				'retry'       => __( 'Retry', 'autoblog-ai' ),
				'delete'      => __( 'Delete', 'autoblog-ai' ),
				'view'        => __( 'View Post', 'autoblog-ai' ),
				'confirmDel'  => __( 'Delete this queue item?', 'autoblog-ai' ),
				'noTitles'    => __( 'Please enter at least one article title.', 'autoblog-ai' ),
				'submitError' => __( 'Failed to submit articles. Please try again.', 'autoblog-ai' ),
			),
		) );
	}

	/**
	 * Check if we are on a plugin admin page.
	 */
	private function is_plugin_page( string $hook_suffix ): bool {
		return str_contains( $hook_suffix, 'autoblog-ai' );
	}
}
