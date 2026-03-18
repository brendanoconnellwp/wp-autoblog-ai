<?php

namespace Autoblog_AI\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AES-256-GCM authenticated encryption for sensitive data (Stability AI key).
 *
 * Uses WP AUTH_KEY and SECURE_AUTH_KEY salts as the encryption key.
 * Backwards-compatible: transparently decrypts legacy AES-CBC values on read.
 */
class Encryption {

	private const CIPHER     = 'aes-256-gcm';
	private const LEGACY_CBC = 'aes-256-cbc';
	private const TAG_LENGTH = 16;

	/**
	 * Encrypt a plaintext value using AES-256-GCM.
	 */
	public static function encrypt( string $plaintext ): string {
		if ( '' === $plaintext ) {
			return '';
		}

		$key    = self::get_key();
		$iv_len = openssl_cipher_iv_length( self::CIPHER );
		$iv     = openssl_random_pseudo_bytes( $iv_len );
		$tag    = '';

		$encrypted = openssl_encrypt( $plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH );

		if ( false === $encrypted ) {
			return '';
		}

		// Format: "gcm:" prefix + base64( IV + tag + ciphertext ).
		return 'gcm:' . base64_encode( $iv . $tag . $encrypted );
	}

	/**
	 * Decrypt a stored value. Handles both GCM and legacy CBC formats.
	 */
	public static function decrypt( string $ciphertext ): string {
		if ( '' === $ciphertext ) {
			return '';
		}

		// GCM format.
		if ( str_starts_with( $ciphertext, 'gcm:' ) ) {
			return self::decrypt_gcm( substr( $ciphertext, 4 ) );
		}

		// Legacy CBC format (no prefix).
		return self::decrypt_cbc( $ciphertext );
	}

	/**
	 * Decrypt AES-256-GCM ciphertext.
	 */
	private static function decrypt_gcm( string $encoded ): string {
		$raw = base64_decode( $encoded, true );
		if ( false === $raw ) {
			return '';
		}

		$key    = self::get_key();
		$iv_len = openssl_cipher_iv_length( self::CIPHER );
		$min    = $iv_len + self::TAG_LENGTH;

		if ( strlen( $raw ) <= $min ) {
			return '';
		}

		$iv        = substr( $raw, 0, $iv_len );
		$tag       = substr( $raw, $iv_len, self::TAG_LENGTH );
		$encrypted = substr( $raw, $min );

		$decrypted = openssl_decrypt( $encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag );

		return false !== $decrypted ? $decrypted : '';
	}

	/**
	 * Decrypt legacy AES-256-CBC ciphertext (backwards compatibility).
	 */
	private static function decrypt_cbc( string $ciphertext ): string {
		$raw = base64_decode( $ciphertext, true );
		if ( false === $raw ) {
			return '';
		}

		$key    = self::get_key();
		$iv_len = openssl_cipher_iv_length( self::LEGACY_CBC );

		if ( strlen( $raw ) <= $iv_len ) {
			return '';
		}

		$iv        = substr( $raw, 0, $iv_len );
		$encrypted = substr( $raw, $iv_len );

		$decrypted = openssl_decrypt( $encrypted, self::LEGACY_CBC, $key, OPENSSL_RAW_DATA, $iv );

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
