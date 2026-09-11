# ASFAR WordPress theme

ASFAR 1.1.2 uses native WordPress pages, menus, posts and featured images, with named ACF Pro fields and Polylang translations.

- Each of the ten homepage sections has its own readable template in `asfar/template-parts/home/` and its own clearly named ACF field group.
- Banner slides use an ACF repeater; Projects and Team have dedicated post types. Team Types is a translatable taxonomy.
- Partners has a separate top-level options page. FAQ questions belong to an ordinary translated page selected on the homepage.
- All languages share `header.php` and `footer.php`. Shared labels are registered in Polylang Strings Translations.
- Content images live in WordPress uploads / Media Library; the theme includes only its admin preview screenshot.
- Gutenberg is disabled in favor of the classic editor. Appearance → ASFAR Content Check can repair missing imported fields and archive duplicate imports.

Installable theme: `dist/asfar.zip`. Separate image package: `dist/asfar-media.zip`.

Read [setup and editing instructions](asfar/SETUP.md) and [verification report](asfar/TEST-REPORT.md).

Build with `python3 tools/package.py v1.1.2` (PHP and Node.js required). The release workflow packages tagged versions for native WordPress updates from GitHub.

`inc/seed.json` and `inc/migration-map.json` are import/migration reference data, never frontend content sources. The one-time migration preserves existing page IDs, images, translations and edited fields. Existing legacy metadata stays in the database; the old generated templates and numbered field editor have been removed.

Tests require an isolated WordPress installation. Never run fixture tests on a production database. Browser paths are configurable through `PLAYWRIGHT_MODULE`, `CHROME_PATH` and `ASFAR_TEST_URL`.
