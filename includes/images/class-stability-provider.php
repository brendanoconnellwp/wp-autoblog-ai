<?php

namespace Autoblog_AI\Images;

use Autoblog_AI\Security\Encryption;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stability AI image generation via direct REST API.
 */
class Stability_Provider {

	private const API_URL = 'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image';

	/**
	 * Generate an image and return binary data.
	 *
	 * @param string $prompt Image prompt.
	 * @param string $style  Image style preset.
	 * @return array{data: string, mime: string} Image binary data and MIME type.
	 * @throws \RuntimeException On failure.
	 */
	public function generate( string $prompt, string $style = 'photographic' ): array {
		$api_key = $this->get_api_key();

		if ( '' === $api_key ) {
			throw new \RuntimeException( 'Stability AI API key is not configured. Add it in AutoBlog AI > Settings.' );
		}

		$style_preset = $this->map_style( $style );

		$body = array(
			'text_prompts' => array(
				array(
					'text'   => $prompt,
					'weight' => 1,
				),
			),
			'cfg_scale'    => 7,
			'height'       => 1024,
			'width'        => 1024,
			'samples'      => 1,
			'steps'        => 30,
		);

		if ( $style_preset ) {
			$body['style_preset'] = $style_preset;
		}

		$response = wp_remote_post( self::API_URL, array(
			'timeout' => 120,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'body' => wp_json_encode( $body ),
		) );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'Stability AI request failed: ' . $response->get_error_message() );
		}

		$status = wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status !== 200 ) {
			$message = $data['message'] ?? "HTTP {$status}";
			throw new \RuntimeException( 'Stability AI error: ' . $message );
		}

		if ( empty( $data['artifacts'][0]['base64'] ) ) {
			throw new \RuntimeException( 'Stability AI returned no image data.' );
		}

		$image_data = base64_decode( $data['artifacts'][0]['base64'] );

		if ( false === $image_data ) {
			throw new \RuntimeException( 'Failed to decode Stability AI image.' );
		}

		return array(
			'data' => $image_data,
			'mime' => 'image/png',
		);
	}

	/**
	 * Get the decrypted API key.
	 */
	private function get_api_key(): string {
		$encrypted = get_option( 'autoblog_ai_stability_api_key', '' );
		if ( '' === $encrypted ) {
			return '';
		}
		return Encryption::decrypt( $encrypted );
	}

	/**
	 * Map our style names to Stability AI style presets.
	 */
	private function map_style( string $style ): ?string {
		return match ( $style ) {
			'photorealistic' => 'photographic',
			'illustration'   => 'comic-book',
			'3d_render'      => '3d-model',
			'digital_art'    => 'digital-art',
			'watercolor'     => 'watercolor',
			default          => null,
		};
	}
}
