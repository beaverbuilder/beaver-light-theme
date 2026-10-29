# Beaver Light Theme: guide for agents

Beaver Light Theme is a lightweight classic theme built for Beaver Builder. It has no settings screens. This file is the map for an agent helping someone style or extend it.

## Styling: which tool to use

Each style value resolves on its own, strongest first:

1. **Design system token.** When the site has a default Beaver Builder AI design system, its tokens (for example `--ds-color-primary`) win. Change those with Beaver Builder AI's design system abilities.
2. **Beaver Builder Global Styles.** Text, heading, link and button colors, and body and heading fonts. These also style Beaver Builder content, so they keep the whole site consistent.
3. **Theme default.**

On a Beaver Builder site, change the design system or Global Styles first. Use CSS only for touches neither one covers.

On a site without Beaver Builder, restyle the theme with CSS in Appearance → Customize → Additional CSS. Setting the custom properties below on `:root` restyles every theme surface at once, for example:

```css
:root {
	--blt-primary: #2d6a4f;
	--blt-link: #2d6a4f;
	--blt-font-heading: Georgia, serif;
	--blt-surface: #ffffff;
}
```

All theme CSS sits in cascade layers, so Additional CSS and any other unlayered CSS wins without extra specificity. The theme never styles inside Beaver Builder layouts (`.fl-builder-content`).

**Fonts.** The theme loads no remote fonts. Use system stacks (for example `Georgia, serif` or `system-ui, sans-serif`), or a family the user has installed under Appearance → Fonts (the WordPress Font Library), named exactly as installed with a fallback: `"Inter", system-ui, sans-serif`.

**Contrast.** Keep `--blt-text` on `--blt-surface` and `--blt-on-primary` on `--blt-primary` at a 4.5:1 contrast ratio or better.

## Layout facts

- The site column (header, footer, archives) follows Beaver Builder's global row width, so native pages line up with builder rows. Change it in Beaver Builder → Global Settings → Rows.
- Posts and plain pages use a reading width of about 46rem.
- A page or post built with Beaver Builder renders full width with no theme title.
- The **No Header/Footer** template (`tpl-no-header-footer.php`) removes both.

## Code-level customization (child theme)

When the user wants more than CSS, build a child theme on the user's computer and have them upload it under Appearance → Themes → Add New → Upload Theme. Never write files to the site.

Minimal child theme, two files in a folder such as `blt-child/`:

`style.css`
```css
/*
Theme Name: BLT Child
Template: beaver-light-theme
*/
```

`functions.php`
```php
<?php
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'blt-child', get_stylesheet_uri(), array( 'blt-theme' ), '1.0' );
} );
```

`Template:` must match the parent theme's folder name on the site.

Child theme CSS should target the theme's classes and custom properties. All theme CSS sits in cascade layers, so any unlayered child CSS wins without extra specificity.

### Custom properties

`--blt-surface`, `--blt-text`, `--blt-heading`, `--blt-muted`, `--blt-line`, `--blt-primary`, `--blt-primary-hover`, `--blt-on-primary`, `--blt-link`, `--blt-link-hover`, `--blt-font-body`, `--blt-font-heading`, `--blt-font-size`, `--blt-heading-weight`, `--blt-radius`, `--blt-site-width`, `--blt-gutter`.

### Main classes

`.blt-header`, `.blt-nav`, `.blt-footer`, `.blt-container`, `.blt-container--narrow`, `.blt-archive`, `.blt-loop-item`, `.blt-entry`, `.blt-prose` (native post and page content), `.blt-comments`, `.blt-404`. Body classes: `.blt-builder-page`, `.blt-no-header-footer`.

### Filters

- `blt_header_footer` (bool): output the header and footer slots. False on the No Header/Footer template.
- `blt_footer_copyright` (string): the footer line, default "© {year} {site name}".

### Action hooks

Content can be added at these positions with `add_action()`, or as Beaver Themer Parts:

- Page: `blt_page_open`, `blt_page_close`
- Header: `blt_header_before`, `blt_header_after`
- Content: `blt_content_before`, `blt_content_open`, `blt_content_close`, `blt_content_after`
- Loop: `blt_loop_before`, `blt_loop_after`
- Entry: `blt_entry_before`, `blt_entry_meta_open`, `blt_entry_meta_close`, `blt_entry_content_before`, `blt_entry_content_after`, `blt_entry_footer_open`, `blt_entry_footer_close`, `blt_entry_after`
- Footer: `blt_footer_before`, `blt_footer_open`, `blt_footer_close`, `blt_footer_after`
- Comments: `blt_comments_open`, `blt_comments_close`

The classic Beaver Builder Theme hook names (`fl_before_header`, `fl_after_content` and so on) also fire at the matching positions, for sites switching from that theme. Prefer the `blt_` names for new work.

## Beaver Themer

The theme supports Themer headers, footers and Parts. A Themer header or footer replaces the theme's own. Parts can be placed at any of the action hooks above.
