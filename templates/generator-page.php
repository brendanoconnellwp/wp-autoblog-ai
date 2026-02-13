<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var array $data */
?>
<div class="wrap autoblog-ai-generator">
	<h1><?php esc_html_e( 'AutoBlog AI — Article Generator', 'autoblog-ai' ); ?></h1>

	<div class="generator-columns">
		<!-- Left column: Form -->
		<div class="generator-form-column">
			<div class="autoblog-ai-card">
				<h2><?php esc_html_e( 'Generate Articles', 'autoblog-ai' ); ?></h2>

				<form id="autoblog-ai-generator-form">
					<div class="autoblog-ai-form-row">
						<label for="autoblog-ai-titles"><?php esc_html_e( 'Article Titles (one per line)', 'autoblog-ai' ); ?></label>
						<textarea id="autoblog-ai-titles" name="titles" rows="8" placeholder="<?php esc_attr_e( "10 Best WordPress Plugins in 2025\nHow to Speed Up Your Website\nComplete Guide to SEO for Beginners", 'autoblog-ai' ); ?>"></textarea>
					</div>

					<div class="autoblog-ai-form-inline">
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-word-count"><?php esc_html_e( 'Word Count', 'autoblog-ai' ); ?></label>
							<input type="number" id="autoblog-ai-word-count" name="word_count"
								value="<?php echo esc_attr( $data['word_count'] ); ?>"
								min="300" max="10000" step="100">
						</div>
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-article-type"><?php esc_html_e( 'Article Type', 'autoblog-ai' ); ?></label>
							<select id="autoblog-ai-article-type" name="article_type">
								<option value="blog_post" <?php selected( $data['article_type'], 'blog_post' ); ?>><?php esc_html_e( 'Blog Post', 'autoblog-ai' ); ?></option>
								<option value="listicle" <?php selected( $data['article_type'], 'listicle' ); ?>><?php esc_html_e( 'Listicle', 'autoblog-ai' ); ?></option>
								<option value="how_to" <?php selected( $data['article_type'], 'how_to' ); ?>><?php esc_html_e( 'How-To Guide', 'autoblog-ai' ); ?></option>
								<option value="review" <?php selected( $data['article_type'], 'review' ); ?>><?php esc_html_e( 'Review', 'autoblog-ai' ); ?></option>
								<option value="comparison" <?php selected( $data['article_type'], 'comparison' ); ?>><?php esc_html_e( 'Comparison', 'autoblog-ai' ); ?></option>
								<option value="news" <?php selected( $data['article_type'], 'news' ); ?>><?php esc_html_e( 'News Article', 'autoblog-ai' ); ?></option>
							</select>
						</div>
					</div>

					<div class="autoblog-ai-form-inline">
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-tone"><?php esc_html_e( 'Tone', 'autoblog-ai' ); ?></label>
							<select id="autoblog-ai-tone" name="tone">
								<option value="informative" <?php selected( $data['tone'], 'informative' ); ?>><?php esc_html_e( 'Informative', 'autoblog-ai' ); ?></option>
								<option value="conversational" <?php selected( $data['tone'], 'conversational' ); ?>><?php esc_html_e( 'Conversational', 'autoblog-ai' ); ?></option>
								<option value="professional" <?php selected( $data['tone'], 'professional' ); ?>><?php esc_html_e( 'Professional', 'autoblog-ai' ); ?></option>
								<option value="casual" <?php selected( $data['tone'], 'casual' ); ?>><?php esc_html_e( 'Casual', 'autoblog-ai' ); ?></option>
								<option value="academic" <?php selected( $data['tone'], 'academic' ); ?>><?php esc_html_e( 'Academic', 'autoblog-ai' ); ?></option>
								<option value="persuasive" <?php selected( $data['tone'], 'persuasive' ); ?>><?php esc_html_e( 'Persuasive', 'autoblog-ai' ); ?></option>
							</select>
						</div>
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-pov"><?php esc_html_e( 'Point of View', 'autoblog-ai' ); ?></label>
							<select id="autoblog-ai-pov" name="pov">
								<option value="first" <?php selected( $data['pov'], 'first' ); ?>><?php esc_html_e( 'First Person', 'autoblog-ai' ); ?></option>
								<option value="second" <?php selected( $data['pov'], 'second' ); ?>><?php esc_html_e( 'Second Person', 'autoblog-ai' ); ?></option>
								<option value="third" <?php selected( $data['pov'], 'third' ); ?>><?php esc_html_e( 'Third Person', 'autoblog-ai' ); ?></option>
							</select>
						</div>
					</div>

					<div class="autoblog-ai-form-inline">
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-faq-count"><?php esc_html_e( 'FAQ Count', 'autoblog-ai' ); ?></label>
							<input type="number" id="autoblog-ai-faq-count" name="faq_count"
								value="<?php echo esc_attr( $data['faq_count'] ); ?>"
								min="0" max="10">
						</div>
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-takeaway-count"><?php esc_html_e( 'Key Takeaways', 'autoblog-ai' ); ?></label>
							<input type="number" id="autoblog-ai-takeaway-count" name="takeaway_count"
								value="<?php echo esc_attr( $data['takeaway_count'] ); ?>"
								min="0" max="10">
						</div>
					</div>

					<div class="autoblog-ai-form-row">
						<label for="autoblog-ai-post-status"><?php esc_html_e( 'Post Status', 'autoblog-ai' ); ?></label>
						<select id="autoblog-ai-post-status" name="post_status">
							<option value="draft"><?php esc_html_e( 'Draft', 'autoblog-ai' ); ?></option>
							<option value="publish"><?php esc_html_e( 'Published', 'autoblog-ai' ); ?></option>
							<option value="pending"><?php esc_html_e( 'Pending Review', 'autoblog-ai' ); ?></option>
						</select>
					</div>

					<div class="autoblog-ai-form-row">
						<label for="autoblog-ai-category"><?php esc_html_e( 'Category', 'autoblog-ai' ); ?></label>
						<select id="autoblog-ai-category" name="category">
							<option value=""><?php esc_html_e( '— None —', 'autoblog-ai' ); ?></option>
							<?php foreach ( $data['categories'] as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="autoblog-ai-form-row">
						<label for="autoblog-ai-tags"><?php esc_html_e( 'Tags (comma-separated)', 'autoblog-ai' ); ?></label>
						<input type="text" id="autoblog-ai-tags" name="tags" class="regular-text"
							placeholder="<?php esc_attr_e( 'wordpress, seo, blogging', 'autoblog-ai' ); ?>">
					</div>

					<div class="autoblog-ai-form-inline">
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-image-provider"><?php esc_html_e( 'Image Generation', 'autoblog-ai' ); ?></label>
							<select id="autoblog-ai-image-provider" name="image_provider">
								<option value="none" <?php selected( $data['image_provider'], 'none' ); ?>><?php esc_html_e( 'None', 'autoblog-ai' ); ?></option>
								<option value="dall-e" <?php selected( $data['image_provider'], 'dall-e' ); ?>><?php esc_html_e( 'DALL-E', 'autoblog-ai' ); ?></option>
								<option value="stability" <?php selected( $data['image_provider'], 'stability' ); ?>><?php esc_html_e( 'Stability AI', 'autoblog-ai' ); ?></option>
							</select>
						</div>
						<div class="autoblog-ai-form-row">
							<label for="autoblog-ai-image-style"><?php esc_html_e( 'Image Style', 'autoblog-ai' ); ?></label>
							<select id="autoblog-ai-image-style" name="image_style">
								<option value="photorealistic" <?php selected( $data['image_style'], 'photorealistic' ); ?>><?php esc_html_e( 'Photorealistic', 'autoblog-ai' ); ?></option>
								<option value="illustration" <?php selected( $data['image_style'], 'illustration' ); ?>><?php esc_html_e( 'Illustration', 'autoblog-ai' ); ?></option>
								<option value="3d_render" <?php selected( $data['image_style'], '3d_render' ); ?>><?php esc_html_e( '3D Render', 'autoblog-ai' ); ?></option>
								<option value="digital_art" <?php selected( $data['image_style'], 'digital_art' ); ?>><?php esc_html_e( 'Digital Art', 'autoblog-ai' ); ?></option>
								<option value="watercolor" <?php selected( $data['image_style'], 'watercolor' ); ?>><?php esc_html_e( 'Watercolor', 'autoblog-ai' ); ?></option>
							</select>
						</div>
					</div>

					<div class="autoblog-ai-form-row">
						<label>
							<input type="checkbox" id="autoblog-ai-internal-linking" name="internal_linking" value="1"
								<?php checked( $data['internal_linking'], '1' ); ?>>
							<?php esc_html_e( 'Enable internal linking', 'autoblog-ai' ); ?>
						</label>
					</div>

					<div class="autoblog-ai-submit">
						<button type="submit" class="button button-primary button-large" id="autoblog-ai-submit-btn">
							<?php esc_html_e( 'Generate Articles', 'autoblog-ai' ); ?>
						</button>
						<span class="spinner" id="autoblog-ai-spinner"></span>
					</div>
				</form>
			</div>
		</div>

		<!-- Right column: Queue -->
		<div class="generator-queue-column">
			<div class="autoblog-ai-card">
				<h2><?php esc_html_e( 'Generation Queue', 'autoblog-ai' ); ?></h2>
				<div id="autoblog-ai-queue">
					<div class="autoblog-ai-queue-empty">
						<?php esc_html_e( 'No articles in the queue. Submit titles to get started.', 'autoblog-ai' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
