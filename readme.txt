=== AutoBlog AI ===
Contributors: brendanoconnell
Tags: ai, content generation, blogging, openai, gemini
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.3.2
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

* WordPress 7.0+ (or WordPress 6.4+ with the wp-ai-client plugin)
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

== Privacy ==

This plugin sends data to external AI services to generate content:

* **Text generation**: Article titles, writing prompts, and internal-link context are sent to the AI provider configured in WordPress AI Client (e.g., OpenAI, Anthropic, or Google Gemini).
* **Image generation (DALL-E)**: Image prompts derived from article titles are sent to OpenAI via the WordPress AI Client.
* **Image generation (Stability AI)**: Image prompts derived from article titles are sent directly to the Stability AI API (`api.stability.ai`).

No data is sent unless you actively trigger article generation. Please review each provider's privacy policy and terms of service before use.

== Changelog ==

= 1.3.2 =
* Fixed minimum WordPress version requirement (now 7.0+).
* Fixed async queue jobs attributing posts to the wrong author.
* Added i18n bootstrapping with load_plugin_textdomain.
* Added privacy disclosure for external AI service usage.
* Improved queue insert error handling.
* Upgraded encryption from AES-CBC to AES-GCM with backwards compatibility.
* Added automatic DB migration system for schema upgrades.
* Added batch size limit (max 50 titles) and within-batch dedup.
* Added concurrency limiter for background article generation.
* Added queue cleanup: auto-prune after 30 days, bulk "Clear Finished" button.
* Added admin notice when WordPress AI Client dependency is missing.
* Rewrote Block_Converter to use DOMDocument for reliable HTML parsing.
* Optimized Internal_Linker with query caching and reduced meta/term cache loading.
* Improved JS error handling for expired nonces and server error messages.
* Multisite-aware uninstall cleanup.

= 1.3.0 =
* Migrated to WordPress 7.0 native AI Client API.
* Fixed timeout handling for long article generation.

= 1.2.0 =
* Added GitHub release workflow.
* Improved timeout handling with http_request_timeout filter.

= 1.1.0 =
* Internal improvements.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.3.2 =
Fixes author attribution on async jobs, raises minimum WP to 7.0, adds queue cleanup and concurrency limits. DB schema upgrades automatically.

= 1.3.0 =
Requires WordPress 7.0+ or the wp-ai-client plugin for text generation.
