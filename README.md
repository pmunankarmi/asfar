# ASFAR WordPress theme

ASFAR 1.2.1 uses native WordPress pages, menus, posts and featured images, with named ACF Pro fields and Polylang translations.

- Each of the ten homepage sections has its own readable template in `asfar/template-parts/home/` and its own clearly named ACF field group.
- Banner slides use an ACF repeater; Projects and Team have dedicated post types. Team Types is a translatable taxonomy.
- Partners has a separate top-level options page. FAQ questions belong to an ordinary translated page selected on the homepage.
- All languages share `header.php` and `footer.php`. Shared labels are registered in Polylang Strings Translations.
- Content images live in WordPress uploads / Media Library; the theme includes only its admin preview screenshot.
- Gutenberg is disabled in favor of the classic editor. Appearance → ASFAR Content Check can repair missing imported fields and archive duplicate imports.

Installable theme: `dist/asfar.zip`. Separate image package: `dist/asfar-media.zip`.

Read [setup and editing instructions](asfar/SETUP.md) and [verification report](asfar/TEST-REPORT.md).

Build with `python3 tools/package.py v1.2.1` (PHP and Node.js required). The release workflow packages tagged versions for native WordPress updates from GitHub.

`inc/seed.json` and `inc/migration-map.json` are import/migration reference data, never frontend content sources. The one-time migration preserves existing page IDs, images, translations and edited fields. Existing legacy metadata stays in the database; the old generated templates and numbered field editor have been removed.

Tests require an isolated WordPress installation. Never run fixture tests on a production database. Browser paths are configurable through `PLAYWRIGHT_MODULE`, `CHROME_PATH` and `ASFAR_TEST_URL`.

ACF configuration screens are hidden because fields are defined in PHP. Content fields, Theme Settings and Partners remain editable. Comments and pingbacks are disabled site-wide while this theme is active; existing comment data is retained.

Version 1.2.1 matches the September 11 source update, including homepage copy, persistent banner buttons, destination controls, PIF artwork, portfolio interaction and corrected news spacing. The one-time upgrade sets Arabic as the default and preserves translated menu destinations.

Selectable page templates live in `asfar/templates/`; WordPress hierarchy entry points stay at the theme root and load markup from `template-parts/layout/`. A one-time admin migration updates existing template assignments. Frontend repeaters use native ACF `have_rows()`, `the_row()` and `get_sub_field()` loops. Only theme-owned CSS classes use `mt-`, including JavaScript-created elements and inline SVG. HTML IDs and URL anchors have no prefix. WordPress/plugin-generated identifiers remain standard.
