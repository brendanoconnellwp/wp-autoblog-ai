<?php

namespace Autoblog_AI\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats article titles: proper title case and basic cleanup.
 */
class Title_Formatter {

	/**
	 * Words that should remain lowercase in title case (AP style)
	 * unless they are the first or last word.
	 */
	private const LOWERCASE_WORDS = array(
		'a', 'an', 'the',                          // articles
		'and', 'but', 'or', 'nor', 'for', 'yet', 'so', // conjunctions
		'at', 'by', 'in', 'of', 'on', 'to', 'up',      // short prepositions
		'as', 'if', 'is', 'it', 'vs', 'via',
	);

	/**
	 * Format a title with proper capitalization and cleanup.
	 *
	 * @param string $title Raw title input.
	 * @return string Formatted title.
	 */
	public static function format( string $title ): string {
		$title = self::cleanup( $title );

		if ( '' === $title ) {
			return '';
		}

		$title = self::title_case( $title );

		/**
		 * Filter the formatted title.
		 *
		 * @param string $title Formatted title.
		 */
		return apply_filters( 'autoblog_ai_format_title', $title );
	}

	/**
	 * Basic text cleanup: trim, collapse whitespace, fix punctuation spacing.
	 */
	private static function cleanup( string $title ): string {
		// Trim and collapse multiple spaces.
		$title = trim( preg_replace( '/\s+/', ' ', $title ) );

		// Remove leading/trailing punctuation that isn't meaningful.
		$title = trim( $title, " \t\n\r\0\x0B." );

		// Fix space before punctuation: "word :" → "word:".
		$title = preg_replace( '/\s+([,:;!?])/', '$1', $title );

		// Ensure space after punctuation when followed by a letter: "word:next" → "word: next".
		$title = preg_replace( '/([,:;])([A-Za-z])/', '$1 $2', $title );

		return $title;
	}

	/**
	 * Apply AP-style title case.
	 */
	private static function title_case( string $title ): string {
		// Split on spaces while preserving the delimiters.
		$words = explode( ' ', $title );

		if ( empty( $words ) ) {
			return $title;
		}

		$last_index = count( $words ) - 1;

		foreach ( $words as $i => &$word ) {
			if ( '' === $word ) {
				continue;
			}

			// Preserve all-caps acronyms (2-4 chars like "SEO", "AI", "API", "HTML").
			if ( preg_match( '/^[A-Z]{2,5}$/', $word ) ) {
				continue;
			}

			// Preserve words with intentional mixed case (e.g., "WordPress", "iPhone").
			if ( preg_match( '/^[a-z]+[A-Z]/', $word ) || preg_match( '/^[A-Z][a-z]+[A-Z]/', $word ) ) {
				continue;
			}

			// Check for hyphenated words — capitalize each part.
			if ( str_contains( $word, '-' ) ) {
				$word = self::title_case_hyphenated( $word );
				continue;
			}

			$lower = strtolower( $word );

			// First and last words are always capitalized.
			if ( 0 === $i || $i === $last_index ) {
				$word = ucfirst( $lower );
				continue;
			}

			// Capitalize after a colon (sub-title).
			if ( $i > 0 && str_ends_with( $words[ $i - 1 ], ':' ) ) {
				$word = ucfirst( $lower );
				continue;
			}

			// Keep lowercase words lowercase.
			if ( in_array( $lower, self::LOWERCASE_WORDS, true ) ) {
				$word = $lower;
				continue;
			}

			// Capitalize first letter of all other words.
			$word = ucfirst( $lower );
		}

		return implode( ' ', $words );
	}

	/**
	 * Title-case a hyphenated word (e.g., "step-by-step" → "Step-by-Step").
	 */
	private static function title_case_hyphenated( string $word ): string {
		$parts      = explode( '-', $word );
		$last_index = count( $parts ) - 1;

		foreach ( $parts as $i => &$part ) {
			$lower = strtolower( $part );

			if ( 0 === $i || $i === $last_index ) {
				$part = ucfirst( $lower );
			} elseif ( in_array( $lower, self::LOWERCASE_WORDS, true ) ) {
				$part = $lower;
			} else {
				$part = ucfirst( $lower );
			}
		}

		return implode( '-', $parts );
	}
}
