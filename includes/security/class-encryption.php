<?php

namespace Autoblog_AI\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AES-256-CBC encryption for sensitive data (Stability AI key).
 *
 * Uses WP AUTH_KEY and SECURE_AUTH_KEY salts as the encryption key.
 */
class Encryption {

	private const CIPHER = 'aes-256-cbc';

	/**
	 * Encrypt a plaintext value.
	 */
	public static function encrypt( string $plaintext ): string {
		if ( '' === $plaintext ) {
			return '';
		}

		$key    = self::get_key();
		$iv_len = openssl_cipher_iv_length( self::CIPHER );
		$iv     = openssl_random_pseudo_bytes( $iv_len );

		$encrypted = openssl_encrypt( $plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $encrypted ) {
			return '';
		}

		// Store IV + ciphertext, base64-encoded.
		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a stored value.
	 */
	public static function decrypt( string $ciphertext ): string {
		if ( '' === $ciphertext ) {
			return '';
		}

		$raw = base64_decode( $ciphertext, true );
		if ( false === $raw ) {
			return '';
		}

		$key    = self::get_key();
		$iv_len = openssl_cipher_iv_length( self::CIPHER );

		if ( strlen( $raw ) <= $iv_len ) {
			return '';
		}

		$iv        = substr( $raw, 0, $iv_len );
		$encrypted = substr( $raw, $iv_len );

		$decrypted = openssl_decrypt( $encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		return false !== $decrypted ? $decrypted : '';
	}

	/**
	 * Derive encryption key from WordPress salts.
	 */
	private static function get_key(): string {
		$salt = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'autoblog-ai-default-key' )
		      . ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '' );

		return hash( 'sha256', $salt, true );
	}
}
