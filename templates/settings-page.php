<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$autoblog_ai_stability_key_raw = get_option( 'autoblog_ai_stability_api_key', '' );
$autoblog_ai_stability_key_display = '';
if ( '' !== $autoblog_ai_stability_key_raw ) {
	$autoblog_ai_decrypted = \Autoblog_AI\Security\Encryption::decrypt( $autoblog_ai_stability_key_raw );
	if ( '' !== $autoblog_ai_decrypted ) {
		$autoblog_ai_stability_key_display = '••••••••' . substr( $autoblog_ai_decrypted, -4 );
	}
}
?>
<div class="wrap autoblog-ai-settings">
	<h1><?php esc_html_e( 'AutoBlog AI Settings', 'autoblog-ai' ); ?></h1>

	<div class="autoblog-ai-notice">
		<p>
			<?php
			esc_html_e( 'Text generation API keys (OpenAI, Anthropic, Gemini) are managed by their respective AI Provider plugins. Install and configure at least one AI Provider plugin from the WordPress plugin directory.', 'autoblog-ai' );
			?>
		</p>
		<?php if ( function_exists( 'wp_ai_client_prompt' ) ) : ?>
			<p style="color: green;">&#10003; <?php esc_html_e( 'WordPress AI Client detected.', 'autoblog-ai' ); ?></p>
		<?php else : ?>
			<p style="color: red;">&#10007; <?php esc_html_e( 'WordPress AI Client not detected. Requires WordPress 7.0+ or the wp-ai-client plugin, plus at least one AI Provider plugin.', 'autoblog-ai' ); ?></p>
		<?php endif; ?>
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
					<?php $autoblog_ai_tone = get_option( 'autoblog_ai_tone', 'informative' ); ?>
					<select id="autoblog_ai_tone" name="autoblog_ai_tone">
						<option value="informative" <?php selected( $autoblog_ai_tone, 'informative' ); ?>><?php esc_html_e( 'Informative', 'autoblog-ai' ); ?></option>
						<option value="conversational" <?php selected( $autoblog_ai_tone, 'conversational' ); ?>><?php esc_html_e( 'Conversational', 'autoblog-ai' ); ?></option>
						<option value="professional" <?php selected( $autoblog_ai_tone, 'professional' ); ?>><?php esc_html_e( 'Professional', 'autoblog-ai' ); ?></option>
						<option value="casual" <?php selected( $autoblog_ai_tone, 'casual' ); ?>><?php esc_html_e( 'Casual', 'autoblog-ai' ); ?></option>
						<option value="academic" <?php selected( $autoblog_ai_tone, 'academic' ); ?>><?php esc_html_e( 'Academic', 'autoblog-ai' ); ?></option>
						<option value="persuasive" <?php selected( $autoblog_ai_tone, 'persuasive' ); ?>><?php esc_html_e( 'Persuasive', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_pov"><?php esc_html_e( 'Point of View', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $autoblog_ai_pov = get_option( 'autoblog_ai_pov', 'third' ); ?>
					<select id="autoblog_ai_pov" name="autoblog_ai_pov">
						<option value="first" <?php selected( $autoblog_ai_pov, 'first' ); ?>><?php esc_html_e( 'First Person (I/We)', 'autoblog-ai' ); ?></option>
						<option value="second" <?php selected( $autoblog_ai_pov, 'second' ); ?>><?php esc_html_e( 'Second Person (You)', 'autoblog-ai' ); ?></option>
						<option value="third" <?php selected( $autoblog_ai_pov, 'third' ); ?>><?php esc_html_e( 'Third Person', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_article_type"><?php esc_html_e( 'Article Type', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $autoblog_ai_article_type = get_option( 'autoblog_ai_article_type', 'blog_post' ); ?>
					<select id="autoblog_ai_article_type" name="autoblog_ai_article_type">
						<option value="blog_post" <?php selected( $autoblog_ai_article_type, 'blog_post' ); ?>><?php esc_html_e( 'Blog Post', 'autoblog-ai' ); ?></option>
						<option value="listicle" <?php selected( $autoblog_ai_article_type, 'listicle' ); ?>><?php esc_html_e( 'Listicle', 'autoblog-ai' ); ?></option>
						<option value="how_to" <?php selected( $autoblog_ai_article_type, 'how_to' ); ?>><?php esc_html_e( 'How-To Guide', 'autoblog-ai' ); ?></option>
						<option value="review" <?php selected( $autoblog_ai_article_type, 'review' ); ?>><?php esc_html_e( 'Review', 'autoblog-ai' ); ?></option>
						<option value="comparison" <?php selected( $autoblog_ai_article_type, 'comparison' ); ?>><?php esc_html_e( 'Comparison', 'autoblog-ai' ); ?></option>
						<option value="news" <?php selected( $autoblog_ai_article_type, 'news' ); ?>><?php esc_html_e( 'News Article', 'autoblog-ai' ); ?></option>
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
					<?php $autoblog_ai_image_provider = get_option( 'autoblog_ai_image_provider', 'none' ); ?>
					<select id="autoblog_ai_image_provider" name="autoblog_ai_image_provider">
						<option value="none" <?php selected( $autoblog_ai_image_provider, 'none' ); ?>><?php esc_html_e( 'None (no images)', 'autoblog-ai' ); ?></option>
						<option value="dall-e" <?php selected( $autoblog_ai_image_provider, 'dall-e' ); ?>><?php esc_html_e( 'DALL-E (via WP AI SDK)', 'autoblog-ai' ); ?></option>
						<option value="stability" <?php selected( $autoblog_ai_image_provider, 'stability' ); ?>><?php esc_html_e( 'Stability AI', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_image_style"><?php esc_html_e( 'Image Style', 'autoblog-ai' ); ?></label></th>
				<td>
					<?php $autoblog_ai_image_style = get_option( 'autoblog_ai_image_style', 'photorealistic' ); ?>
					<select id="autoblog_ai_image_style" name="autoblog_ai_image_style">
						<option value="photorealistic" <?php selected( $autoblog_ai_image_style, 'photorealistic' ); ?>><?php esc_html_e( 'Photorealistic', 'autoblog-ai' ); ?></option>
						<option value="illustration" <?php selected( $autoblog_ai_image_style, 'illustration' ); ?>><?php esc_html_e( 'Illustration', 'autoblog-ai' ); ?></option>
						<option value="3d_render" <?php selected( $autoblog_ai_image_style, '3d_render' ); ?>><?php esc_html_e( '3D Render', 'autoblog-ai' ); ?></option>
						<option value="digital_art" <?php selected( $autoblog_ai_image_style, 'digital_art' ); ?>><?php esc_html_e( 'Digital Art', 'autoblog-ai' ); ?></option>
						<option value="watercolor" <?php selected( $autoblog_ai_image_style, 'watercolor' ); ?>><?php esc_html_e( 'Watercolor', 'autoblog-ai' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="autoblog_ai_stability_api_key"><?php esc_html_e( 'Stability AI API Key', 'autoblog-ai' ); ?></label></th>
				<td>
					<input type="password" id="autoblog_ai_stability_api_key" name="autoblog_ai_stability_api_key"
						value="<?php echo esc_attr( $autoblog_ai_stability_key_display ); ?>"
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
					<input type="hidden" name="autoblog_ai_internal_linking" value="0">
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
			<tr>
				<th scope="row"><?php esc_html_e( 'Post Types to Link', 'autoblog-ai' ); ?></th>
				<td>
					<?php
					$autoblog_ai_available_types = get_post_types( array( 'public' => true ), 'objects' );
					unset( $autoblog_ai_available_types['attachment'] );

					$autoblog_ai_saved_types = get_option( 'autoblog_ai_linking_post_types', array() );
					$autoblog_ai_enabled_types = ( ! empty( $autoblog_ai_saved_types ) && is_array( $autoblog_ai_saved_types ) ) ? $autoblog_ai_saved_types : array( 'post', 'page' );

					foreach ( $autoblog_ai_available_types as $autoblog_ai_pt ) :
					?>
						<label style="display: block; margin-bottom: 4px;">
							<input type="checkbox"
								name="autoblog_ai_linking_post_types[]"
								value="<?php echo esc_attr( $autoblog_ai_pt->name ); ?>"
								<?php checked( in_array( $autoblog_ai_pt->name, $autoblog_ai_enabled_types, true ) ); ?>>
							<?php echo esc_html( $autoblog_ai_pt->labels->name ); ?>
							<code style="font-size: 12px; color: #666;">(<?php echo esc_html( $autoblog_ai_pt->name ); ?>)</code>
						</label>
					<?php endforeach; ?>
					<p class="description"><?php esc_html_e( 'Select which post types to include when searching for internal link targets.', 'autoblog-ai' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
