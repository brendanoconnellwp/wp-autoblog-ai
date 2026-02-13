<?php

namespace Autoblog_AI\Admin;

use Autoblog_AI\Security\Encryption;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin settings page using the WordPress Settings API.
 */
class Settings_Page {

	private const OPTION_GROUP = 'autoblog_ai_settings';

	/**
	 * Register settings and sections.
	 */
	public function register_settings(): void {
		// Content defaults.
		register_setting( self::OPTION_GROUP, 'autoblog_ai_word_count', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 1500,
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_tone', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'informative',
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_pov', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'third',
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_faq_count', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 3,
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_takeaway_count', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 3,
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_article_type', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'blog_post',
		) );

		// Image settings.
		register_setting( self::OPTION_GROUP, 'autoblog_ai_image_provider', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'none',
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_image_style', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'photorealistic',
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_stability_api_key', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'sanitize_stability_key' ),
			'default'           => '',
		) );

		// Internal linking.
		register_setting( self::OPTION_GROUP, 'autoblog_ai_internal_linking', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );
		register_setting( self::OPTION_GROUP, 'autoblog_ai_max_links', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 3,
		) );

		// Sections.
		add_settings_section(
			'autoblog_ai_content_section',
			__( 'Content Defaults', 'autoblog-ai' ),
			'__return_null',
			self::OPTION_GROUP
		);

		add_settings_section(
			'autoblog_ai_image_section',
			__( 'Image Generation', 'autoblog-ai' ),
			'__return_null',
			self::OPTION_GROUP
		);

		add_settings_section(
			'autoblog_ai_linking_section',
			__( 'Internal Linking', 'autoblog-ai' ),
			'__return_null',
			self::OPTION_GROUP
		);
	}

	/**
	 * Sanitize and encrypt the Stability AI API key.
	 */
	public function sanitize_stability_key( string $value ): string {
		$value = sanitize_text_field( $value );

		// If the user submitted the masked placeholder, keep existing value.
		if ( str_starts_with( $value, '••••' ) ) {
			return get_option( 'autoblog_ai_stability_api_key', '' );
		}

		if ( '' === $value ) {
			return '';
		}

		return Encryption::encrypt( $value );
	}

	/**
	 * Render the settings page.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include AUTOBLOG_AI_PLUGIN_DIR . 'templates/settings-page.php';
	}
}
