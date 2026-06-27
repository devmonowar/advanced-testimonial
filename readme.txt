=== Advanced Testimonial ===
Contributors: devmonowar
Tags: testimonials, customer reviews, social proof, testimonial block, review carousel
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Showcase customer testimonials and client reviews in beautiful grids, carousels, cards and more. Lightweight, block-ready and built for social proof.

== Description ==

Advanced Testimonial is a dedicated testimonial and customer-review management plugin for WordPress. Manage reviews as regular posts, group them, and display them anywhere with a shortcode or Gutenberg block.

Key ideas:

* One plugin, one purpose — professional testimonial management, not a generic slider.
* Lightweight and performance-first: frontend assets load only when needed, no jQuery, no heavy libraries.
* Multiple display layouts: Grid, List, Card, Carousel, Masonry and Spotlight.
* Schema.org Review markup for richer search results.
* Translation-ready, accessible, and built on the WordPress Coding Standards.

== Installation ==

1. Upload the `advanced-testimonial` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Add testimonials under the new **Testimonials** menu.
4. Display them anywhere with the `[advanced_testimonial]` shortcode or the **Advanced Testimonial** block.

== Screenshots ==

1. Grid layout — testimonials in a responsive grid with star ratings and verified badges.
2. Carousel layout — a lightweight, swipeable slider with navigation and dots.
3. Spotlight layout — one large featured testimonial.
4. Gutenberg block with live preview and full inspector controls.
5. Settings page — colors, layout, performance and advanced options.

== Frequently Asked Questions ==

= How do I display testimonials? =

Use the `[advanced_testimonial]` shortcode anywhere, or add the **Advanced Testimonial** block in the editor. Example: `[advanced_testimonial layout="grid" columns="3" group="clients" limit="9"]`.

= Which layouts are available? =

Grid, List, Card, Carousel, Masonry and Spotlight. Set the `layout` attribute, or pick it in the block.

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

= 2.0.2 =
Adds a Tools tab, Demo Library with one-click imports, optional headings, more style controls and accessibility improvements. Recommended update.
