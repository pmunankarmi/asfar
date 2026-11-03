# ASFAR 1.1.4 verification

Local environment: WordPress 7.1, PHP 8.5.7, SQLite Database Integration, Polylang 3.8.9, and free ACF with a test-only options-registration fixture. The fixture is not included in the theme.

## Completed

- PHP syntax and JavaScript syntax checks, matching release versions, correct installable ZIP root and archive integrity.
- Ten homepage section templates and ten named ACF groups; obsolete numbered editor definitions and per-language header/footer files removed.
- Existing-content migration: 44 existing page/post IDs retained; 38 team members, eight projects and two FAQ pages created with English/Arabic translation pairs. Team types also translated.
- Migration reruns preserve edited and deliberately cleared values, do not duplicate content, and reconstruct the nested option-count format used by legacy ACF Pro repeaters.
- Homepage FAQ reads the selected page directly. Project selection can be cleared. New project, team-member and FAQ pages return complete successful responses.
- Real Polylang translates the shared footer labels and assigns the native menus in both languages.
- Browser checks: all 44 existing URLs; banner dot selection; native drawer menu; FAQ expansion on homepage and standalone page; team biography opening/closing; shared footer/contact form; 404 page; no JavaScript errors or broken loaded images.
- English desktop and Arabic mobile screenshots inspected. Mobile layout has no horizontal overflow. Corrected the old desktop sector mask covering the stacked mobile carousel.
- Contact form saves through the WordPress AJAX endpoint with mail deliberately disabled in the local fixture. CSV neutralization, Arabic CSV text and the submissions schema pass integration checks.
- All 399 images registered in uploads / Media Library; repeat installation reuses IDs; unsafe media archives rejected. Uploaded map artwork renders. Theme packaging rejects content image files inside the theme and includes the required admin screenshot.
- Customizer/ACF logo synchronization, graceful rendering with both plugins disabled, and GitHub updater version detection, failure handling, caching and installed-directory preservation pass.

## Staging verification (asfar.primedigital.dev)

- Installed 1.1.2 through the native WordPress theme uploader. Classic page editing and the Appearance theme screenshot work.
- Saved both translated homepages using actual ACF Pro. Three banner rows and the named section groups persist; four selected projects and five FAQ items render per language.
- Trashed 24 duplicate imports (19 Team, four Projects, one FAQ) and the three default WordPress post/page records. Archived 13 duplicate menu links in an unassigned backup menu. The content check reports zero remaining duplicates.
- Verified 19 Team entries in three translated types and 19 news entries per language, eight native pages total, nine partner logos, and complete source project details. Cleared the generic Posts page assignment so translated News pages use their page templates.
- Verified both homepage saves, translated FAQ/project relationships, menu opening/closing, frontend images, and the shared footer. Added the missing Arabic Back to top label through Polylang.
- New isolated repair tests cover concurrent-operation locking, reversible duplicate cleanup, missing field recovery, canonical translation pairs, homepage reference remapping and order, menu archival, and repeat repair.

## Limits of verification

Local tests use free ACF; actual ACF Pro homepage saves were verified on staging. Pro UI add/remove/reorder/image-selection operations were not exhaustively tested. No premium plugin is bundled.

Staging content updates were verified through WordPress admin. Production cache and actual email delivery were not tested. Browser interaction checks primarily use reduced motion; full landscape-animation timing requires visual acceptance on supported browsers. No formal PHPCS audit was run. A full WordPress upgrade on the destination server has not been performed.

Publishing the GitHub release does not install the update on asfar.com.

## 1.1.3 administration checks

Verified that ACF configuration is hidden while field groups remain registered, legacy posts with open discussion reject comments/pingbacks, new discussion defaults are closed, editor comment support is removed, and WordPress REST comment creation is rejected. PHP/JavaScript syntax and installable package checks pass.

## 1.1.4 latest source revision

Compared the September 11 `ASFAR final3 (1).zip` with the prior source. Content changes are the two homepage About headings; team and news visible text is unchanged. Ported persistent hero buttons, larger navigation targets, destination selection, PIF artwork, vision layout, interactive portfolio regions and desktop portfolio scroll progression. Restored the news listing classes and inner-page header styling, with clearance above news headings/cards.

Local browser verification covers English and Arabic news at 1440px and 390px, persistent banner buttons, destination controls, keyboard map selection, PIF artwork from uploads, Arabic default homepage, animated destination selection, portfolio pinning and Escape release, and no JavaScript errors. Source-revision tests verify the Arabic default, News page assignment, preserved subsequent edits and remapped menu URLs. Existing integration and package checks pass.

The 1.1.4 release is prepared for the administrator to install. No 1.1.4 staging deployment is claimed. ACF Pro save behavior was previously verified on staging with 1.1.2; this revision uses the same field structure with an added explanatory message.

## 1.1.7 templates and selector namespace

Verified the template assignment migration, native ACF repeater output, English/Arabic homepage section counts, banner controls, menu, FAQ, portfolio keyboard selection and scroll release, team biography dialogs, contact submission with local mail disabled, all imported page responses, Arabic default and mobile width. Browser checks report no JavaScript errors or broken loaded images. Integration checks and the no-ACF/no-Polylang fallback smoke test pass. PHP/JavaScript syntax, CSS parsing and installable archive checks pass.

This revision uses local ACF Free with a test-only raw repeater row adapter, not ACF Pro UI. No staging installation or Pro editor verification is claimed for 1.1.7. WordPress/plugin-generated identifiers retain their native names; theme-owned selectors use mt-.

## 1.1.8 automatic admin update discovery

Isolated tests verify that admin visits refresh stale GitHub release data and populate the native update notice, preserve other theme updates, throttle checks to five minutes and skip unauthorized users. Syntax and release package checks pass.

## 1.1.9 homepage assignment and anchors

Confirmed staging English homepage retained the old template path after the folder migration. Restored its homepage assignment through WordPress admin and verified three banner slides and homepage section headings. Revised migration version two explicitly queries all Polylang languages. Local checks cover rerunning from version one and both translated homepage assignments. Classes retain mt-; IDs, CSS ID selectors, JavaScript references and anchor links use original names.

## 1.2.0 background update notices

Verified privileged Heartbeat responses discover new releases, preserve other theme updates, respect the two-minute throttle and omit update data for unauthorized users. Browser tests verify the notice appears once, links to WordPress Updates, preserves unsaved input and disappears when no update remains. Syntax and package checks pass. Staging Polylang now has all 42 registered strings populated in both languages; all three translation pages were reloaded to verify no blank entries.

## 1.2.1 tabbed Theme Settings

Organized existing fields with standard ACF tabs, retaining their names and keys. Integration checks (including logo synchronization and shared translations), syntax and package checks pass. The tab layout has not been installed or visually verified in staging ACF Pro.

## 1.3.0 AMV4

Compared all supplied HTML and assets against the prior reference. Ported two banner modes, map pointer/hint states, language/menu alignment, article return links and the Arabic heading correction. The isolated migration test verifies exact-match correction, preservation of custom headings/other fields and repeat-run safety. PHP and JavaScript syntax and archive integrity pass.

Browser verification uses a source-derived static preview with the actual theme scripts/styles, not an installed WordPress/ACF environment. Bilingual banner selection, actual map pointer clicks and keyboard selection, mobile reduced-motion navigation and viewport width pass without JavaScript errors. Staging installation and ACF Pro editor verification remain for after the administrator installs the release.

## 1.4.12 — Taif, project visibility and partner Swiper

- PHP syntax, JavaScript syntax and package integrity passed through `tools/package.py v1.4.12`.
- Partner template fixtures with 0, 9, 10, 11 and 16 valid logos verified the Swiper threshold and rendered logo count.
- Project data fixture verified legacy entries remain visible, saved `0` is hidden, and saved `1` is visible.
- Headless Chrome verified bundled Swiper initialization, next navigation, responsive slide count and no horizontal page overflow at 390px and 1440px in LTR and RTL.
- The Taif administrator migration requires ACF Pro, Polylang and the existing imported content. It has not been exercised against a running WordPress database in this session. New statistics are intentionally left empty for editorial entry.

## 1.4.13 — Partner logo overlap regression

The isolated `tests/partners-preview.php` fixture deliberately inserts the legacy first-child absolute-positioning rule. Browser verification confirmed all nine images load, remain visible without entrance JavaScript, and have non-overlapping bounding boxes in a two-row desktop grid. At 390px RTL, nine images remain a grid with no overlapping boxes or page overflow. With eleven images, Swiper initializes and the Next button changes the track transform. Partner image slots alternate linked and unlinked markup. PHP/JS syntax and package integrity checks pass.

## 1.4.14 — Repair Taif homepage selection

`php tests/taif-setup.php` passes the nested ACF field naming regression: English and Arabic homepage selections are repaired from revision 1, selection order and edited headings are retained, hidden flags and project content are preserved, and a second run makes no duplicates or changes. The installer verifies the saved relationship before marking completion. PHP/JS syntax and theme packaging pass. This contract test does not replace a live WordPress database check.


## 1.4.25 — Responsive layout corrections

Headless Chrome checked English and Arabic homepage, team listing, news listing, article and supplier previews at 320, 390, 600, 768, 960, 1024 and 1440px. No horizontal page overflow or escaped section/text/button bounds were found outside intentional carousel tracks. The homepage regression test additionally checks that About/Vision cards fill their mobile rows, hero actions retain at least 18px side gutters, and action heights remain at least 48px. Mobile Arabic hero and About screenshots were inspected.

These checks use source-derived static previews with current theme CSS and homepage scripts, plus the native supplier fixture; they do not verify a live WordPress database or customized editor content. `tests/responsive.cjs` runs against the output of `tests/amv4-preview.py` served locally, with optional `ASFAR_TEST_URL`, `PLAYWRIGHT_MODULE` and `CHROME_PATH` overrides. The supplier fixture's missing text helper was restored. Packaging validates PHP/JavaScript syntax and ZIP integrity.


## 1.4.26 — Mobile refinements

Chrome checks passed in English and Arabic at 320, 390, 600, 768, 960, 1024 and 1440px. Mobile assertions cover hidden decorative Vision artwork, portfolio pagination clearing both panel and wave, actual pagination selection and panel switching, two team columns with captions below portraits, and increased News/Team padding. The source-derived fixture now explicitly enables its optional Team section for verification. Arabic screenshots of Vision, portfolio and team were visually inspected. Current theme assets and homepage scripts are used; live WordPress editor content is outside this preview. Packaging checks PHP/JavaScript syntax and ZIP integrity.


## 1.4.27 — Finger scrolling and mobile team navigation

`tests/mobile-interactions.cjs` uses Chrome DevTools touch input with a 390px mobile viewport. English and Arabic pass real horizontal swipes on Strategic Investments and News, vertical page scrolling across each rail, synchronized previous/next arrows, sticky Team group tabs, return to the new group's first portrait, and restoration of desktop tab placement on resize. Checks pass with normal and reduced motion. Desktop mouse dragging also passes in both languages. `tests/responsive.cjs` passes at 320, 390, 600, 768, 960, 1024 and 1440px. No browser JavaScript errors were recorded.

The preview now renders the actual PHP Team template and all group tabs from source fixture data through `tests/team-preview.php`. Fixture photographs and copy are not live WordPress content. Tests bypass the introductory curtain using its ordinary session-storage flag. PHP/JavaScript syntax and package integrity are checked by `tools/package.py v1.4.27`. Physical iPhone/Safari testing remains outside these local Chrome checks.


## 1.4.28 — Featured portrait, image proportions and mobile type

The bilingual responsive suite passes at 320, 390, 600, 768, 960, 1024 and 1440px. Added mobile assertions verify a full-width featured portrait, a portrait width over 1.8 times the regular portrait width, caption placement below the first portrait, and desktop-matching aspect ratios for featured/regular team portraits, Strategic Investments cards, News cards and News photographs. About paragraphs have a 16px minimum and News titles a 14px minimum.

Arabic mobile captures were visually reviewed for About, Vision, Impact, portfolio, Strategic Investments, News, Team, FAQ and footer. The interaction suite passes actual Chrome touch input in both languages with normal/reduced motion, including swiping, page scrolling, arrows, sticky team tabs and return to the first member. Desktop mouse dragging is retained. Fixtures use current theme assets; live WordPress content and physical Safari devices were not exercised. Packaging validates PHP/JavaScript syntax and ZIP integrity.


## 1.4.29 — Team page columns and Partners carousel

`tests/team-partners.cjs` checks the real PHP Team page template and all groups in English and Arabic at 320, 390, 600, 768, 960 and 1440px. Mobile checks verify two columns, full card-width portraits at the homepage 114:113 ratio, captions below images and no page overflow. Supplied fixture portraits load successfully.

The native Partners PHP fixture passes with 0, 1, 2, 9 and 11 logos in LTR/RTL at 390, 768 and 1440px. Swiper initializes for every multi-logo list, uses 2/3/5 visible slides at the corresponding breakpoints, and arrows move one slide. Real Chrome touch gestures advance the nine-logo carousel in both directions/languages. Single-logo and empty fallbacks pass; no JavaScript errors were recorded. Team and Partners captures were visually inspected. These local fixtures do not exercise a live WordPress database or physical iPhone. Packaging checks PHP/JavaScript syntax and ZIP integrity.
