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
		if ( ! class_exists( '\\Developer_Portal\\WP_AI_Client\\AI_Client' ) ) {
			throw new \RuntimeException( 'WP AI Client SDK is not installed.' );
		}

		try {
			$client   = new \Developer_Portal\WP_AI_Client\AI_Client();
			$response = $client->prompt( $prompt )->generate_image();

			$url = $response->get_url();

			if ( empty( $url ) ) {
				throw new \RuntimeException( 'DALL-E returned no image URL.' );
			}

			// Download the image.
			$download = wp_remote_get( $url, array( 'timeout' => 60 ) );

			if ( is_wp_error( $download ) ) {
				throw new \RuntimeException( 'Failed to download DALL-E image: ' . $download->get_error_message() );
			}

			$body = wp_remote_retrieve_body( $download );
			$mime = wp_remote_retrieve_header( $download, 'content-type' ) ?: 'image/png';

			if ( empty( $body ) ) {
				throw new \RuntimeException( 'Downloaded DALL-E image was empty.' );
			}

			return array(
				'data' => $body,
				'mime' => $mime,
			);
		} catch ( \RuntimeException $e ) {
			throw $e;
		} catch ( \Throwable $e ) {
			throw new \RuntimeException( 'DALL-E generation failed: ' . $e->getMessage() );
		}
	}
}
