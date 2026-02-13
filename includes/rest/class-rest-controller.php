<?php

namespace Autoblog_AI\Rest;

use Autoblog_AI\Queue\Queue_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API endpoints for the generator UI.
 */
class Rest_Controller {

	private const NAMESPACE = 'autoblog-ai/v1';

	/**
	 * Register all routes.
	 */
	public function register_routes(): void {
		register_rest_route( self::NAMESPACE, '/generate', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'generate' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args'                => $this->generate_args(),
		) );

		register_rest_route( self::NAMESPACE, '/queue', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_queue' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( self::NAMESPACE, '/queue/(?P<id>\d+)/retry', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'retry_item' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
			),
		) );

		register_rest_route( self::NAMESPACE, '/queue/(?P<id>\d+)', array(
			'methods'             => 'DELETE',
			'callback'            => array( $this, 'delete_item' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
			),
		) );
	}

	/**
	 * Permission callback for all endpoints.
	 */
	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * POST /generate — Add a batch of titles to the queue.
	 */
	public function generate( \WP_REST_Request $request ): \WP_REST_Response {
		$titles = $request->get_param( 'titles' );

		if ( ! is_array( $titles ) || empty( $titles ) ) {
			return new \WP_REST_Response( array( 'message' => 'No titles provided.' ), 400 );
		}

		$options = array(
			'word_count'       => absint( $request->get_param( 'word_count' ) ) ?: 1500,
			'article_type'     => sanitize_key( $request->get_param( 'article_type' ) ?: 'blog_post' ),
			'tone'             => sanitize_key( $request->get_param( 'tone' ) ?: 'informative' ),
			'pov'              => sanitize_key( $request->get_param( 'pov' ) ?: 'third' ),
			'faq_count'        => absint( $request->get_param( 'faq_count' ) ),
			'takeaway_count'   => absint( $request->get_param( 'takeaway_count' ) ),
			'post_status'      => sanitize_key( $request->get_param( 'post_status' ) ?: 'draft' ),
			'category'         => absint( $request->get_param( 'category' ) ),
			'tags'             => sanitize_text_field( $request->get_param( 'tags' ) ?: '' ),
			'image_provider'   => sanitize_key( $request->get_param( 'image_provider' ) ?: 'none' ),
			'image_style'      => sanitize_key( $request->get_param( 'image_style' ) ?: 'photorealistic' ),
			'internal_linking' => absint( $request->get_param( 'internal_linking' ) ),
		);

		$ids = Queue_Manager::add_batch( $titles, $options );

		return new \WP_REST_Response( array(
			'message' => sprintf( '%d article(s) queued.', count( $ids ) ),
			'ids'     => $ids,
		), 201 );
	}

	/**
	 * GET /queue — Return current queue items.
	 */
	public function get_queue(): \WP_REST_Response {
		$items = Queue_Manager::get_items( 50 );

		$data = array_map( function ( $item ) {
			return array(
				'id'            => (int) $item->id,
				'title'         => $item->title,
				'status'        => $item->status,
				'post_id'       => $item->post_id ? (int) $item->post_id : null,
				'edit_url'      => $item->post_id ? get_edit_post_link( (int) $item->post_id, 'raw' ) : null,
				'error_message' => $item->error_message,
				'retry_count'   => (int) $item->retry_count,
				'created_at'    => $item->created_at,
			);
		}, $items );

		return new \WP_REST_Response( $data );
	}

	/**
	 * POST /queue/{id}/retry — Retry a failed item.
	 */
	public function retry_item( \WP_REST_Request $request ): \WP_REST_Response {
		$id     = (int) $request->get_param( 'id' );
		$result = Queue_Manager::retry_item( $id );

		if ( ! $result ) {
			return new \WP_REST_Response( array( 'message' => 'Cannot retry this item.' ), 400 );
		}

		return new \WP_REST_Response( array( 'message' => 'Item re-queued.' ) );
	}

	/**
	 * DELETE /queue/{id} — Delete a queue item.
	 */
	public function delete_item( \WP_REST_Request $request ): \WP_REST_Response {
		$id     = (int) $request->get_param( 'id' );
		$result = Queue_Manager::delete_item( $id );

		if ( ! $result ) {
			return new \WP_REST_Response( array( 'message' => 'Item not found.' ), 404 );
		}

		return new \WP_REST_Response( array( 'message' => 'Item deleted.' ) );
	}

	/**
	 * Argument schema for the generate endpoint.
	 */
	private function generate_args(): array {
		return array(
			'titles' => array(
				'required' => true,
				'type'     => 'array',
				'items'    => array( 'type' => 'string' ),
			),
			'word_count' => array(
				'type'    => 'integer',
				'default' => 1500,
			),
			'article_type' => array(
				'type'    => 'string',
				'default' => 'blog_post',
			),
			'tone' => array(
				'type'    => 'string',
				'default' => 'informative',
			),
			'pov' => array(
				'type'    => 'string',
				'default' => 'third',
			),
			'faq_count' => array(
				'type'    => 'integer',
				'default' => 3,
			),
			'takeaway_count' => array(
				'type'    => 'integer',
				'default' => 3,
			),
			'post_status' => array(
				'type'    => 'string',
				'default' => 'draft',
			),
			'category' => array(
				'type'    => 'integer',
				'default' => 0,
			),
			'tags' => array(
				'type'    => 'string',
				'default' => '',
			),
			'image_provider' => array(
				'type'    => 'string',
				'default' => 'none',
			),
			'image_style' => array(
				'type'    => 'string',
				'default' => 'photorealistic',
			),
			'internal_linking' => array(
				'type'    => 'integer',
				'default' => 1,
			),
		);
	}
}
