# AutoBlog AI

AI-powered bulk article generator for WordPress. Submit a list of titles, and AutoBlog AI generates full articles in the background using the WordPress AI Client SDK.

## Features

- **Bulk generation** — enter multiple titles, generate them all at once
- **Background queue** — articles are processed asynchronously via Action Scheduler
- **Writing controls** — tone, point of view, article type, word count, FAQs, key takeaways
- **Image generation** — optional featured images via DALL-E or Stability AI
- **Internal linking** — AI-driven linking to your existing content with configurable post types
- **Queue management** — real-time status, retry failed items, delete from queue

## Installation

1. Download the latest `.zip` from [Releases](https://github.com/brendanoconnellwp/wp-autoblog-ai/releases)
2. In WordPress: **Plugins → Add New → Upload Plugin** → upload the zip
3. Activate
4. Configure your AI provider credentials in **Settings → AI Credentials**

That's it. The release zip includes all dependencies — no Composer or CLI access needed.

## Requirements

- WordPress 6.4+
- PHP 8.0+
- A valid AI provider API key (OpenAI, Anthropic, or Google Gemini)

## Development

If you're working on the plugin itself:

```bash
git clone https://github.com/brendanoconnellwp/wp-autoblog-ai.git
cd wp-autoblog-ai
composer install
npm install        # optional, for CSS/JS build tooling
```

## Creating a release

Push a version tag to trigger the GitHub Actions build:

```bash
git tag v1.0.0
git push origin v1.0.0
```

This runs `composer install --no-dev`, packages the plugin into a zip, and creates a GitHub Release with the zip attached.

## License

GPL-2.0-or-later
