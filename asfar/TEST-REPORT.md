# Verification report

Tested locally on September 10–11, 2026. No production site was modified. GitHub publication is handled separately from local testing.

Environment: WordPress 7.1; PHP 8.5.7; official SQLite Database Integration; Polylang 3.8.9; free ACF with an isolated test-only options API fixture; Google Chrome through Playwright. The fixture is not delivered in the theme and is not a substitute for ACF Pro validation.

## Passed

- PHP syntax validation across every theme PHP file, and JavaScript syntax validation across the maintained interaction scripts.
- Installation and import of 44 pages/posts, including 22 English/Arabic pairs; all imported media references exist in the theme.
- Selectable `template-home.php`; no `front-page.php` or static-homepage `home.php`.
- All 44 imported URLs returned complete successful pages in HTTP smoke checks.
- English desktop and Arabic mobile rendering; RTL language attributes and language-switch navigation.
- No browser JavaScript errors; no broken loaded homepage images; mobile viewport and document widths both 390px after correcting the honeypot's off-screen positioning.
- Team biography opens and closes with Escape. Server-rendered team, news, portfolio, partner, sector and FAQ content.
- Custom 404 renders the editable missing-page message.
- Import rerun preserves a deliberately emptied field. Native logo replacement/removal and ACF logo replacement/removal synchronize in both directions. Removing the main logo hides its dark variant.
- ACF field keys are unique and the homepage receives its correct field group through its custom location rule.
- Nonce, invalid email and honeypot rejection through the real WordPress AJAX endpoint.
- Arabic form submission, duplicate handling, and persistent storage when the test mail transport returns failure.
- Anonymous CSV export denied; administrator export works; invalid export nonce denied.
- CSV UTF-8 BOM, Arabic text preservation and formula-injection neutralization for `=`, `+`, `-`, `@`, leading whitespace and control-character cases.
- Versioned submissions table created.
- A mocked newer GitHub release appears through WordPress's native theme update filter; release caching and API-failure handling pass. Extracted update directory is renamed to preserve the installed `asfar` slug.
- Homepage renders without ACF or Polylang activated, without a PHP fatal error.
- Installable ZIP root, version matching and archive integrity checked by `tools/package.py`.

## Still required on staging

- ACF Pro's actual options-page/repeater editor: saving, removing and reordering rows, image selection and bilingual editor workflow. A licensed Pro installation was not available locally. Registered definitions and data access were checked with the free ACF API and a test fixture, which does not verify Pro behavior.
- MySQL/MariaDB hosting behavior, production caching and mail delivery to an actual inbox. Local submissions intentionally used a failing mail transport.
- A real WordPress update from a published GitHub release, including private-repository downloads if applicable. The repository was empty, so release responses were mocked and the package/slug logic was tested locally.
- Full animation timing and visual acceptance across the destination site's supported browsers. Browser screenshots and smoke checks primarily used reduced-motion mode; the original animation assets and timing code are retained.
- Formal WordPress Coding Standards/PHPCS audit was not run. Syntax, escaping, capability/nonce paths and integration behavior were checked.

The deliverable is an implemented, installable theme with local verification; the remaining checks above must be completed before calling the deployment production-verified.

## Version 1.0.1 — external image storage

All 399 image files were removed from the theme. The separate media ZIP is verified against a checksum manifest before installation, then registered in uploads/Media Library. Repeated installation preserves attachment IDs. The theme package builder rejects image files inside the theme ZIP. CSS decorations, map artwork and animation frames now resolve from uploads.
