# ASFAR WordPress theme

ASFAR 1.4.6 uses native WordPress pages, menus, posts and featured images, with named ACF Pro fields and Polylang translations.

- Each of the ten homepage sections has its own readable template in `asfar/template-parts/home/` and its own clearly named ACF field group.
- Banner slides use an ACF repeater; Projects and Team have dedicated post types. Team Types is a translatable taxonomy.
- Partners has a separate top-level options page. FAQ questions belong to an ordinary translated page selected on the homepage.
- All languages share `header.php` and `footer.php`. Shared labels are registered in Polylang Strings Translations.
- Content images live in WordPress uploads / Media Library; the theme includes only its admin preview screenshot.
- Gutenberg is disabled in favor of the classic editor. The administrator-only ASFAR Content Check maintenance page can repair missing imported fields and archive duplicate imports.

Installable theme: `dist/asfar.zip`. Separate image package: `dist/asfar-media.zip`.

Read [setup and editing instructions](asfar/SETUP.md) and [verification report](asfar/TEST-REPORT.md).

Build with `python3 tools/package.py v1.4.6` (PHP and Node.js required). The release workflow packages tagged versions for native WordPress updates from GitHub.

`inc/seed.json` and `inc/migration-map.json` are import/migration reference data, never frontend content sources. The one-time migration preserves existing page IDs, images, translations and edited fields. Existing legacy metadata stays in the database; the old generated templates and numbered field editor have been removed.

Tests require an isolated WordPress installation. Never run fixture tests on a production database. Browser paths are configurable through `PLAYWRIGHT_MODULE`, `CHROME_PATH` and `ASFAR_TEST_URL`.

ACF configuration screens are hidden because fields are defined in PHP. Content fields, Theme Settings and Partners remain editable. Comments and pingbacks are disabled site-wide while this theme is active; existing comment data is retained.

Version 1.3.0 matches the September 11 source update, including homepage copy, persistent banner buttons, destination controls, PIF artwork, portfolio interaction and corrected news spacing. The one-time upgrade sets Arabic as the default and preserves translated menu destinations.

Selectable page templates live in `asfar/templates/`; WordPress hierarchy entry points stay at the theme root and load markup from `template-parts/layout/`. A one-time admin migration updates existing template assignments. Frontend repeaters use native ACF `have_rows()`, `the_row()` and `get_sub_field()` loops. Only theme-owned CSS classes use `mt-`, including JavaScript-created elements and inline SVG. HTML IDs and URL anchors have no prefix. WordPress/plugin-generated identifiers remain standard.

ASFAR Setup and ASFAR Content Check are hidden from the admin menu. Their direct maintenance URLs and existing permissions are retained; see the setup guide.

Version 1.3.0 ports the AMV4 source revision. See [AMV4 comparison](AMV4-COMPARISON.md) for changes and verification limits. The update includes two landscape banner modes, clearer interactive map regions, navigation refinements and article return links. No media reimport is required.

Version 1.3.1 restores the portfolio’s top and bottom wave dividers, source title emphasis and line breaks, About subtitle spacing, and square-metre typography. Compared against the live `/staging/` reference in Arabic and English. Existing ACF content stays editable; no media reimport is required.

Version 1.3.2 fixes map highlighting and marker placement after adding the wave SVGs. Strategic Investments now uses the reference rose landscape. Each project has an optional Homepage Map Background ACF image field; featured images remain unchanged.

Version 1.3.3 automatically cycles the portfolio every eight seconds while visible, resets the timer after a manual selection, and pauses offscreen or in a hidden browser tab. Reduced motion disables autoplay. Normal page scrolling replaces the previous map scroll lock.

Version 1.3.4 follows the detailed staging comparison: reference map scroll walkthrough and post-interaction autoplay, language-aware slider arrows, lighter Strategic Investments second heading line, reference hero/header spacing and wave geometry, and deterministic chronological news order. See [section audit](SECTION-AUDIT.md).

Version 1.4.0 adds the Supplier Portal from the supplied “Asfar correct” reference and `/staging/v2/`. After installing the update, open WordPress admin: a one-time setup creates linked Arabic and English pages and inserts them before Partner with us in each expanded menu. ACF Pro, Polylang and assigned language menus are required. Edit the pages under Pages → Supplier Portal / بوابة الموردين; content is grouped into Introduction, Registration, Sign In and Contact tabs. Registration options use a native ACF repeater. Existing saved content is preserved on retries. The shared header/footer and supplied Oracle registration/sign-in links are retained. No media reimport is required.

Supplier setup checks: `php tests/supplier.php` verifies translated page creation, menu placement, unique ACF field keys, retry safety and preservation of editor changes. `tests/supplier-preview.php` renders either language with fixture data for browser checks.

Version 1.4.1 loads the supplied Polylang Slug code from `asfar/inc/polylang-slug.php` through `functions.php`; no additional plugin is installed. The integration preserves the original attribution and adds reserved-slug protection and language-aware page resolution when WordPress has cached the other translation.

After the theme update, the first administrator visit aligns published, linked Arabic pages, posts and translated custom post types with their English slugs. For example, the Supplier Portal becomes `/supplier-portal/` and `/en/supplier-portal/`. Taxonomy terms and media slugs are unchanged. This one-time migration preserves content and translation IDs, records original URLs in `asfar_slug_history`, updates custom menu links without losing anchors, and redirects old paths while this theme is active. Conflicting same-language slugs are skipped and reported. Later editorial slug changes remain under the editor’s control.

The slug migration is a separate, readable module: `asfar/inc/slug-migration.php`. Its checks are in `tests/shared-slugs.php`, which requires an explicitly marked, isolated WordPress installation. Validation covers shared page/post slugs, alternating language queries with a warm cache, same-language conflicts, reserved URLs, unchanged content, menu anchors, retries and redirects.

Version 1.4.2 adds Team Type display controls and the requested homepage refinements. Under Team → Team Types, edit a term to set Menu Order (lower numbers first) and Show on Homepage. The taxonomy list defaults to the same numeric order and includes Order/Visibility columns. Existing terms stay visible until switched off; hiding a type removes its homepage tab and group without deleting members.

Both hero actions are filled blue. Strategic Investments and News support arrow navigation and mouse/touch dragging in either language; vertical touch scrolling and normal news-card clicks remain available. News arrows are centered with View All News at the side. The expanded menu has smaller text and the supplied motif over its existing blue background, using the Media Library artwork.

Validation: `php tests/team-type-settings.php /path/to/marked-test-wordpress` checks ordering before admin pagination, legacy visibility and active-tab indexing. Browser previews verified English arrows and dragging, Arabic drag direction, blue buttons/menu, and the mobile Arabic menu/news layout. PHP/JavaScript syntax and package integrity checks passed.

Version 1.4.3 widens the featured team member’s desktop text column to fit the name on one line, with space before the other members. Mobile layouts still wrap naturally. Posts now have a Show on Homepage ACF switch in News Article Content. Only enabled, published posts in the current language appear in homepage news; unset switches are off, and no posts are selected automatically. The full news archive is unchanged. The homepage news section is hidden until at least one post is selected. The existing homepage post-count setting still applies.

Validation: `tests/home-news-selection.php` checks enabled/disabled/unset states, drafts, language separation, deselection and the full archive against isolated WordPress. Browser checks verified a single-line featured name without overlap on desktop and no horizontal overflow at 390px.

Version 1.4.4 adds Tools → Content Order for dragging posts, pages, custom post types and taxonomy terms into order. Choose the content and language, reorder items within their parent group, then click Save Order. Up/down buttons also support keyboard and touch editing. Saved news order applies to the homepage’s selected posts and news archive; Team and Projects use their existing menu order. Company Profile now uses a blue outline, while Watch the Film remains filled blue. Homepage news font sizes are reduced by 20% on desktop and mobile.

Native Posts, Pages, Team, Projects and taxonomy lists also have an Order drag handle. Drop a row to save automatically. Moves preserve unseen rows, language and parent groups; use Tools → Content Order for an overview of all siblings or keyboard ordering.

Version 1.4.5 anchors the navigation motif at the bottom-right, keeps investment controls within their section, and removes overlapping Team/Partners section lifts. Map regions still autoplay and respond to clicks, but no longer capture page scrolling, touch gestures or navigation keys.

Version 1.4.6 reduces news listing and article typography by roughly 15% at desktop sizes, with readable minimum sizes on mobile. Article paragraphs use 1.85 line spacing, headlines have more breathing room, and news card title heights accommodate the increased leading. Homepage news styling is unchanged.
