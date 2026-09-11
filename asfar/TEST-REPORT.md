# ASFAR 1.1.0 verification

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
- All 399 images registered in uploads / Media Library; repeat installation reuses IDs; unsafe media archives rejected. Uploaded map artwork renders. Theme packaging rejects image files inside the theme.
- Customizer/ACF logo synchronization, graceful rendering with both plugins disabled, and GitHub updater version detection, failure handling, caching and installed-directory preservation pass.

## Limits of local verification

The licensed ACF Pro repeater/options editor was unavailable. Field registration, data access, migration and template behavior were tested; actual Pro UI add/remove/reorder/image-selection saving still needs verification on the destination installation. No Pro plugin or imitation repeater implementation is shipped.

The destination MySQL/MariaDB database, production cache and actual email delivery were not tested. Browser interaction checks primarily use reduced motion; full landscape-animation timing requires visual acceptance on supported browsers. No formal PHPCS audit was run. A full WordPress upgrade on the destination server has not been performed.

Publishing the GitHub release does not install the update on asfar.com.
