=== Advanced Testimonial ===
Contributors: devmonowar
Tags: testimonials, customer reviews, social proof, testimonial block, review carousel
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.4
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
* Rich per-review fields: headline, rating (half-stars supported), company, designation, location, photo, verified badge and website.
* Group filter tabs, a "Read more" toggle for long reviews and "Load more" batching.
* Ready-made demos you can import in one click from the built-in Demo Library.
* Translation-ready, accessible (ARIA, keyboard, RTL) and built on the WordPress Coding Standards.

== Installation ==

1. Upload the `advanced-testimonial` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Add testimonials under the new **Testimonials** menu.
4. Display them anywhere with the `[advanced_testimonial]` shortcode or the **Advanced Testimonial** block.

== Screenshots ==

1. Grid layout — customer reviews in a responsive grid with star ratings, headlines and verified badges.
2. Carousel layout — a lightweight, swipeable slider with navigation arrows and dots.
3. Marquee layout — a smooth, continuously scrolling row of testimonials with soft edge fades.
4. Card layout — large, prominent single-column testimonial cards.
5. Fully responsive — testimonials look great on mobile right out of the box.

== Frequently Asked Questions ==

= How do I display testimonials? =

Use the `[advanced_testimonial]` shortcode anywhere, or add the **Advanced Testimonial** block in the editor. Example: `[advanced_testimonial layout="grid" columns="3" group="clients" limit="9"]`.

= Which layouts are available? =

Grid, List, Card, Carousel, Marquee, Masonry and Spotlight. Set the `layout` attribute, or pick it in the block.

= How do I group testimonials? =

Create groups under **Testimonials → Groups**, assign testimonials to them, then filter with the `group` attribute, e.g. `[advanced_testimonial group="clients"]`.

= Can I change the colors and styling? =

Yes. Go to **Testimonials → Settings → Styles** for primary/accent/text colors, border radius, spacing, card shadow and button style. Add your own rules under **Advanced → Custom CSS**.

= How do I override the templates? =

Copy any file from the plugin's `templates/` folder into an `advanced-testimonial/` folder in your theme — for example `your-theme/advanced-testimonial/grid.php` or `your-theme/advanced-testimonial/item.php`. The plugin loads the theme copy automatically, so your customizations survive plugin updates.

= Does it work in the block editor? =

Yes — a no-build Gutenberg block with a live preview and full inspector controls (layout, group, columns, order, autoplay and show/hide toggles). It also supports Wide and Full alignment.

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

== Changelog ==

= 2.0.4 =
* New: an **Elementor widget** — build testimonial sections in Elementor with the same layouts and controls as the block and shortcode.
* New: a **Marquee** layout — a smooth, continuously scrolling row of testimonials that pauses on hover, with an optional soft edge fade and a choice of scroll direction (right-to-left or left-to-right). Scroll speed, card width and direction are set globally in Settings → Styles with per-block overrides. Reduced-motion friendly and lightweight, no jQuery.
* New: an optional per-testimonial **Headline / Review Title** field, shown above the review (block/shortcode `show_headline`), output with the Schema.org review name.
* New: **half-star ratings** — set any rating in 0.5 steps (e.g. 4.5 stars); the front-end renders a precise half star and the Schema.org `ratingValue` reflects the decimal.
* New: optional **group filter tabs** so visitors can filter testimonials by group (e.g. "All / Clients / Partners") on the Grid, List, Card and Masonry layouts (`show_filter`).
* New: optional **"Read more"** for long reviews and **"Load more"** to reveal testimonials in batches — both configurable in Settings → Styles and safe when JavaScript is off.
* New: the built-in **Demo Library** now has a search box and category filters so you can find a ready-made demo by use case.
* Fix: the **Carousel** now advances one testimonial per arrow/dot click instead of a whole page.
* Fix: the settings page keeps the active tab after **Save Changes** instead of jumping back to General.
* Fix: **Masonry** no longer overlaps cards, and adapts its column count so it never overcrowds a narrow content area.
* Fix: the "Use Minified Assets" option now falls back to the full CSS/JS when a minified file is not present, instead of failing to load.

= 2.0.3 =
* New: a "Refresh" button on the Demo Library screen to load the latest demos immediately, bypassing the 6-hour cache.
* Tools → Clear Cache now also clears the cached demo library list.

= 2.0.2 =
* New: Tools tab — one-click Starter Demo, demo Import/Export, Clear Cache, Reset Settings, Debug Mode and System Info.
* New: Demo Library — import ready-made testimonial sets (with images) from the online library, with an offline starter fallback.
* New: optional heading via the `title` shortcode attribute / block control, rendered inside the testimonials wrapper so it always aligns with the content.
* New: style settings — Avatar Shape, Rating Icon (star or heart) and Default Rating.
* New: click-to-copy shortcodes on the Groups list and a per-testimonial row action; a "Settings" link on the Plugins screen; a testimonial-specific menu icon; and an empty-state call to action.
* Accessibility: carousel/slide ARIA roles, screen-reader slide labels, corrected dot semantics, and visible keyboard focus styles.
* Developer hooks: new `advanced_testimonial_before_card` / `advanced_testimonial_after_card` actions and an `advanced_testimonial_review_html` filter, plus documented filters and template-override / FAQ docs.
* Uninstall: an opt-in "Remove all plugin data when deleting the plugin" option that removes testimonials, groups, meta, options and transients.
* Assets are versioned by file modification time so updates always bust browser caches.
* Verified clean against the WordPress Coding Standards, Plugin Check and i18n.

= 2.0.1 =
* Complete rewrite from the ground up as a dedicated testimonial and customer-review plugin.
* New: testimonial post type with groups and rich review fields (rating, company, designation, verified badge, logo, location, socials).
* New: six display layouts — Grid, List, Card, Carousel, Masonry and Spotlight.
* New: `[advanced_testimonial]` shortcode and a no-build Gutenberg block with live preview.
* New: Settings page (General, Styles, Performance, Advanced) with full theming controls.
* New: Schema.org Review markup, accessibility, RTL support and lightweight vanilla-JS carousel (no jQuery).
* Performance: frontend assets load only when testimonials are present.

== Upgrade Notice ==

= 2.0.4 =
Big update: an Elementor widget, a new Marquee layout, half-star ratings, review headlines, group filters, Read more / Load more, and a searchable Demo Library. Recommended update.

= 2.0.3 =
Adds a Demo Library "Refresh" button and clears the demo cache from Tools → Clear Cache. Recommended update.

= 2.0.2 =
Adds a Tools tab, Demo Library with one-click imports, optional headings, more style controls and accessibility improvements. Recommended update.
