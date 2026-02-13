<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stability_key_raw = get_option( 'autoblog_ai_stability_api_key', '' );
$stability_key_display = '';
if ( '' !== $stability_key_raw ) {
	$decrypted = \Autoblog_AI\Security\Encryption::decrypt( $stability_key_raw );
	if ( '' !== $decrypted ) {
		$stability_key_display = '••••••••' . substr( $decrypted, -4 );
	}
}
?>
<div class="wrap autoblog-ai-settings">
	<h1><?php esc_html_e( 'AutoBlog AI Settings', 'autoblog-ai' ); ?></h1>

	<div class="autoblog-ai-notice">
		<p>
			<?php
			printf(
				/* translators: %s: link to AI Credentials settings */
				esc_html__( 'Text generation API keys (OpenAI, Anthropic, Gemini) are managed in %s via the WordPress AI Client SDK.', 'autoblog-ai' ),
				'<a href="' . esc_url( admin_url( 'options-general.php?page=ai-credentials' ) ) . '">' . esc_html__( 'Settings &rarr; AI Credentials', 'autoblog-ai' ) . '</a>'
			);
			?>
		</p>
	</div>

	<form method="post" action="options.php">
		<?php settings_fields( 'autoblog_ai_settings' ); ?>

		<h2><?php esc_html_e( 'Content Defaults', 'autoblog-ai' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="autoblog_ai_word_count"><?php esc_html_e( 'Word Count', 'autoblog-ai' ); ?></label></th>
				<td>
					<input type="number" id="autoblog_ai_word_count" name="autoblog_ai_word_count"
						value="<?php echo esc_attr( get_option( 'autoblog_ai_word_count', 1500 ) ); ?>"
						min="300" max="10000" step="100" class="small-text">
					<p class="description"><?php esc_html_e( 'Target word count per article.', 'autoblog-ai' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_tone"><?php esc_html_e( 'Tone', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $tone = get_option( 'autoblog_ai_tone', 'informative' ); ?>
					<select id="autoblog_ai_tone" name="autoblog_ai_tone">
						<option value="informative" <?php selected( $tone, 'informative' ); ?>><?php esc_html_e( 'Informative', 'autoblog-ai' ); ?></option>
						<option value="conversational" <?php selected( $tone, 'conversational' ); ?>><?php esc_html_e( 'Conversational', 'autoblog-ai' ); ?></option>
						<option value="professional" <?php selected( $tone, 'professional' ); ?>><?php esc_html_e( 'Professional', 'autoblog-ai' ); ?></option>
						<option value="casual" <?php selected( $tone, 'casual' ); ?>><?php esc_html_e( 'Casual', 'autoblog-ai' ); ?></option>
						<option value="academic" <?php selected( $tone, 'academic' ); ?>><?php esc_html_e( 'Academic', 'autoblog-ai' ); ?></option>
						<option value="persuasive" <?php selected( $tone, 'persuasive' ); ?>><?php esc_html_e( 'Persuasive', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_pov"><?php esc_html_e( 'Point of View', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $pov = get_option( 'autoblog_ai_pov', 'third' ); ?>
					<select id="autoblog_ai_pov" name="autoblog_ai_pov">
						<option value="first" <?php selected( $pov, 'first' ); ?>><?php esc_html_e( 'First Person (I/We)', 'autoblog-ai' ); ?></option>
						<option value="second" <?php selected( $pov, 'second' ); ?>><?php esc_html_e( 'Second Person (You)', 'autoblog-ai' ); ?></option>
						<option value="third" <?php selected( $pov, 'third' ); ?>><?php esc_html_e( 'Third Person', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_article_type"><?php esc_html_e( 'Article Type', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $article_type = get_option( 'autoblog_ai_article_type', 'blog_post' ); ?>
					<select id="autoblog_ai_article_type" name="autoblog_ai_article_type">
						<option value="blog_post" <?php selected( $article_type, 'blog_post' ); ?>><?php esc_html_e( 'Blog Post', 'autoblog-ai' ); ?></option>
						<option value="listicle" <?php selected( $article_type, 'listicle' ); ?>><?php esc_html_e( 'Listicle', 'autoblog-ai' ); ?></option>
						<option value="how_to" <?php selected( $article_type, 'how_to' ); ?>><?php esc_html_e( 'How-To Guide', 'autoblog-ai' ); ?></option>
						<option value="review" <?php selected( $article_type, 'review' ); ?>><?php esc_html_e( 'Review', 'autoblog-ai' ); ?></option>
						<option value="comparison" <?php selected( $article_type, 'comparison' ); ?>><?php esc_html_e( 'Comparison', 'autoblog-ai' ); ?></option>
						<option value="news" <?php selected( $article_type, 'news' ); ?>><?php esc_html_e( 'News Article', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_faq_count"><?php esc_html_e( 'FAQ Count', 'autoblog-ai' ); ?></label></th>
				<td>
					<input type="number" id="autoblog_ai_faq_count" name="autoblog_ai_faq_count"
						value="<?php echo esc_attr( get_option( 'autoblog_ai_faq_count', 3 ) ); ?>"
						min="0" max="10" class="small-text">
					<p class="description"><?php esc_html_e( 'Number of FAQ items to include (0 to disable).', 'autoblog-ai' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_takeaway_count"><?php esc_html_e( 'Key Takeaways', 'autoblog-ai' ); ?></label></th>
				<td>
					<input type="number" id="autoblog_ai_takeaway_count" name="autoblog_ai_takeaway_count"
						value="<?php echo esc_attr( get_option( 'autoblog_ai_takeaway_count', 3 ) ); ?>"
						min="0" max="10" class="small-text">
					<p class="description"><?php esc_html_e( 'Number of key takeaways to include (0 to disable).', 'autoblog-ai' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Image Generation', 'autoblog-ai' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="autoblog_ai_image_provider"><?php esc_html_e( 'Image Provider', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $image_provider = get_option( 'autoblog_ai_image_provider', 'none' ); ?>
					<select id="autoblog_ai_image_provider" name="autoblog_ai_image_provider">
						<option value="none" <?php selected( $image_provider, 'none' ); ?>><?php esc_html_e( 'None (no images)', 'autoblog-ai' ); ?></option>
						<option value="dall-e" <?php selected( $image_provider, 'dall-e' ); ?>><?php esc_html_e( 'DALL-E (via WP AI SDK)', 'autoblog-ai' ); ?></option>
						<option value="stability" <?php selected( $image_provider, 'stability' ); ?>><?php esc_html_e( 'Stability AI', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_image_style"><?php esc_html_e( 'Image Style', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $image_style = get_option( 'autoblog_ai_image_style', 'photorealistic' ); ?>
					<select id="autoblog_ai_image_style" name="autoblog_ai_image_style">
						<option value="photorealistic" <?php selected( $image_style, 'photorealistic' ); ?>><?php esc_html_e( 'Photorealistic', 'autoblog-ai' ); ?></option>
						<option value="illustration" <?php selected( $image_style, 'illustration' ); ?>><?php esc_html_e( 'Illustration', 'autoblog-ai' ); ?></option>
						<option value="3d_render" <?php selected( $image_style, '3d_render' ); ?>><?php esc_html_e( '3D Render', 'autoblog-ai' ); ?></option>
						<option value="digital_art" <?php selected( $image_style, 'digital_art' ); ?>><?php esc_html_e( 'Digital Art', 'autoblog-ai' ); ?></option>
						<option value="watercolor" <?php selected( $image_style, 'watercolor' ); ?>><?php esc_html_e( 'Watercolor', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_stability_api_key"><?php esc_html_e( 'Stability AI API Key', 'autoblog-ai' ); ?></label></th>
				<td>
					<input type="password" id="autoblog_ai_stability_api_key" name="autoblog_ai_stability_api_key"
						value="<?php echo esc_attr( $stability_key_display ); ?>"
						class="regular-text" autocomplete="off">
					<p class="description"><?php esc_html_e( 'Required only if using Stability AI for image generation. Stored encrypted.', 'autoblog-ai' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Internal Linking', 'autoblog-ai' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Internal Linking', 'autoblog-ai' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="autoblog_ai_internal_linking" value="1"
							<?php checked( get_option( 'autoblog_ai_internal_linking', '1' ), '1' ); ?>>
						<?php esc_html_e( 'Automatically add internal links to generated articles', 'autoblog-ai' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_max_links"><?php esc_html_e( 'Max Links Per Article', 'autoblog-ai' ); ?></label></th>
				<td>
					<input type="number" id="autoblog_ai_max_links" name="autoblog_ai_max_links"
						value="<?php echo esc_attr( get_option( 'autoblog_ai_max_links', 3 ) ); ?>"
						min="1" max="20" class="small-text">
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
