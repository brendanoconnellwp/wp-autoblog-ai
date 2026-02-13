<?php

namespace Autoblog_AI\Content;

use Autoblog_AI\Images\Image_Generator;
use Autoblog_AI\Linking\Internal_Linker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orchestrates the full article generation pipeline:
 * prompt → AI SDK → link injection → images → post creation.
 */
class Content_Generator {

	/**
	 * Generate a complete article.
	 *
	 * @param string $title   Article title.
	 * @param array  $options Generation options.
	 * @return int Created post ID.
	 * @throws \RuntimeException On generation failure.
	 */
	public function generate( string $title, array $options ): int {
		// 1. Build prompts.
		$prompt_builder  = new Prompt_Builder( $options );
		$linking_context = '';

		if ( ! empty( $options['internal_linking'] ) ) {
			$linker          = new Internal_Linker();
			$linking_context = $linker->get_context_for_prompt( $title );
		}

		$system_prompt = $prompt_builder->system_prompt();
		$user_prompt   = $prompt_builder->user_prompt( $title, $linking_context );

		// 2. Generate text via WP AI Client SDK.
		$content = $this->call_ai_sdk( $system_prompt, $user_prompt );

		/**
		 * Filter the generated content before post creation.
		 *
		 * @param string $content Generated HTML content.
		 * @param string $title   Article title.
		 * @param array  $options Generation options.
		 */
		$content = apply_filters( 'autoblog_ai_generated_content', $content, $title, $options );

		// 3. Inject internal links.
		if ( ! empty( $options['internal_linking'] ) ) {
			$max_links = (int) get_option( 'autoblog_ai_max_links', 3 );
			$linker    = $linker ?? new Internal_Linker();
			$content   = $linker->inject_links( $content, $title, $max_links );
		}

		// 4. Generate featured image (non-blocking: failure doesn't stop post creation).
		$image_id = null;
		$image_provider = $options['image_provider'] ?? 'none';

		if ( 'none' !== $image_provider ) {
			try {
				$image_gen = new Image_Generator();
				$image_id  = $image_gen->generate_featured_image( $title, $options );
			} catch ( \Throwable $e ) {
				// Store the error but continue with post creation.
				$image_error = $e->getMessage();
			}
		}

		// 5. Create the WordPress post.
		$creator = new Post_Creator();
		$post_id = $creator->create( $title, $content, $options, $image_id );

		// Store image error in post meta if applicable.
		if ( ! empty( $image_error ) ) {
			update_post_meta( $post_id, '_autoblog_ai_image_error', sanitize_text_field( $image_error ) );
		}

		return $post_id;
	}

	/**
	 * Call the WP AI Client SDK to generate text.
	 *
	 * @param string $system_prompt System prompt.
	 * @param string $user_prompt   User prompt.
	 * @return string Generated content.
	 * @throws \RuntimeException If the SDK call fails.
	 */
	private function call_ai_sdk( string $system_prompt, string $user_prompt ): string {
		if ( ! class_exists( '\\Developer_Portal\\WP_AI_Client\\AI_Client' ) ) {
			throw new \RuntimeException( 'WP AI Client SDK is not installed. Please install the wordpress/wp-ai-client package.' );
		}

		/**
		 * Filter the temperature for text generation.
		 *
		 * @param float $temperature Default temperature.
		 */
		$temperature = apply_filters( 'autoblog_ai_temperature', 0.7 );

		try {
			$client = new \Developer_Portal\WP_AI_Client\AI_Client();

			$response = $client->prompt( $user_prompt )
				->system( $system_prompt )
				->option( 'temperature', $temperature )
				->generate_text();

			$text = $response->get_text();

			if ( empty( $text ) ) {
				throw new \RuntimeException( 'AI returned empty content.' );
			}

			return $text;
		} catch ( \RuntimeException $e ) {
			throw $e;
		} catch ( \Throwable $e ) {
			throw new \RuntimeException( 'AI text generation failed: ' . $e->getMessage() );
		}
	}
}
