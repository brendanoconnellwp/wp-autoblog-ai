<?php

namespace Autoblog_AI\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts raw HTML into Gutenberg block markup using DOMDocument
 * for reliable parsing of nested/malformed AI output.
 */
class Block_Converter {

	/**
	 * Wrapper tags that should be stripped (content kept, tag removed).
	 */
	private const WRAPPER_TAGS = array( 'section', 'article', 'div', 'main', 'figure', 'header', 'footer' );

	/**
	 * Convert HTML content to Gutenberg block markup.
	 *
	 * @param string $html Raw HTML content.
	 * @return string Block-formatted content.
	 */
	public static function convert( string $html ): string {
		$html = trim( $html );
		if ( '' === $html ) {
			return '';
		}

		$doc = self::load_html( $html );
		if ( ! $doc ) {
			// Fallback: return as a single paragraph block.
			return "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $html ) . "</p>\n<!-- /wp:paragraph -->";
		}

		$body = $doc->getElementsByTagName( 'body' )->item( 0 );
		if ( ! $body ) {
			return '';
		}

		// Flatten wrapper tags first.
		self::strip_wrappers( $doc, $body );

		$blocks = array();

		foreach ( $body->childNodes as $node ) {
			$block = self::node_to_block( $node, $doc );
			if ( '' !== $block ) {
				$blocks[] = $block;
			}
		}

		return implode( "\n\n", $blocks );
	}

	/**
	 * Load HTML into a DOMDocument.
	 */
	private static function load_html( string $html ): ?\DOMDocument {
		$doc = new \DOMDocument( '1.0', 'UTF-8' );

		// Wrap in a root element to handle fragments, force UTF-8.
		$wrapped = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';

		$prev = libxml_use_internal_errors( true );
		$doc->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );

		return $doc;
	}

	/**
	 * Recursively unwrap wrapper elements, replacing them with their children.
	 */
	private static function strip_wrappers( \DOMDocument $doc, \DOMNode $parent ): void {
		$to_unwrap = array();

		foreach ( $parent->childNodes as $child ) {
			if ( $child instanceof \DOMElement && in_array( strtolower( $child->tagName ), self::WRAPPER_TAGS, true ) ) {
				$to_unwrap[] = $child;
			}
		}

		foreach ( $to_unwrap as $wrapper ) {
			// Move all children of the wrapper before the wrapper itself.
			while ( $wrapper->firstChild ) {
				$wrapper->parentNode->insertBefore( $wrapper->firstChild, $wrapper );
			}
			$wrapper->parentNode->removeChild( $wrapper );
		}

		// Recurse in case wrappers were nested.
		if ( ! empty( $to_unwrap ) ) {
			self::strip_wrappers( $doc, $parent );
		}
	}

	/**
	 * Get the outer HTML of a DOMNode.
	 */
	private static function outer_html( \DOMNode $node, \DOMDocument $doc ): string {
		return $doc->saveHTML( $node );
	}

	/**
	 * Convert a single DOM node to its Gutenberg block equivalent.
	 */
	private static function node_to_block( \DOMNode $node, \DOMDocument $doc ): string {
		// Skip whitespace-only text nodes.
		if ( $node instanceof \DOMText ) {
			$text = trim( $node->textContent );
			if ( '' === $text ) {
				return '';
			}
			return "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->";
		}

		if ( ! ( $node instanceof \DOMElement ) ) {
			return '';
		}

		$tag  = strtolower( $node->tagName );
		$html = self::outer_html( $node, $doc );

		return match ( $tag ) {
			'p'          => "<!-- wp:paragraph -->\n{$html}\n<!-- /wp:paragraph -->",
			'h2'         => "<!-- wp:heading -->\n{$html}\n<!-- /wp:heading -->",
			'h3'         => "<!-- wp:heading {\"level\":3} -->\n{$html}\n<!-- /wp:heading -->",
			'h4'         => "<!-- wp:heading {\"level\":4} -->\n{$html}\n<!-- /wp:heading -->",
			'h5'         => "<!-- wp:heading {\"level\":5} -->\n{$html}\n<!-- /wp:heading -->",
			'h6'         => "<!-- wp:heading {\"level\":6} -->\n{$html}\n<!-- /wp:heading -->",
			'ul'         => self::convert_list( $node, $doc, false ),
			'ol'         => self::convert_list( $node, $doc, true ),
			'blockquote' => "<!-- wp:quote -->\n{$html}\n<!-- /wp:quote -->",
			'table'      => "<!-- wp:table -->\n<figure class=\"wp-block-table\">{$html}</figure>\n<!-- /wp:table -->",
			'pre'        => "<!-- wp:preformatted -->\n{$html}\n<!-- /wp:preformatted -->",
			'hr'         => "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->",
			'img'        => "<!-- wp:image -->\n<figure class=\"wp-block-image\">{$html}</figure>\n<!-- /wp:image -->",
			default      => $html,
		};
	}

	/**
	 * Convert a list element to a Gutenberg list block with wp:list-item wrappers.
	 */
	private static function convert_list( \DOMElement $list, \DOMDocument $doc, bool $ordered ): string {
		$tag   = $ordered ? 'ol' : 'ul';
		$inner = '';

		foreach ( $list->childNodes as $child ) {
			if ( $child instanceof \DOMElement && 'li' === strtolower( $child->tagName ) ) {
				$li_html = $doc->saveHTML( $child );
				$inner  .= "<!-- wp:list-item -->\n{$li_html}\n<!-- /wp:list-item -->\n";
			}
		}

		$attrs = $ordered ? ' {"ordered":true}' : '';
		return "<!-- wp:list{$attrs} -->\n<{$tag}>\n{$inner}</{$tag}>\n<!-- /wp:list -->";
	}
}
