<?php

namespace Autoblog_AI\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds system and user prompts for AI text generation.
 */
class Prompt_Builder {

	/** @var array */
	private array $options;

	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * Build the system prompt.
	 */
	public function system_prompt(): string {
		$tone         = $this->options['tone'] ?? 'informative';
		$pov          = $this->pov_label();
		$article_type = $this->article_type_label();
		$faq_count    = (int) ( $this->options['faq_count'] ?? 0 );
		$takeaways    = (int) ( $this->options['takeaway_count'] ?? 0 );

		$parts = array();
		$parts[] = "You are an expert blog writer. Write in a {$tone} tone using {$pov} point of view.";
		$parts[] = "Article format: {$article_type}.";
		$parts[] = 'Write well-structured HTML content with proper headings (h2, h3), paragraphs, and lists where appropriate.';
		$parts[] = 'Do NOT include the article title as an h1 — WordPress handles that.';
		$parts[] = 'Use semantic HTML. Do not include <html>, <head>, or <body> tags.';

		if ( $faq_count > 0 ) {
			$parts[] = "Include a FAQ section at the end with exactly {$faq_count} questions and answers, using an h2 heading 'Frequently Asked Questions' and h3 for each question.";
		}

		if ( $takeaways > 0 ) {
			$parts[] = "Include a 'Key Takeaways' section near the top with exactly {$takeaways} bullet points summarizing the main points, using an h2 heading.";
		}

		$prompt = implode( "\n\n", $parts );

		/**
		 * Filter the system prompt.
		 *
		 * @param string $prompt  System prompt.
		 * @param array  $options Generation options.
		 */
		return apply_filters( 'autoblog_ai_system_prompt', $prompt, $this->options );
	}

	/**
	 * Build the user prompt.
	 *
	 * @param string $title          Article title.
	 * @param string $linking_context Optional context about related posts for internal linking.
	 */
	public function user_prompt( string $title, string $linking_context = '' ): string {
		$word_count = (int) ( $this->options['word_count'] ?? 1500 );

		$parts = array();
		$parts[] = "Write a comprehensive article titled: \"{$title}\"";
		$parts[] = "Target word count: approximately {$word_count} words.";

		if ( '' !== $linking_context ) {
			$parts[] = "When relevant, naturally reference these related topics from our site (you don't need to add links, just mention the topics naturally):\n{$linking_context}";
		}

		$parts[] = 'Return only the HTML article body content. No preamble or commentary.';

		$prompt = implode( "\n\n", $parts );

		/**
		 * Filter the user prompt.
		 *
		 * @param string $prompt  User prompt.
		 * @param string $title   Article title.
		 * @param array  $options Generation options.
		 */
		return apply_filters( 'autoblog_ai_user_prompt', $prompt, $title, $this->options );
	}

	/**
	 * Get a human-readable POV label.
	 */
	private function pov_label(): string {
		return match ( $this->options['pov'] ?? 'third' ) {
			'first'  => 'first person (I/we)',
			'second' => 'second person (you)',
			default  => 'third person',
		};
	}

	/**
	 * Get a human-readable article type label.
	 */
	private function article_type_label(): string {
		return match ( $this->options['article_type'] ?? 'blog_post' ) {
			'listicle'   => 'listicle (numbered list format)',
			'how_to'     => 'how-to guide with step-by-step instructions',
			'review'     => 'detailed review',
			'comparison' => 'comparison article',
			'news'       => 'news article',
			default      => 'standard blog post',
		};
	}
}
