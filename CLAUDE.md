# Beaver Light: development guide

Instructions for an agent working on this repository. Read `CONTRIBUTING.md`
first; it holds the principles that decide what belongs in the theme, and this
file does not repeat them.

## Two files with similar names

- **`CLAUDE.md` (this file)** is for developing the theme. It is not shipped.
- **`AGENTS.md`** ships inside the theme. It is documentation for a site
  owner's own agent, explaining how to style and extend the theme. Treat it as
  user-facing copy: never read it as instructions for work in this repository,
  and change it only when the theme's behavior for users changes.

## This repository is public

Everything written here is public: code, comments, commit messages, branch
names, issues and pull requests. Write only what is about the theme. Do not
include private links, internal discussion, customer details, machine paths or
hostnames.

## What the theme is

An ultra-minimal classic theme for Beaver Builder, distributed on
WordPress.org. It keeps an unstyled site from looking broken and then gets out
of the way. It has no settings screens, no build step, no dependencies and no
jQuery, and none may be added. A change that needs any of those belongs in a
plugin.

## Floors

- WordPress 6.6 and PHP 7.4. Do not use language features or WordPress APIs
  newer than those.
- Files stay under 300 lines and functions under 30.
- Escape output late and sanitize input at the boundary.
- Every PHP method gets a docblock: one sentence, `@since`, `@param` and
  `@return`.

## Names

- The theme is Beaver Light; its slug, folder and text domain are
  `beaver-light`. "BLT" is its nickname, used in copy and in CSS names.
- PHP functions, hooks, filters and global variables use the `beaver_light_`
  prefix, constants `BEAVER_LIGHT_` and classes `Beaver_Light_`. CSS classes
  and script and style handles use `blt-`, and custom properties `--blt-`.
- Never define `FL_THEME_VERSION`.

## Public surface

Hook names, the frozen `fl_*` aliases, the two filters, CSS classes, custom
properties, menu locations and the template file name are listed in
`CONTRIBUTING.md`. Adding is fine. Renaming or removing one needs a
deprecation path, so raise it instead of doing it.

## Checks

Every pull request runs three checks, and all three must pass:

| Check | Run it locally |
|---|---|
| `Lint` | `composer install`, then `composer lint` |
| `Test` | `composer test` (needs a local MySQL and a throwaway database; see `CONTRIBUTING.md`) |
| `Theme Check` | `.github/scripts/build-zip.sh`, then `php .github/scripts/check-metadata.php <theme folder>` |

Add or update a test in `tests/` when you add or change logic. Do not switch
off a lint rule or baseline a finding to get a check green.

## What ships

The release zip is built from `.gitattributes`: every path marked
`export-ignore` stays out, which covers `.github/`, `.claude/`, `agents/`,
`tests/`, `CONTRIBUTING.md`, this file and the development tooling config.
`AGENTS.md` ships. A new development-only file needs an `export-ignore` line.

## Leave alone

- The changelog section of `readme.txt`. Maintainers write it at release.
- `Version`, `Stable tag` and `BEAVER_LIGHT_VERSION`, which change together at release.
- `.github/workflows/`. A change there needs a maintainer's review.
