=== Beaver Light Theme ===
Contributors: beaverbuilder
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.0-demo
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight theme built for Beaver Builder. Finished out of the box, out of the way once you start styling.

== Description ==

Beaver Light Theme (BLT) keeps a new site from looking broken before you style it: a plain, readable blog, single posts, search, archives, a 404 page and comments in system fonts on white, with no plugins required.

Style the site with Beaver Builder Global Styles or a Beaver Builder AI design system, and your fonts, colors and widths flow into the blog, archives and the built-in header and footer, one value at a time.

Working with an AI agent? `AGENTS.md` in the theme folder maps the theme's CSS variables, classes and hooks, so an agent can restyle it with a few lines of Additional CSS or build a child theme.

* No settings panels. Logo, site title, menus and Additional CSS come from WordPress itself.
* Beaver Themer ready: headers, footers and Parts, including Parts saved against the classic Beaver Builder Theme hooks.
* Builder pages run full width with no theme title; native pages get a comfortable reading width that lines up with your builder rows.
* No frameworks, no jQuery, no build step. Two small stylesheets and a one-kilobyte menu script.

== Frequently Asked Questions ==

= How do I customize it without code? =

Use Beaver Builder Global Styles or a Beaver Builder AI design system. Without Beaver Builder, set the theme's CSS variables (for example `--blt-primary`) on `:root` in Appearance → Customize → Additional CSS.

= Can I remove the header and footer? =

Use the No Header/Footer page template, or `add_filter( 'blt_header_footer', '__return_false' );` site-wide.

== Copyright ==

Beaver Light Theme, (C) 2026 The Beaver Builder Team. GPLv2 or later.

The BLT mascot (assets/images) is by Robby McCullough. Its license must be confirmed as GPL-compatible before a WordPress.org release.

