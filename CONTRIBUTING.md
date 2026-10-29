# Contributing to Beaver Light Theme

Beaver Light Theme is an ultra-minimal theme for Beaver Builder, distributed on WordPress.org. It keeps an unstyled site from looking broken, then gets out of the way. These principles decide what belongs in it. `AGENTS.md` is a different file: it ships with the theme and tells a user's agent how to style it.

## Principles

- **No settings.** No Customizer panels, no admin pages, no theme options. The logo, site title, menus, Site Icon and Additional CSS come from WordPress core. Styling comes from Beaver Builder Global Styles or a design system.
- **One plain look.** White, system fonts, links in the body color with an underline, near-black buttons, neutral grays. The defaults exist only to keep an unstyled site from looking broken, so there is no second look to switch to and nothing for a user to undo.
- **Each style value resolves on its own:** design system token, then Global Styles, then the theme default. Every `--blt-*` property is printed by PHP (`BLT_Styles::bridge_css()`) on the front end and in the block editor.
- **The theme styles what the theme renders.** The header, footer, archives, native posts and pages. Never anything inside `.fl-builder-content`.
- **Every rule sits in a cascade layer**, so Beaver Builder, a child theme and Additional CSS win without a specificity fight. `editor.css` is the one exception, because nothing competes with it in the editor.
- **Hybrid theme.** PHP templates for structure, because Beaver Themer attaches through PHP action hooks, and `theme.json` for editor settings only. Colors and fonts stay out of `theme.json` `styles`: WordPress prints those unlayered, and they would beat Global Styles.
- **No build step, no dependencies, no jQuery.**
- **No plugin territory.** No SEO output, head cleanup, favicons, code fields, remote fonts or custom post types. WordPress.org reviewers reject them in themes, and they belong in a plugin.
- **Never define `FL_THEME_VERSION`.** Beaver Themer and the builder read it as "this is bb-theme", which loads bb-theme's Themer shim and drops the builder's button defaults.
- **No agent abilities in the theme.** Beaver Builder AI is the agentic route for Beaver Builder users. A standalone user's agent can write Additional CSS or a child theme from `AGENTS.md`. If abilities are ever needed, they go in a companion plugin.

## Public surface

These are frozen once the first beta ships. Adding is fine; renaming or removing needs a deprecation path.

- Hook names `blt_*` and the frozen set of `fl_*` aliases in `inc/class-blt-compat-fl.php`. The aliases keep Themer Parts saved against bb-theme rendering after a switch; new positions get `blt_*` names only.
- Filters `blt_header_footer` and `blt_footer_copyright`.
- CSS classes `blt-*` and custom properties `--blt-*`.
- Menu locations `header` and `footer`, and the template file `tpl-no-header-footer.php`. These match bb-theme so menus and page templates survive a switch.

## Code

- WordPress 6.6 and PHP 7.4 minimums.
- Files stay under 300 lines and functions under 30.
- Escape output late, sanitize input at the boundary.
- Every PHP method gets a docblock: one sentence, `@since`, `@param` and `@return`.
