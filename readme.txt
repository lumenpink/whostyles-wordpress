=== Whostyles ===
Contributors: whostyles
Tags: typography, comments, style, customization, styling, indieweb
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Safe, structured, data-driven typographic and cosmetic style syndication across WordPress posts and comments.

== Description ==

Whostyles adds support for the Whostyles V1 (Hash Protocol) specification to WordPress.

The Whostyles protocol abstracts personal typographic and cosmetic styles into an ultra-compact Base64
bitpacked hash (e.g. `{ws1:...}`). The host platform resolves this hash into safe design tokens, enforcing
accessible contrast ratios and blocking geometry-hijacking properties (like absolute positioning, floats,
or external asset execution).

This plugin automatically integrates the Whostyles protocol with:
1. **Comment Sections**: Automatically detects, sanitizes, and renders comments carrying Whostyles.
2. **Post Layouts**: Allows authors to specify an identity hash for individual posts or site-wide.
3. **Syndication discovery**: Automatically outputs the `X-Whostyle` HTTP Header and the `<meta name="whostyle">`
   discovery tags.

== Installation ==

1. Upload the `whostyles` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. (Optional) Run `composer install` inside the plugin folder to install development dependencies.

== Frequently Asked Questions ==

= Does this plugin execute raw CSS from users? =
No. The plugin decodes Whostyles hashes into numeric parameters and safe hex colors. These are applied strictly
as bounded CSS Custom Properties. The host theme remains fully in control of layout geometry.

= How do I define my site's Whostyle? =
You can define a site-wide Whostyle under Settings -> Whostyles, or configure it on a per-post basis.
