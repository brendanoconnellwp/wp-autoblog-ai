<?php

namespace Autoblog_AI\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts raw HTML into Gutenberg block markup.
 */
class Block_Converter {

	/**
	 * Convert HTML content to Gutenberg block markup.
	 *
	 * @param string $html Raw HTML content.
	 * @return string Block-formatted content.
	 */
	public static function convert( string $html ): string {
		// Strip wrapper tags that the AI might include.
		$html = self::strip_wrappers( $html );

		// Normalize whitespace between tags.
		$html = preg_replace( '/>\s+</', ">\n<", trim( $html ) );

		// Split into top-level elements and convert each to a block.
		$blocks = array();
		$chunks = self::split_top_level_elements( $html );

		foreach ( $chunks as $chunk ) {
			$chunk = trim( $chunk );
			if ( '' === $chunk ) {
				continue;
			}

			$block = self::element_to_block( $chunk );
			if ( '' !== $block ) {
				$blocks[] = $block;
			}
		}

		return implode( "\n\n", $blocks );
	}

	/**
	 * Strip wrapper elements (section, article, div, main) but keep their inner content.
	 */
	private static function strip_wrappers( string $html ): string {
		// Repeatedly strip outermost wrapper tags.
		$wrapper_tags = array( 'section', 'article', 'div', 'main', 'figure', 'header', 'footer' );
		$pattern      = '/^<(' . implode( '|', $wrapper_tags ) . ')[^>]*>(.*)<\/\1>\s*$/is';

		$prev = '';
		while ( $prev !== $html ) {
			$prev = $html;
			$html = preg_replace( $pattern, '$2', $html );
		}

		// Also strip inline wrapper tags that appear mid-content.
		foreach ( $wrapper_tags as $tag ) {
			$html = preg_replace( '/<' . $tag . '[^>]*>/i', '', $html );
			$html = preg_replace( '/<\/' . $tag . '>/i', '', $html );
		}

		return trim( $html );
	}

	/**
	 * Split HTML into top-level elements.
	 *
	 * Handles self-closing and block-level elements.
	 */
	private static function split_top_level_elements( string $html ): array {
		// Match top-level HTML elements (opening tag through closing tag) or text nodes.
		preg_match_all(
			'/<(p|h[1-6]|ul|ol|blockquote|table|pre|hr|img)(?:\s[^>]*)?>.*?<\/\1>|<(hr|img)(?:\s[^>]*)?\/?\s*>|[^<]+/is',
			$html,
			$matches
		);

		return $matches[0] ?? array();
	}

	/**
	 * Convert a single HTML element to its Gutenberg block equivalent.
	 */
	private static function element_to_block( string $element ): string {
		$element = trim( $element );

		// Skip plain text that isn't wrapped in a tag.
		if ( ! preg_match( '/^<(\w+)/', $element, $m ) ) {
			// If it's meaningful text, wrap it as a paragraph.
			$text = trim( strip_tags( $element ) );
			if ( '' === $text ) {
				return '';
			}
			return "<!-- wp:paragraph -->\n<p>{$element}</p>\n<!-- /wp:paragraph -->";
		}

		$tag = strtolower( $m[1] );

		return match ( $tag ) {
			'p'          => "<!-- wp:paragraph -->\n{$element}\n<!-- /wp:paragraph -->",
			'h2'         => "<!-- wp:heading -->\n{$element}\n<!-- /wp:heading -->",
			'h3'         => "<!-- wp:heading {\"level\":3} -->\n{$element}\n<!-- /wp:heading -->",
			'h4'         => "<!-- wp:heading {\"level\":4} -->\n{$element}\n<!-- /wp:heading -->",
			'h5'         => "<!-- wp:heading {\"level\":5} -->\n{$element}\n<!-- /wp:heading -->",
			'h6'         => "<!-- wp:heading {\"level\":6} -->\n{$element}\n<!-- /wp:heading -->",
			'ul'         => self::convert_list( $element, false ),
			'ol'         => self::convert_list( $element, true ),
			'blockquote' => "<!-- wp:quote -->\n{$element}\n<!-- /wp:quote -->",
			'table'      => "<!-- wp:table -->\n<figure class=\"wp-block-table\">{$element}</figure>\n<!-- /wp:table -->",
			'pre'        => "<!-- wp:preformatted -->\n{$element}\n<!-- /wp:preformatted -->",
			'hr'         => "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->",
			'img'        => self::convert_image( $element ),
			default      => $element,
		};
	}

	/**
	 * Convert a list element to a Gutenberg list block.
	 */
	private static function convert_list( string $html, bool $ordered ): string {
		// Extract list items and wrap each in wp:list-item.
		$inner = preg_replace_callback(
			'/<li[^>]*>(.*?)<\/li>/is',
			function ( $m ) {
				return "<!-- wp:list-item -->\n<li>{$m[1]}</li>\n<!-- /wp:list-item -->";
			},
			$html
		);

		// Replace the outer tag to include the inner block items.
		$tag = $ordered ? 'ol' : 'ul';
		$inner = preg_replace( '/<' . $tag . '[^>]*>/i', "<{$tag}>", $inner );

		$attrs = $ordered ? ' {"ordered":true}' : '';
		return "<!-- wp:list{$attrs} -->\n{$inner}\n<!-- /wp:list -->";
	}

	/**
	 * Convert an image element to a Gutenberg image block.
	 */
	private static function convert_image( string $html ): string {
		return "<!-- wp:image -->\n<figure class=\"wp-block-image\">{$html}</figure>\n<!-- /wp:image -->";
	}
}
