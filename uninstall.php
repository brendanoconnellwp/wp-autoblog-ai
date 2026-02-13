<?php
/**
 * AutoBlog AI Uninstall
 *
 * Drops the queue table and deletes all plugin options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Drop the queue table.
$table_name = $wpdb->prefix . 'autoblog_queue';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Delete all plugin options.
$options = array(
	'autoblog_ai_word_count',
	'autoblog_ai_tone',
	'autoblog_ai_pov',
	'autoblog_ai_faq_count',
	'autoblog_ai_takeaway_count',
	'autoblog_ai_article_type',
	'autoblog_ai_image_provider',
	'autoblog_ai_image_style',
	'autoblog_ai_stability_api_key',
	'autoblog_ai_internal_linking',
	'autoblog_ai_max_links',
);

foreach ( $options as $option ) {
	delete_option( $option );
}
