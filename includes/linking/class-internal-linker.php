<?php

namespace Autoblog_AI\Linking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal linker: finds relevant existing content and injects contextual links.
 *
 * Uses keyword extraction with stop-word removal and TF overlap scoring
 * to find the most relevant existing posts/pages to link to.
 */
class Internal_Linker {

	/**
	 * Common English stop words to exclude from keyword extraction.
	 */
	private const STOP_WORDS = array(
		'a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'was', 'were',
		'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did',
		'will', 'would', 'could', 'should', 'may', 'might', 'can', 'shall',
		'to', 'of', 'in', 'for', 'on', 'with', 'at', 'by', 'from', 'as',
		'into', 'about', 'like', 'through', 'after', 'over', 'between',
		'out', 'up', 'down', 'off', 'then', 'than', 'too', 'very', 'just',
		'also', 'not', 'no', 'so', 'if', 'this', 'that', 'it', 'its',
		'how', 'what', 'when', 'where', 'which', 'who', 'why',
		'all', 'each', 'every', 'both', 'few', 'more', 'most', 'other',
		'some', 'such', 'only', 'own', 'same', 'your', 'our', 'their',
		'you', 'we', 'they', 'he', 'she', 'me', 'him', 'her', 'us', 'them',
		'my', 'his', 'get', 'got', 'make', 'made', 'one', 'two', 'new',
		'best', 'top', 'good', 'great', 'first', 'last', 'long', 'way',
		'use', 'used', 'using', 'need', 'help', 'know', 'much', 'many',
	);

	/**
	 * Cached related posts per title to avoid redundant queries.
	 *
	 * @var array<string, \WP_Post[]>
	 */
	private array $cache = array();

	/**
	 * Get context string for the AI prompt with titles and URLs.
	 *
	 * Provides the AI with actual links it can embed in the generated HTML.
	 *
	 * @param string $title Article title being generated.
	 * @return string Context string listing related post titles and URLs.
	 */
	public function get_context_for_prompt( string $title ): string {
		$related = $this->find_related_posts( $title, 10 );

		if ( empty( $related ) ) {
			return '';
		}

		$lines = array();
		foreach ( $related as $post ) {
			$url     = get_permalink( $post->ID );
			$lines[] = '- "' . $post->post_title . '" (' . $url . ')';
		}

		return implode( "\n", $lines );
	}

	/**
	 * Inject internal links into HTML content.
	 *
	 * @param string $content   HTML content.
	 * @param string $title     Current article title (to exclude from linking).
	 * @param int    $max_links Maximum number of links to inject.
	 * @return string Content with links injected.
	 */
	public function inject_links( string $content, string $title, int $max_links = 3 ): string {
		$related = $this->find_related_posts( $title, $max_links * 2 );

		if ( empty( $related ) ) {
			return $content;
		}

		$links_added = 0;

		foreach ( $related as $post ) {
			if ( $links_added >= $max_links ) {
				break;
			}

			$keywords = $this->extract_keywords( $post->post_title );
			if ( empty( $keywords ) ) {
				continue;
			}

			// Try to find and link the best keyword phrase in the content.
			foreach ( $keywords as $keyword ) {
				if ( strlen( $keyword ) < 4 ) {
					continue;
				}

				$url     = get_permalink( $post->ID );
				$linked  = $this->link_first_occurrence( $content, $keyword, $url, $post->post_title );

				if ( $linked !== $content ) {
					$content = $linked;
					$links_added++;
					break; // Move to next post.
				}
			}
		}

		return $content;
	}

	/**
	 * Find related published posts by keyword overlap.
	 *
	 * Results are cached per title so that get_context_for_prompt() and
	 * inject_links() don't run duplicate queries for the same article.
	 *
	 * @param string $title     Reference title for relevance scoring.
	 * @param int    $max_posts Maximum posts to return.
	 * @return \WP_Post[]
	 */
	private function find_related_posts( string $title, int $max_posts ): array {
		$cache_key = strtolower( $title );

		if ( isset( $this->cache[ $cache_key ] ) ) {
			return array_slice( $this->cache[ $cache_key ], 0, $max_posts );
		}

		$title_keywords = $this->extract_keywords( $title );

		if ( empty( $title_keywords ) ) {
			$this->cache[ $cache_key ] = array();
			return array();
		}

		// Search for posts that contain any of our keywords.
		$search_term = implode( ' ', array_slice( $title_keywords, 0, 3 ) );
		$post_types  = self::get_linking_post_types();

		$query = new \WP_Query( array(
			'post_type'              => $post_types,
			'post_status'            => 'publish',
			's'                      => $search_term,
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );

		if ( ! $query->have_posts() ) {
			$this->cache[ $cache_key ] = array();
			return array();
		}

		// Score each post by keyword overlap with the title.
		$scored = array();
		foreach ( $query->posts as $post ) {
			$post_keywords = $this->extract_keywords( $post->post_title );
			$overlap       = array_intersect( $title_keywords, $post_keywords );
			$score         = count( $overlap );

			if ( $score > 0 ) {
				$scored[] = array(
					'post'  => $post,
					'score' => $score,
				);
			}
		}

		// Sort by score descending.
		usort( $scored, function ( $a, $b ) {
			return $b['score'] - $a['score'];
		} );

		$result = array();
		foreach ( $scored as $item ) {
			$result[] = $item['post'];
		}

		// Cache the full sorted list; callers slice to their own max.
		$this->cache[ $cache_key ] = $result;

		return array_slice( $result, 0, $max_posts );
	}

	/**
	 * Extract keywords from text, removing stop words.
	 *
	 * @param string $text Input text.
	 * @return string[] Lowercase keywords.
	 */
	private function extract_keywords( string $text ): array {
		$text  = strtolower( wp_strip_all_tags( $text ) );
		$text  = preg_replace( '/[^a-z0-9\s]/', '', $text );
		$words = preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );

		return array_values( array_diff( $words, self::STOP_WORDS ) );
	}

	/**
	 * Get the post types enabled for internal linking.
	 *
	 * @return string[]
	 */
	public static function get_linking_post_types(): array {
		$saved = get_option( 'autoblog_ai_linking_post_types', array() );

		if ( ! empty( $saved ) && is_array( $saved ) ) {
			return $saved;
		}

		return array( 'post', 'page' );
	}

	/**
	 * Get all public post types available for linking.
	 *
	 * @return \WP_Post_Type[]
	 */
	public static function get_available_post_types(): array {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		// Exclude attachments — they are not useful for internal linking.
		unset( $post_types['attachment'] );

		return $post_types;
	}

	/**
	 * Link the first occurrence of a keyword within paragraph text.
	 *
	 * Only links text inside <p> tags and avoids linking inside existing anchors.
	 *
	 * @param string $content  HTML content.
	 * @param string $keyword  Keyword to find.
	 * @param string $url      URL to link to.
	 * @param string $title    Link title attribute.
	 * @return string Modified content (unchanged if keyword not found in paragraphs).
	 */
	private function link_first_occurrence( string $content, string $keyword, string $url, string $title ): string {
		$pattern = '/(<p[^>]*>)(.*?)(<\/p>)/is';

		$linked = false;

		$result = preg_replace_callback( $pattern, function ( $matches ) use ( $keyword, $url, $title, &$linked ) {
			if ( $linked ) {
				return $matches[0];
			}

			$paragraph = $matches[2];

			// Skip if this paragraph already contains links.
			if ( str_contains( $paragraph, '<a ' ) ) {
				return $matches[0];
			}

			// Case-insensitive search for the keyword.
			$pos = stripos( $paragraph, $keyword );
			if ( false === $pos ) {
				return $matches[0];
			}

			$actual_text = substr( $paragraph, $pos, strlen( $keyword ) );
			$link        = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $actual_text ) . '</a>';

			$new_paragraph = substr_replace( $paragraph, $link, $pos, strlen( $keyword ) );
			$linked        = true;

			return $matches[1] . $new_paragraph . $matches[3];
		}, $content );

		return $result ?? $content;
	}
}
