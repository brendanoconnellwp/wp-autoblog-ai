<?php

namespace Autoblog_AI\Images;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image generation abstraction: builds prompts, delegates to providers,
 * and uploads results to the WordPress media library.
 */
class Image_Generator {

	/**
	 * Generate a featured image for an article and upload to media library.
	 *
	 * @param string $title   Article title.
	 * @param array  $options Generation options.
	 * @return int Attachment ID.
	 * @throws \RuntimeException On failure.
	 */
	public function generate_featured_image( string $title, array $options ): int {
		$provider = $options['image_provider'] ?? 'none';
		$style    = $options['image_style'] ?? 'photorealistic';

		if ( 'none' === $provider ) {
			throw new \RuntimeException( 'No image provider configured.' );
		}

		$prompt = $this->build_prompt( $title, $style );

		/**
		 * Filter the image generation prompt.
		 *
		 * @param string $prompt  Image prompt.
		 * @param string $title   Article title.
		 * @param array  $options Generation options.
		 */
		$prompt = apply_filters( 'autoblog_ai_image_prompt', $prompt, $title, $options );

		// Generate via selected provider.
		$result = $this->generate_image( $provider, $prompt, $style );

		// Upload to media library.
		return $this->upload_to_media_library( $result['data'], $result['mime'], $title );
	}

	/**
	 * Build the image prompt from the article title and style.
	 */
	private function build_prompt( string $title, string $style ): string {
		$style_desc = match ( $style ) {
			'photorealistic' => 'photorealistic, high quality photography',
			'illustration'   => 'digital illustration, vibrant colors',
			'3d_render'      => '3D rendered, professional lighting',
			'digital_art'    => 'digital art, modern aesthetic',
			'watercolor'     => 'watercolor painting, artistic',
			default          => 'high quality',
		};

		return "A featured blog image for an article titled \"{$title}\". Style: {$style_desc}. Clean, professional, suitable for a blog header. No text or words in the image.";
	}

	/**
	 * Delegate to the appropriate provider.
	 *
	 * @return array{data: string, mime: string}
	 */
	private function generate_image( string $provider, string $prompt, string $style ): array {
		return match ( $provider ) {
			'dall-e'    => ( new Dall_E_Provider() )->generate( $prompt ),
			'stability' => ( new Stability_Provider() )->generate( $prompt, $style ),
			default     => throw new \RuntimeException( "Unknown image provider: {$provider}" ),
		};
	}

	/**
	 * Upload image binary data to the WordPress media library.
	 *
	 * @param string $data  Image binary data.
	 * @param string $mime  MIME type.
	 * @param string $title Article title (used for filename and alt text).
	 * @return int Attachment ID.
	 * @throws \RuntimeException On upload failure.
	 */
	private function upload_to_media_library( string $data, string $mime, string $title ): int {
		if ( ! function_exists( 'wp_upload_bits' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$ext = match ( $mime ) {
			'image/jpeg', 'image/jpg' => 'jpg',
			'image/webp'              => 'webp',
			default                   => 'png',
		};

		$filename = sanitize_file_name( sanitize_title( $title ) ) . '.' . $ext;
		$upload   = wp_upload_bits( $filename, null, $data );

		if ( ! empty( $upload['error'] ) ) {
			throw new \RuntimeException( 'Media upload failed: ' . $upload['error'] );
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_title'     => sanitize_text_field( $title ),
				'post_mime_type' => $mime,
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) ) {
			throw new \RuntimeException( 'Failed to create attachment: ' . $attachment_id->get_error_message() );
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		// Set alt text.
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $title ) );

		return $attachment_id;
	}
}
