<?php

namespace Autoblog_AI\Queue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queue CRUD operations and Action Scheduler scheduling.
 */
class Queue_Manager {

	/**
	 * Get the queue table name.
	 */
	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'autoblog_queue';
	}

	/**
	 * Add a batch of articles to the queue and schedule processing.
	 *
	 * @param array $titles  List of article titles.
	 * @param array $options Shared generation options for the batch.
	 * @return int[] Inserted queue item IDs.
	 */
	public static function add_batch( array $titles, array $options ): array {
		global $wpdb;

		$table = self::table();
		$ids   = array();
		$now   = current_time( 'mysql', true );

		foreach ( $titles as $title ) {
			$title = sanitize_text_field( $title );
			if ( '' === $title ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'title'      => $title,
					'options'    => wp_json_encode( $options ),
					'status'     => 'queued',
					'created_at' => $now,
				),
				array( '%s', '%s', '%s', '%s' )
			);

			$queue_id = (int) $wpdb->insert_id;
			$ids[]    = $queue_id;

			// Schedule via Action Scheduler.
			self::schedule_item( $queue_id );
		}

		return $ids;
	}

	/**
	 * Schedule a single queue item for processing.
	 */
	public static function schedule_item( int $queue_id ): void {
		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			return;
		}

		$action_id = as_enqueue_async_action(
			'autoblog_ai_process_article',
			array( 'queue_id' => $queue_id ),
			'autoblog-ai'
		);

		if ( $action_id ) {
			self::update( $queue_id, array( 'action_id' => $action_id ) );
		}
	}

	/**
	 * Update a queue item.
	 *
	 * @param int   $queue_id Queue item ID.
	 * @param array $data     Column => value pairs.
	 */
	public static function update( int $queue_id, array $data ): bool {
		global $wpdb;

		$result = $wpdb->update(
			self::table(),
			$data,
			array( 'id' => $queue_id ),
		);

		return false !== $result;
	}

	/**
	 * Mark item as started.
	 */
	public static function mark_generating( int $queue_id ): void {
		self::update( $queue_id, array(
			'status'     => 'generating',
			'started_at' => current_time( 'mysql', true ),
		) );
	}

	/**
	 * Mark item as complete.
	 */
	public static function mark_complete( int $queue_id, int $post_id ): void {
		self::update( $queue_id, array(
			'status'       => 'complete',
			'post_id'      => $post_id,
			'completed_at' => current_time( 'mysql', true ),
		) );
	}

	/**
	 * Mark item as failed.
	 */
	public static function mark_failed( int $queue_id, string $error ): void {
		global $wpdb;

		$item = self::get_item( $queue_id );
		if ( ! $item ) {
			return;
		}

		self::update( $queue_id, array(
			'status'        => 'failed',
			'error_message' => sanitize_text_field( $error ),
			'retry_count'   => (int) $item->retry_count + 1,
			'completed_at'  => current_time( 'mysql', true ),
		) );
	}

	/**
	 * Retry a failed item by resetting status and re-scheduling.
	 */
	public static function retry_item( int $queue_id ): bool {
		$item = self::get_item( $queue_id );
		if ( ! $item || 'failed' !== $item->status || $item->retry_count >= 3 ) {
			return false;
		}

		self::update( $queue_id, array(
			'status'        => 'queued',
			'error_message' => null,
			'started_at'    => null,
			'completed_at'  => null,
		) );

		self::schedule_item( $queue_id );

		return true;
	}

	/**
	 * Delete a queue item.
	 */
	public static function delete_item( int $queue_id ): bool {
		global $wpdb;

		$item = self::get_item( $queue_id );

		// Cancel scheduled action if pending.
		if ( $item && $item->action_id && function_exists( 'as_unschedule_action' ) ) {
			as_unschedule_action( 'autoblog_ai_process_article', array( 'queue_id' => $queue_id ), 'autoblog-ai' );
		}

		$result = $wpdb->delete( self::table(), array( 'id' => $queue_id ), array( '%d' ) );

		return false !== $result;
	}

	/**
	 * Get a single queue item.
	 */
	public static function get_item( int $queue_id ): ?object {
		global $wpdb;

		$table = self::table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $queue_id ) );

		return $row ?: null;
	}

	/**
	 * Get queue items, most recent first.
	 *
	 * @param int $limit Max items to return.
	 */
	public static function get_items( int $limit = 50 ): array {
		global $wpdb;

		$table = self::table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", $limit ) );
	}
}
