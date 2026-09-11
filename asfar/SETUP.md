# ASFAR setup and editing

Requires WordPress, PHP 8.1+, your licensed ACF Pro, and Polylang with English (`en`) and Arabic (`ar`). Premium plugins are not bundled.

## Install or upgrade

1. Install `asfar.zip` under Appearance → Themes and activate it.
2. For a new installation, upload `asfar-media.zip` under Appearance → ASFAR Setup. Alternatively, extract it into `wp-content/uploads` and use **Register images already in uploads**. All 399 supplied images are registered in the Media Library; none are stored inside the theme.
3. Use **Populate existing website content** on ASFAR Setup after activating ACF Pro and setting up both languages. Imported English and Arabic content is linked through Polylang.
4. Assign the **ASFAR Homepage** template to both homepages. Select the English homepage under Settings → Reading. Use Polylang to link the Arabic translation.
5. Review Appearance → Menus. Primary and Expanded Navigation are separate native menu locations for each language. The original design displays the expanded menu behind the menu button; the primary location remains available for a child theme.

When upgrading from 1.0.1, the next administrator visit automatically migrates the existing data. No second media upload is needed. It creates Team, Projects and FAQ entries, replaces numbered ACF fields with named sections, and transfers global labels into Polylang. It preserves existing page IDs, custom menu assignments, attachment IDs and edited or deliberately cleared values. Legacy metadata is retained. Migration can also be run with `wp asfar migrate`; it is safe to repeat. Initial import is available with `wp asfar seed`.

Back up the database and uploads before a production upgrade. The published release updates theme files; it does not deploy to asfar.com automatically.

## Editing content

| Admin location | Content |
| --- | --- |
| Pages → Homepage | Ten numbered field groups in frontend order: Banner, About, Vision, Impact, Projects, Investments, News, Team, Partners, FAQ |
| Homepage → Banner Slides | Add, remove and drag slides into order; edit headings, background image, film button and link |
| Homepage → Landscape Animation | Optional original landscape film and destination captions; off by default so all banner slides cycle |
| Projects | Add projects, featured images, company, description, statistics and map position; translate using Polylang |
| Homepage → Project Portfolio | Select and order the projects to feature; clearing the selection removes the project panels |
| Team | Add members with a featured photo, position and biography; use the Order field to sort members |
| Team → Team Types | Organize Board, Leadership and Committees; translate terms and set their display order |
| Pages → Team | Use the ASFAR Team template; the listing reads native members and team types automatically |
| Partners | Separate top-level ACF options page for logos, names, links and display order |
| Pages → Frequently Asked Questions | Use the ASFAR FAQ template; add and order the question/answer repeater |
| Homepage → FAQ Source Page | Select the FAQ page; the current-language translation is used automatically |
| Posts | News title, featured image, date, category label, paragraphs and optional external article link |
| Pages → News | Use the ASFAR News template for the listing |
| Theme Settings | Shared logos, contact details, notification recipients, social links and source interface labels |
| Languages → Translations | Translate footer, form, navigation labels and partner names registered under ASFAR Theme / ASFAR Partners |

Edit the English and Arabic homepage content separately using Polylang's translation controls. Team members, team types and projects also have native translation pairs. There are no separate language-specific header or footer files.

The Site Logo is synchronized with WordPress Site Identity. Background and content images use Media Library attachments. Decorative source artwork is served from the separately installed uploads package.

## Templates and fields

`template-home.php` loads these individual parts from `template-parts/home/`: `banner.php`, `about.php`, `vision.php`, `impact.php`, `projects.php`, `investments.php`, `news.php`, `team.php`, `partners.php`, and `faq.php`.

Named ACF definitions live in `inc/fields.php`; post types and taxonomy in `inc/content-types.php`; translation registration in `inc/translations.php`. Standard `page.php`, `single.php`, `archive.php`, `single-asfar_project.php` and `single-asfar_team.php` handle other views. Frontend HTML is not minified.

## Forms and updates

Contact submissions are stored before email delivery, listed under Form Submissions, and exportable as UTF-8 CSV. CSV formula-like values are neutralized. Choose notification recipients by language under Theme Settings; the WordPress admin email is the fallback. Use SMTP or the host's mail service and verify delivery on the destination installation. Avoid caching the contact-form nonce beyond its WordPress lifetime.

The theme checks stable releases at `pmunankarmi/asfar` and offers newer versions through Dashboard → Updates. Use Appearance → ASFAR Setup → **Check GitHub for theme updates** for an immediate check. Updates download `asfar.zip` and preserve the `asfar` directory name. Database content, media and submissions remain outside the theme.

To release: increase `Version` in `style.css` and `ASFAR_VERSION` in `functions.php`, run `python3 tools/package.py vVERSION`, then push a matching tag. GitHub Actions builds and attaches both packages. Do not install GitHub's generic source archive as a WordPress theme.

For a private repository, provide a read-only `ASFAR_GITHUB_TOKEN` server-side in wp-config.php using an environment variable. Never store credentials in the theme. Private release download behavior requires separate staging verification.

See TEST-REPORT.md for the completed checks and the ACF Pro testing limitation.
