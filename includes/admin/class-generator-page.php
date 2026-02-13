<?php

namespace Autoblog_AI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generator page logic: loads options and categories for the template.
 */
class Generator_Page {

	/**
	 * Render the generator page.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$data = array(
			'word_count'       => get_option( 'autoblog_ai_word_count', 1500 ),
			'tone'             => get_option( 'autoblog_ai_tone', 'informative' ),
			'pov'              => get_option( 'autoblog_ai_pov', 'third' ),
			'faq_count'        => get_option( 'autoblog_ai_faq_count', 3 ),
			'takeaway_count'   => get_option( 'autoblog_ai_takeaway_count', 3 ),
			'article_type'     => get_option( 'autoblog_ai_article_type', 'blog_post' ),
			'image_provider'   => get_option( 'autoblog_ai_image_provider', 'none' ),
			'image_style'      => get_option( 'autoblog_ai_image_style', 'photorealistic' ),
			'internal_linking' => get_option( 'autoblog_ai_internal_linking', '1' ),
			'max_links'        => get_option( 'autoblog_ai_max_links', 3 ),
			'categories'       => get_categories( array( 'hide_empty' => false ) ),
		);

		include AUTOBLOG_AI_PLUGIN_DIR . 'templates/generator-page.php';
	}
}
