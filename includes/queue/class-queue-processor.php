<?php

namespace Autoblog_AI\Queue;

use Autoblog_AI\Content\Content_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Action Scheduler callback handler. Processes one article at a time.
 *
 * Uses a transient-based concurrency limit to prevent multiple jobs
 * from hammering the AI API simultaneously.
 */
class Queue_Processor {

	/**
	 * Maximum number of articles generating concurrently.
	 * Filterable via 'autoblog_ai_max_concurrent'.
	 */
	private const DEFAULT_MAX_CONCURRENT = 2;

	private const CONCURRENCY_TRANSIENT = 'autoblog_ai_active_jobs';

	/**
	 * Register the Action Scheduler hook.
	 */
	public function register(): void {
		add_action( 'autoblog_ai_process_article', array( $this, 'process' ), 10, 1 );
	}

	/**
	 * Process a single queued article.
	 *
	 * @param int $queue_id Queue item ID.
	 */
	public function process( int $queue_id ): void {
		$item = Queue_Manager::get_item( $queue_id );

		if ( ! $item ) {
			return;
		}

		// Only process items that are properly queued (not failed — those must go through retry_item first).
		if ( 'queued' !== $item->status ) {
			return;
		}

		// Concurrency gate: if too many jobs are running, re-schedule for later.
		if ( ! $this->acquire_slot() ) {
			Queue_Manager::schedule_item( $queue_id );
			return;
		}

		Queue_Manager::mark_generating( $queue_id );

		/**
		 * Fires before article generation begins.
		 *
		 * @param object $item Queue item.
		 */
		do_action( 'autoblog_ai_before_generate', $item );

		try {
			$options = json_decode( $item->options, true );
			if ( ! is_array( $options ) ) {
				throw new \RuntimeException( 'Invalid queue item options.' );
			}

			// Pass the submitting user so the post is attributed correctly.
			$options['post_author'] = (int) ( $item->user_id ?? 1 );

			$generator = new Content_Generator();
			$post_id   = $generator->generate( $item->title, $options );

			Queue_Manager::mark_complete( $queue_id, $post_id );

			/**
			 * Fires after a successful article generation.
			 *
			 * @param int    $post_id  Created post ID.
			 * @param object $item     Queue item.
			 */
			do_action( 'autoblog_ai_article_generated', $post_id, $item );

		} catch ( \Throwable $e ) {
			$error = substr( $e->getMessage(), 0, 500 );
			Queue_Manager::mark_failed( $queue_id, $error );

			/**
			 * Fires after a failed article generation.
			 *
			 * @param string $error Error message.
			 * @param object $item  Queue item.
			 */
			do_action( 'autoblog_ai_article_failed', $error, $item );
		} finally {
			$this->release_slot();
		}
	}

	/**
	 * Try to acquire a concurrency slot.
	 *
	 * @return bool True if a slot was acquired.
	 */
	private function acquire_slot(): bool {
		/** @var int $max */
		$max     = (int) apply_filters( 'autoblog_ai_max_concurrent', self::DEFAULT_MAX_CONCURRENT );
		$current = (int) get_transient( self::CONCURRENCY_TRANSIENT );

		if ( $current >= $max ) {
			return false;
		}

		set_transient( self::CONCURRENCY_TRANSIENT, $current + 1, 10 * MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * Release a concurrency slot.
	 */
	private function release_slot(): void {
		$current = (int) get_transient( self::CONCURRENCY_TRANSIENT );
		$new     = max( 0, $current - 1 );

		if ( 0 === $new ) {
			delete_transient( self::CONCURRENCY_TRANSIENT );
		} else {
			set_transient( self::CONCURRENCY_TRANSIENT, $new, 10 * MINUTE_IN_SECONDS );
		}
	}
}
