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
