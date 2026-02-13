=== AutoBlog AI ===
Contributors: autoblogai
Tags: ai, autoblog, content generation, openai, claude, gemini
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered bulk article generator using the WordPress AI Client SDK. Supports OpenAI, Anthropic, and Google Gemini for text, plus DALL-E and Stability AI for images.

== Description ==

AutoBlog AI lets you generate high-quality blog articles in bulk using AI. It leverages the official WordPress AI Client SDK, so it works with whichever AI provider you've configured in WordPress — OpenAI, Anthropic (Claude), or Google Gemini.

**Features:**

* **Bulk article generation** — Enter multiple titles and generate them all at once
* **Multiple AI providers** — Works with OpenAI, Anthropic, and Google Gemini via the WP AI SDK
* **Image generation** — Featured images via DALL-E or Stability AI
* **Smart internal linking** — Automatically links to your existing content
* **Background processing** — Articles generate asynchronously via Action Scheduler
* **Customizable content** — Control tone, point of view, article type, word count, FAQs, and key takeaways
* **Queue management** — Monitor progress, retry failures, and manage the generation queue

**How it works:**

1. Configure your AI provider in WordPress Settings → AI Credentials
2. Visit AutoBlog AI → Article Generator
3. Enter article titles (one per line)
4. Customize generation settings (tone, word count, etc.)
5. Click "Generate Articles" and watch the queue process

== Installation ==

1. Upload the `autoblog-ai` folder to `/wp-content/plugins/`
2. Run `composer install` in the plugin directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your AI provider in Settings → AI Credentials
5. (Optional) Configure Stability AI key in AutoBlog AI → Settings

== Frequently Asked Questions ==

= What AI providers are supported? =

For text generation: OpenAI (GPT-4), Anthropic (Claude), and Google Gemini — all managed through the WordPress AI Client SDK.
For image generation: DALL-E (via the SDK) and Stability AI (direct API).

= Do I need API keys? =

Yes. Text generation API keys are managed in WordPress Settings → AI Credentials (provided by the WP AI SDK). If you want Stability AI images, add that key in AutoBlog AI → Settings.

= Can I customize the generated content? =

Yes. You can control the tone (informative, conversational, professional, etc.), point of view, article type (blog post, listicle, how-to, etc.), word count, number of FAQs, and key takeaways.

= What is internal linking? =

When enabled, AutoBlog AI scans your existing published content and automatically inserts relevant internal links into generated articles. This helps with SEO and site navigation.

= What happens if image generation fails? =

The article is still created without a featured image. The error is stored in post meta for debugging.

== Changelog ==

= 1.0.0 =
* Initial release
* Bulk article generation with queue management
* Support for OpenAI, Anthropic, and Google Gemini
* DALL-E and Stability AI image generation
* Smart internal linking
* Background processing via Action Scheduler

== Upgrade Notice ==

= 1.0.0 =
Initial release.
