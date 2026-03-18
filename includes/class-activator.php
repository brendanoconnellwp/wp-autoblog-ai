<?php

namespace Autoblog_AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation and schema upgrades.
 */
class Activator {

	private const DB_VERSION_KEY     = 'autoblog_ai_db_version';
	private const CURRENT_DB_VERSION = 2;

	/**
	 * Run on plugin activation.
	 */
	public static function activate(): void {
		self::create_queue_table();
		self::set_default_options();
		update_option( self::DB_VERSION_KEY, self::CURRENT_DB_VERSION );
	}

	/**
	 * Run schema migrations if needed. Called on plugins_loaded.
	 */
	public static function maybe_upgrade(): void {
		$installed = (int) get_option( self::DB_VERSION_KEY, 1 );

		if ( $installed >= self::CURRENT_DB_VERSION ) {
			return;
		}

		if ( $installed < 2 ) {
			self::upgrade_to_v2();
		}

		update_option( self::DB_VERSION_KEY, self::CURRENT_DB_VERSION );
	}

	/**
	 * v2: Add user_id column to queue table.
	 */
	private static function upgrade_to_v2(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'autoblog_queue';

		// Check if column already exists (e.g. fresh install with new schema).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$column = $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'user_id'" );

		if ( empty( $column ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table} ADD COLUMN user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 1 AFTER options" );
		}
	}

	/**
	 * Create the autoblog_queue table via dbDelta.
	 */
	private static function create_queue_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'autoblog_queue';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL DEFAULT '',
			options LONGTEXT NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 1,
			status VARCHAR(20) NOT NULL DEFAULT 'queued',
			post_id BIGINT(20) UNSIGNED DEFAULT NULL,
			error_message TEXT DEFAULT NULL,
			retry_count TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
			action_id BIGINT(20) UNSIGNED DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			started_at DATETIME DEFAULT NULL,
			completed_at DATETIME DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY action_id (action_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Set default option values.
	 */
	private static function set_default_options(): void {
		$defaults = array(
			'autoblog_ai_word_count'        => 1500,
			'autoblog_ai_tone'              => 'informative',
			'autoblog_ai_pov'               => 'third',
			'autoblog_ai_faq_count'         => 3,
			'autoblog_ai_takeaway_count'    => 3,
			'autoblog_ai_article_type'      => 'blog_post',
			'autoblog_ai_image_provider'    => 'none',
			'autoblog_ai_image_style'       => 'photorealistic',
			'autoblog_ai_internal_linking'    => '1',
			'autoblog_ai_max_links'          => 3,
			'autoblog_ai_linking_post_types' => array( 'post', 'page' ),
			'autoblog_ai_stability_api_key'  => '',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}
}
