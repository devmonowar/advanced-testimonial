=== Advanced Testimonial ===
Contributors: devmonowar
Tags: testimonials, customer reviews, social proof, testimonial block, review carousel
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Showcase customer testimonials and client reviews in beautiful grids, carousels, cards and more. Lightweight, block-ready and built for social proof.

== Description ==

Turn happy customers into your best marketing. Advanced Testimonial helps you collect trust, build social proof and increase conversions by showcasing real client reviews anywhere on your WordPress site — in beautiful grids, carousels, cards and more.

Manage reviews like regular posts, group them, and drop them onto any page with a shortcode, a Gutenberg block or an Elementor widget.

= Why Advanced Testimonial? =

* **Build credibility & social proof** — show authentic reviews with star ratings, photos, companies, locations and verified badges.
* **Increase conversions** — place persuasive testimonials on landing pages, product pages and checkout to turn visitors into customers.
* **Win in Google** — built-in **Schema.org Review markup** lets search engines show ⭐ star ratings as rich snippets, so your result stands out and earns more clicks.
* **Fast & lightweight** — frontend assets load only where they are used, with no jQuery and no bloat, so your pages stay fast.
* **Fits any site** — agencies, SaaS products, WooCommerce shops, restaurants, portfolios, healthcare and more.

= Everything you need =

* Seven display layouts: Grid, List, Card, Carousel, Marquee, Masonry and Spotlight.
* A no-build Gutenberg block, an Elementor widget and the `[advanced_testimonial]` shortcode.
* **Front-end submission form** — add `[at_form]` to any page so visitors can submit testimonials directly from your site. Submissions land as "Pending" for you to review before publishing, with spam protection (nonce, honeypot and rate-limiting) and an optional admin email notification.
* **Video testimonials** — paste a YouTube, Vimeo or self-hosted MP4 link on any testimonial; the card shows a play button and the video opens in a lightbox.
* Rich per-review fields: headline, rating (half-stars supported), company, designation, location, photo, company logo, review date, verified badge, website and social links (X/Twitter, LinkedIn, Facebook).
* Group filter tabs, a "Read more" toggle for long reviews and "Load more" batching.
* Ready-made demos you can import in one click from the built-in Demo Library.
* Handy Tools tab: one-click starter demo, JSON import/export of your testimonials, clear cache, reset settings and system info.
* Translation-ready, accessible (ARIA, keyboard, RTL) and built on the WordPress Coding Standards.

== Installation ==

1. Upload the `advanced-testimonial` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Add testimonials under the new **Testimonials** menu.
4. Display them anywhere with the `[advanced_testimonial]` shortcode or the **Advanced Testimonial** block.

== Screenshots ==

1. Grid layout — customer reviews in a responsive grid with star ratings, headlines and verified badges.
2. Carousel layout — a lightweight, swipeable slider with navigation arrows and dots.
3. Card layout — large, prominent single-column testimonial cards.
4. Fully responsive — testimonials look great on mobile right out of the box.
5. Marquee layout in action — animated preview of the continuous scroll with edge fades.
6. Group filter tabs in action — click any tab to instantly filter testimonials by group.
7. Testimonial edit screen — every review field (rating, company, logo, video, socials and more) in one clean meta box.
8. Front-end submission form — visitors submit reviews with built-in spam protection; you approve them from a Pending queue.
9. Demo Library — browse ready-made testimonial sets by category and import one (with images) in a single click.
10. Video testimonials — cards show a play button; the video opens in a lightbox (YouTube, Vimeo or self-hosted MP4).

== Frequently Asked Questions ==

= How do I display testimonials? =

Use the `[advanced_testimonial]` shortcode anywhere, or add the **Advanced Testimonial** block in the editor. Example: `[advanced_testimonial layout="grid" columns="3" group="clients" limit="9"]`.

= Which layouts are available? =

Grid, List, Card, Carousel, Marquee, Masonry and Spotlight. Set the `layout` attribute, or pick it in the block. The Marquee layout also has its own scroll speed, direction and card width controls (global defaults in Settings → Styles, with per-block overrides).

= How do I group testimonials? =

Create groups under **Testimonials → Groups**, assign testimonials to them, then filter with the `group` attribute, e.g. `[advanced_testimonial group="clients"]`.

= Can I change the colors and styling? =

Yes. Go to **Testimonials → Settings → Styles** for primary/accent/text colors, border radius, spacing, card shadow, button style, avatar shape (circle/rounded/square), rating icon (star or heart) and a default rating for reviews without one. Add your own rules under **Advanced → Custom CSS**.

= Can I display specific testimonials only? =

Yes — pass their post IDs with the `ids` attribute: `[advanced_testimonial ids="12,34,56"]`. They are shown in the order you list them.

= Can I add video testimonials? =

Yes. Paste a YouTube, Vimeo or self-hosted MP4 URL in the testimonial's **Video URL** field. The card then shows a video thumbnail with a play button, and the video opens in a lightbox when clicked — nothing loads from YouTube or Vimeo until the visitor actually clicks play. Hide videos on a specific block or shortcode with `show_video="0"`.

= How do I show my best reviews first? =

Sort by rating with `[advanced_testimonial orderby="rating"]` (highest first), and show only verified reviews with `verified="1"`. Both options are also available in the block and the Elementor widget.

= How do I override the templates? =

Copy any file from the plugin's `templates/` folder into an `advanced-testimonial/` folder in your theme — for example `your-theme/advanced-testimonial/grid.php` or `your-theme/advanced-testimonial/item.php`. The plugin loads the theme copy automatically, so your customizations survive plugin updates.

= Does it work in the block editor? =

Yes — a no-build Gutenberg block with a live preview and full inspector controls (layout, group, columns, order, autoplay and show/hide toggles). It also supports Wide and Full alignment.

= Where do the demos come from? =

The Demo Library loads ready-made testimonial sets from a free online service provided by the plugin author, hosted on GitHub Pages: https://devmonowar.github.io/wp-plugin-demo-library/advanced-testimonial/ — you can browse every demo there. It is only contacted when you open the **Demo Library** screen or click **Import Demo** — no data from your site is ever sent to it; only public demo files (JSON and images) are downloaded into your Media Library. See the "External services" section below for full details and source code links. The plugin works completely without it — the Demo Library is optional.

= How do I collect testimonials from visitors? =

Add the `[at_form]` shortcode to any page. Visitors fill in their name, review, rating and optional details (company, job title, location, email). Each submission is saved as "Pending" so you can review it before it goes live — or turn on auto-publish under **Testimonials → Settings → Submission Form**.

You can control which fields appear, set a custom success message and receive an email notification for every new submission — all from **Testimonials → Settings → Submission Form**.

= Can I import or export my testimonials? =

Yes. **Testimonials → Settings → Tools** has a one-click JSON **Export** of your testimonials, groups and (optionally) plugin settings, and an **Import** that restores them on any site — handy for backups, staging or moving between sites. The same tab also offers a one-click starter demo, clear cache and reset settings.

= Will my data be deleted if I uninstall the plugin? =

No. Deactivating or deleting the plugin never removes your testimonials by default. If you *want* a full clean-up, enable "Remove all plugin data when deleting the plugin" under **Settings → Advanced** first — only then does deleting the plugin remove testimonials, groups, settings and transients.

= Do I need Elementor to use this plugin? =

No. The shortcode and the Gutenberg block work with any theme. If Elementor is installed, an **Advanced Testimonial** widget appears in the Elementor panel automatically — with the same layouts and controls.

= Will it slow down my site? =

No. CSS/JS load only on pages that actually display testimonials, there is no jQuery and no external libraries, queries are cached, and you can serve minified assets — all configurable under **Settings → Performance**.

= Are there hooks for developers? =

Yes. Filters: `advanced_testimonial_query_args`, `advanced_testimonial_item_data`, `advanced_testimonial_review_html`, `advanced_testimonial_output`, `advanced_testimonial_template`. Actions: `advanced_testimonial_before_card`, `advanced_testimonial_after_card`.

= Is it accessible and translation-ready? =

Yes — ARIA roles, keyboard navigation, visible focus styles, RTL support, and a bundled `.pot` file for translations.

== External services ==

This plugin includes an optional **Demo Library** that loads ready-made testimonial sets from a remote service hosted on GitHub Pages: https://devmonowar.github.io/wp-plugin-demo-library/

It connects to this service only when you:

* open the **Demo Library** screen — to download the list of demos and show their preview images; and
* click **Import Demo** — to download that demo's testimonials and images into your site's Media Library.

No data from your site is sent to the service; only public demo files (JSON and images) are downloaded. The service is provided by the plugin author. Source code: https://github.com/devmonowar/wp-plugin-demo-library — it is served by GitHub Pages, whose terms and privacy policy apply (https://docs.github.com/en/site-policy).

**Video testimonials (optional):** if you add a YouTube or Vimeo URL to a testimonial, the visitor's browser connects to those services — YouTube thumbnails are loaded from img.youtube.com, and the player (youtube-nocookie.com or player.vimeo.com) is only embedded after the visitor clicks play. No connection is made if you don't use video, and nothing is sent to these services by your site itself. YouTube is provided by Google (terms: https://www.youtube.com/t/terms — privacy: https://policies.google.com/privacy); Vimeo terms: https://vimeo.com/terms — privacy: https://vimeo.com/privacy.

== Changelog ==

= 2.0.6 =
* New: video testimonials — paste a YouTube, Vimeo or self-hosted MP4 URL and the card shows a play button that opens the video in a lightbox (`show_video` to toggle).
* New: sort by rating (`orderby="rating"`) and show verified reviews only (`verified="1"`) — in the shortcode, block and Elementor widget.
* Improved: Demo Library screen — full-width search above the category filters and a wider demo grid.

= 2.0.5 =
* New: front-end submission form — add `[at_form]` to any page. Spam protection (nonce, honeypot, rate limit), Pending-review queue, optional admin email notification, and a new Settings → Submission Form tab.

= 2.0.4 =
* New: Elementor widget.
* New: Marquee layout with scroll speed, direction and card-width controls.
* New: Headline / Review Title field per testimonial.
* New: half-star ratings (0.5 steps, e.g. 4.5).
* New: group filter tabs (`show_filter`).
* New: "Read more" for long reviews and "Load more" batching.
* New: Demo Library search box and category filters.
* Fix: carousel arrows advance one testimonial per click; settings page keeps the active tab after saving; Masonry no longer overlaps cards; minified assets fall back to the full files when missing.

= 2.0.3 =
* New: Demo Library "Refresh" button; Clear Cache also clears the demo list cache.

= 2.0.2 =
* New: Tools tab — starter demo, JSON import/export, clear cache, reset settings, debug mode and system info.
* New: Demo Library — one-click demo imports with images.
* New: optional heading (`title` attribute), avatar shape, rating icon and default rating settings.
* New: developer hooks and an opt-in uninstall clean-up option.
* Improved: accessibility (ARIA roles, keyboard focus styles) and admin conveniences (click-to-copy shortcodes, Settings link).

= 2.0.1 =
* Complete rewrite as a dedicated testimonial and customer-review plugin.
* Testimonial post type with groups and rich review fields.
* Six display layouts, `[advanced_testimonial]` shortcode and a no-build Gutenberg block.
* Settings page with full theming controls, Schema.org Review markup, accessibility, RTL and a lightweight vanilla-JS carousel.

== Upgrade Notice ==

= 2.0.6 =
Adds video testimonials (YouTube, Vimeo or MP4 in a lightbox), rating sort and a verified-only filter. Recommended update.

= 2.0.5 =
Adds a front-end submission form with spam protection and a Pending-review queue. Recommended update.

= 2.0.4 =
Big update: an Elementor widget, a new Marquee layout, half-star ratings, review headlines, group filters, Read more / Load more, and a searchable Demo Library. Recommended update.

= 2.0.3 =
Adds a Demo Library "Refresh" button and clears the demo cache from Tools → Clear Cache. Recommended update.

= 2.0.2 =
Adds a Tools tab, Demo Library with one-click imports, optional headings, more style controls and accessibility improvements. Recommended update.
