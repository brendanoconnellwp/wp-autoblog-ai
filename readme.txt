=== AutoBlog AI ===
Contributors: brendanoconnell
Tags: ai, content generation, blogging, openai, gemini, claude
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate blog articles in bulk with AI using a queued workflow, configurable writing controls, and optional featured image generation.

== Description ==

AutoBlog AI is a WordPress admin tool for generating multiple long-form articles from a list of titles. Jobs are queued and processed in the background, so you can submit a batch and let it run.

Text generation uses the WordPress AI Client SDK (BYOK model). Image generation supports DALL-E (via the SDK) and Stability AI (direct API).

= Core features =

* Bulk generation from multiple titles (one per line)
* Background queue processing with retry support
* Writing controls: tone, point of view, article type, word count, FAQs, and key takeaways
* Optional featured image generation (DALL-E or Stability AI)
* AI-driven internal linking to existing content with configurable post types
* Per-item queue visibility (queued, generating, complete, failed)

= Typical workflow =

1. Configure AI credentials in `Settings -> AI Credentials`.
2. Open `AutoBlog AI -> Article Generator`.
3. Add titles and choose generation options.
4. Submit the batch.
5. Monitor queue progress and retry failures if needed.

= Requirements =

* WordPress 6.4+
* PHP 8.0+
* Valid AI provider credentials for text generation
* Stability AI key only if using Stability for images

== Installation ==

1. Download the latest release zip from the [GitHub Releases page](https://github.com/brendanoconnellwp/wp-autoblog-ai/releases).
2. In WordPress, go to `Plugins -> Add New -> Upload Plugin` and upload the zip.
3. Activate the plugin.
4. Configure your text provider credentials in `Settings -> AI Credentials`.
5. (Optional) Add a Stability AI API key in `AutoBlog AI -> Settings`.

== Frequently Asked Questions ==

= Which AI providers are supported? =

For text: providers available through the WordPress AI Client SDK configuration (for example OpenAI, Anthropic, Google Gemini).
For images: DALL-E (SDK) and Stability AI (direct API).

= Do I need API keys? =

Yes. Text credentials are configured in `Settings -> AI Credentials`. Stability API key is only needed if `Stability AI` is selected as image provider.

= What happens if image generation fails? =

The article is still created. Image failures are stored so the queue item/post can be reviewed.

= How does queue processing work? =

Each submitted title becomes a queue item and is scheduled with Action Scheduler. Failed items can be retried up to 3 times.

= Can I disable internal linking? =

Yes. Internal linking can be toggled in the generator form and in `AutoBlog AI -> Settings`.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
