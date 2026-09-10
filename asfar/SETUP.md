# ASFAR WordPress theme

## Install

1. Install WordPress 6.6+ with PHP 8.1+. Upload `asfar.zip` under Appearance → Themes → Add New → Upload Theme, then activate ASFAR.
2. Install and activate your licensed **ACF Pro** and **Polylang** (the free Polylang edition is sufficient). Neither plugin nor a license key is included.
3. In Languages, add **English** (`en`, locale `en_US`) and **Arabic** (`ar`, locale `ar`, RTL enabled). Set English as the default. Do not enable Polylang's custom-field synchronization: these page fields must remain independent between languages.
4. Open Appearance → **ASFAR Setup**. Upload **asfar-media.zip** first, then click **Populate existing website content**. This imports 44 pages/posts, attachment media, translated global content and translation pairs. On hosts with short request timeouts, use `wp asfar seed`; interrupted imports can be run again.
5. In Settings → Reading, choose **A static page** and select the imported English homepage. Both homepages already have the selectable **ASFAR Homepage** template (`template-home.php`). Check their English/Arabic translation pairing in Pages. Do not assign the News landing page as WordPress's Posts page; it is an ordinary page with the supplied design.
6. Set the native site title and tagline in Settings → General. Save Settings → Permalinks. Set the native Site Icon if desired.
7. Review Theme Settings → **EN Content** and **AR Content**, especially contact information and form notification recipients. A blank notification recipient falls back to the WordPress administrator email.

The theme ZIP contains only the `asfar/` theme directory and no image files (about 7.6 MB). All 399 images, SVG artwork and animation frames are supplied in **asfar-media.zip**. The installer places them under `wp-content/uploads/asfar-media/img/` and registers them in the Media Library, reusing existing attachment IDs. Increase the host upload limit to 50 MB for the media package, or extract that ZIP into `wp-content/uploads/` with your hosting file manager, then click **Register images already in uploads**. With WP-CLI: `wp asfar media /path/to/asfar-media.zip`. The film and fonts remain in the theme. Source artwork, fonts, photographs and film are retained from the supplied archive; retain the applicable original asset rights.

## Edit content

- Pages and imported news posts have labeled ACF Text, Textarea, Image/File and Link fields, grouped by their original layout. Markup stays in PHP. HTML in written fields is stripped; content output is escaped.
- New news items use native Posts, Featured Image, the optional Display Date, and the Paragraphs repeater. An External URL makes a news card link to an external article. Add the Arabic translation through Polylang. News cards read published posts automatically.
- Theme Settings holds per-language header/footer content, contact email, form messages, social links, FAQs, partners, investment sectors, portfolio regions/statistics and team members. Repeater rows can be added, removed and reordered. Team content is shared by the homepage and team page within each language. “Show Team” controls homepage visibility.
- Theme Settings → Shared branding holds the **main Site Logo**. It synchronizes with WordPress's native Customizer logo using attachment IDs, including removal. An optional dark logo variant is also shared. Replacing or removing the main logo clears this variant so an outdated logo cannot remain visible; add a matching variant again if needed. The footer logo can differ per language.
- Optional native menus can be assigned under Appearance → Menus to Primary, Expanded and Footer locations, separately per Polylang language. If Primary/Expanded is unassigned, the editable source navigation in Theme Settings is used. Use a flat list for the original layout.
- Navigation and imported `.html` links resolve to WordPress permalinks. A missing translated page falls back to the other published translation for imported links, and to that language's homepage for the language switcher. Missing translated content is not silently replaced with English copy.

ACF options use explicit storage IDs `global_en`, `global_ar` and `asfar_shared`, independent of the language selected in the admin toolbar. ACF field definitions are in `inc/fields.php` and `inc/fields.json`. `inc/seed.json` is read only by the explicit import action; it is never a runtime content fallback or a browser data file.

Import is repeatable: existing fields, empty values, posts and imported media are reused. It does not reassign the homepage or reset content on activation/update. Do not regenerate the original migration field IDs on a live installation.

## Forms

The existing contact form submits to WordPress with nonce verification, field validation, sanitization, a honeypot, IP-based throttling and duplicate protection. JavaScript supplies an inline result; without JavaScript the normal WordPress handler shows the result page.

Form Submissions is restricted to administrators (`manage_options`), with search, language/email-status filters, details, pagination and CSV Export. Exports require a nonce, neutralize spreadsheet formulas and include a UTF-8 BOM for Arabic. Submissions are stored in `{prefix}asfar_submissions` before `wp_mail()` is called. Mail failure is recorded and does not discard the submission. A “sent” status means WordPress accepted the mail call, not proof of inbox delivery.

Configure and test email delivery on the destination host. Exclude the form page from long-lived full-page caching, or keep cache lifetime below the WordPress nonce lifetime. There is no automatic submission deletion; use the site's agreed retention process. Theme updates and theme switching do not delete the table or content.

## GitHub releases and WordPress updates

Repository: `https://github.com/pmunankarmi/asfar`. The theme uses published, tagged GitHub releases as its WordPress update source. Images remain in uploads across theme updates.

1. Publish the repository contents to `pmunankarmi/asfar`, retaining `asfar/` as the theme directory.
2. Increase the version in **both** `asfar/style.css` and `asfar/functions.php`.
3. Run `python3 tools/package.py v1.0.2` (substitute the new version). This checks PHP/JavaScript syntax and the package structure.
4. Commit and push the changes, then create and push the matching `v1.0.2` Git tag.
5. The included GitHub Actions workflow creates a release with **asfar.zip** and **asfar-media.zip** attached. WordPress downloads only asfar.zip when updating the theme. Alternatively, manually attach `dist/asfar.zip` to a published, non-prerelease GitHub release. Do not use GitHub's automatically generated source ZIP as the installable theme.
6. WordPress checks the latest stable release, caches success for six hours and failures for 15 minutes, and offers newer versions through its native theme update interface. The updater preserves the installed theme directory name. For an immediate recheck, use **Appearance → ASFAR Setup → Check GitHub for theme updates**. Updates then appear in **Dashboard → Updates** or **Appearance → Themes**.

For a private repository, configure a fine-grained read-only token with access to this repository's Contents in the server environment, then add `define( 'ASFAR_GITHUB_TOKEN', getenv( 'ASFAR_GITHUB_TOKEN' ) );` to `wp-config.php`. Never place it in the theme, repository, admin content, browser settings or a download URL. The token goes only to the GitHub API. Signed download redirects are fetched without the Authorization header. The private-download path still needs verification against your private release.

Database content, ACF values, options, attachment files and submissions live outside the theme directory and survive normal theme updates. Back up the database and uploads before production upgrades. Make design modifications in a child theme or version-controlled source; editing files directly inside an installed theme is overwritten by updates.

## Verification and remaining checks

See `TEST-REPORT.md` for the exact checks performed. Local verification used WordPress 7.1, PHP 8.5, SQLite integration, Polylang 3.8.9 and free ACF with a **test-only options API fixture**. The fixture is not bundled with the theme and does not exercise the commercial ACF Pro options/repeater editor.

Before production launch, validate ACF Pro editing and saving on the destination installation, run the site with its actual MySQL/MariaDB database and cache, verify email receipt, and perform a real tagged-release upgrade on staging. Uploading the GitHub release does not deploy the theme to your website. Complete these staging checks before installing it on production.

Developer references: [ACF options pages](https://www.advancedcustomfields.com/resources/acf_add_options_page/), [Polylang API](https://polylang.pro/documentation/support/developers/function-reference/), [WordPress theme upgrader](https://developer.wordpress.org/reference/classes/theme_upgrader/).

## Upgrading from the initial image-bundled version

Before updating an existing site from 1.0.0, extract asfar-media.zip into wp-content/uploads with your hosting file manager. Then update the theme to 1.0.1 and click Register images already in uploads in ASFAR Setup. Alternatively, update the theme and immediately upload the image ZIP from the new setup screen. Existing ACF image selections and Media Library IDs are preserved. Subsequent theme-only updates do not need another media upload unless the release introduces additional images.
