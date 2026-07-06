# Advanced Testimonial

A modern, lightweight WordPress plugin for managing and displaying customer testimonials and reviews — built for social proof and business credibility.

[![CI](https://github.com/devmonowar/advanced-testimonial/actions/workflows/ci.yml/badge.svg)](https://github.com/devmonowar/advanced-testimonial/actions/workflows/ci.yml)

> **Note:** This is a complete, from-scratch rewrite. The plugin shares only the WordPress.org slug (`advanced-testimonial`) with the previous version — no code, data model or UI is carried over.

## Features

- **Testimonial post type** with hierarchical **Groups** and rich fields: rating, company, designation, website, verified badge, company logo, location, social links.
- **Seven layouts:** Grid, List, Card, Carousel, Marquee, Masonry, Spotlight.
- **Nine card styles:** Classic, Modern, Minimal, Bubble, Bold, Glass, Gradient, Outline, Retro — per block (`style` attribute) or site-wide default; extensible via the `advanced_testimonial_card_styles` filter.
- **Shortcode** `[advanced_testimonial]`, a **no-build Gutenberg block** with a live (ServerSideRender) preview, and an **Elementor widget**.
- **Front-end submission form** (`[at_form]`) — visitors submit testimonials that land as Pending for review, with nonce + honeypot + rate-limit spam protection and an optional admin email notification.
- **Video testimonials** — a per-testimonial Video URL (YouTube / Vimeo / MP4); cards show a play button and the video opens in a lightbox, with nothing loaded until the visitor clicks play.
- **Settings page** (General / Styles / Performance / Submission Form / Advanced) with full theming via CSS custom properties.
- **Schema.org Review** markup, accessibility (ARIA, keyboard), RTL support.
- **Performance-first:** frontend assets load only when testimonials are present; a tiny vanilla-JS carousel (no jQuery, no external libraries).
- **Developer friendly:** theme template overrides (`theme/advanced-testimonial/*.php`) and filters.

## Shortcode

```
[advanced_testimonial layout="grid" columns="3" group="clients" limit="9" order="desc"]
```

Attributes: `layout`, `style` (`classic`/`modern`/`minimal`/`bubble`/`bold`/`glass`/`gradient`/`outline`/`retro`), `width` (`wide`/`full`), `columns`, `limit`, `group`, `ids`, `order` (`asc`/`desc`/`random`), `orderby` (`date`/`title`/`menu_order`/`rating`), `verified` (`1` = verified only), `autoplay`, and `show_rating` / `show_image` / `show_company` / `show_designation` / `show_location` / `show_date` / `show_verified` / `show_website` / `show_video`.

Submission form: `[at_form title="Leave a review" group="clients"]` — attributes: `title`, `success` (custom success message), `group` (auto-assign submissions to a group).

## Architecture

- Namespace `AdvancedTestimonial`, PSR-4 autoload (with a bundled fallback loader, so `composer install` is not required at runtime).
- `includes/` core · `admin/` CPT, taxonomy, meta boxes, columns, settings · `frontend/` query, renderer, template loader, assets · `shortcode/` · `blocks/` (no-build) · `templates/` · `assets/`.

## Development

```bash
composer install     # dev tools (PHP_CodeSniffer + WPCS)
composer lint        # run coding-standards checks
composer lint:fix    # auto-fix where possible
```

PHP 7.4+ compatible. Coding standard: WordPress-Core (see `phpcs.xml.dist`).

## Release workflow

1. Bump the version in **4 places**: `advanced-testimonial.php` (header + `ADVANCED_TESTIMONIAL_VERSION`), `blocks/block.json`, `composer.json`, and `readme.txt` (`Stable tag` + changelog).
2. Commit and push to `main`. Wait for **CI** (lint + PHPCS) to pass.
3. Tag the release: `git tag X.Y.Z && git push origin X.Y.Z`. The **Deploy** workflow publishes it to WordPress.org.

Requires GitHub secrets `SVN_USERNAME` and `SVN_PASSWORD` (a WordPress.org account with commit access to the plugin).

## License

GPL-2.0-or-later.
