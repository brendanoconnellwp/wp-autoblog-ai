<?php

namespace Autoblog_AI\Queue;

use Autoblog_AI\Content\Content_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Action Scheduler callback handler. Processes one article at a time.
 */
class Queue_Processor {

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

		// Skip if already processed or in progress.
		if ( ! in_array( $item->status, array( 'queued', 'failed' ), true ) ) {
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
		}
	}
}
