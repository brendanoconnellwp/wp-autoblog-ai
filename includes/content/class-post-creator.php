<?php

namespace Autoblog_AI\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates WordPress posts from generated content.
 */
class Post_Creator {

	/**
	 * Create a WordPress post.
	 *
	 * @param string   $title   Post title.
	 * @param string   $content HTML content.
	 * @param array    $options Generation options.
	 * @param int|null $image_id Featured image attachment ID (optional).
	 * @return int Post ID.
	 * @throws \RuntimeException If post creation fails.
	 */
	public function create( string $title, string $content, array $options, ?int $image_id = null ): int {
		$post_args = array(
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => $this->sanitize_status( $options['post_status'] ?? 'draft' ),
			'post_type'    => 'post',
			'post_author'  => get_current_user_id() ?: 1,
		);

		// Category.
		$category = absint( $options['category'] ?? 0 );
		if ( $category > 0 ) {
			$post_args['post_category'] = array( $category );
		}

		/**
		 * Filter the post arguments before insertion.
		 *
		 * @param array  $post_args Post arguments.
		 * @param string $title     Original title.
		 * @param array  $options   Generation options.
		 */
		$post_args = apply_filters( 'autoblog_ai_post_args', $post_args, $title, $options );

		$post_id = wp_insert_post( $post_args, true );

		if ( is_wp_error( $post_id ) ) {
			throw new \RuntimeException( 'Failed to create post: ' . $post_id->get_error_message() );
		}

		// Tags.
		$tags = $options['tags'] ?? '';
		if ( '' !== $tags ) {
			$tag_list = array_map( 'trim', explode( ',', $tags ) );
			$tag_list = array_filter( $tag_list );
			if ( ! empty( $tag_list ) ) {
				wp_set_post_tags( $post_id, $tag_list );
			}
		}

		// Featured image.
		if ( $image_id ) {
			set_post_thumbnail( $post_id, $image_id );
		}

		// Store generation meta.
		update_post_meta( $post_id, '_autoblog_ai_generated', '1' );
		update_post_meta( $post_id, '_autoblog_ai_options', wp_json_encode( $options ) );

		return $post_id;
	}

	/**
	 * Ensure the post status is valid.
	 */
	private function sanitize_status( string $status ): string {
		$allowed = array( 'draft', 'publish', 'pending' );
		return in_array( $status, $allowed, true ) ? $status : 'draft';
	}
}
