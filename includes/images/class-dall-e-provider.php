<?php

namespace Autoblog_AI\Images;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DALL-E image generation via the WP AI Client SDK.
 */
class Dall_E_Provider {

	/**
	 * Generate an image and return binary data.
	 *
	 * @param string $prompt Image prompt.
	 * @return array{data: string, mime: string} Image binary data and MIME type.
	 * @throws \RuntimeException On failure.
	 */
	public function generate( string $prompt ): array {
		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			throw new \RuntimeException( 'WordPress AI Client is not available. Requires WordPress 7.0+ or the wp-ai-client plugin.' );
		}

		try {
			$image_file = wp_ai_client_prompt( $prompt )
				->using_provider( 'openai' )
				->generate_image();

			if ( is_wp_error( $image_file ) ) {
				throw new \RuntimeException( 'DALL-E generation failed: ' . $image_file->get_error_message() );
			}

			$data_uri = $image_file->getDataUri();

			if ( empty( $data_uri ) ) {
				throw new \RuntimeException( 'DALL-E returned no image data.' );
			}

			// Parse data URI to get binary data and MIME type.
			if ( ! preg_match( '/^data:([^;]+);base64,(.+)$/', $data_uri, $matches ) ) {
				throw new \RuntimeException( 'Failed to parse DALL-E image data URI.' );
			}

			$mime       = $matches[1];
			$image_data = base64_decode( $matches[2] );

			if ( false === $image_data ) {
				throw new \RuntimeException( 'Failed to decode DALL-E image data.' );
			}

			return array(
				'data' => $image_data,
				'mime' => $mime,
			);
		} catch ( \RuntimeException $e ) {
			throw $e;
		} catch ( \Throwable $e ) {
			throw new \RuntimeException( 'DALL-E generation failed: ' . $e->getMessage() );
		}
	}
}
