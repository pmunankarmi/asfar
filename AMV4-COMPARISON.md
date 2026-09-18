# AMV4 source comparison — 17 September 2026

Compared AMV4.zip against the previous supplied ASFAR final3 (1).zip reference and ported its changes onto WordPress theme 1.2.2.

| Source change | WordPress implementation |
| --- | --- |
| Two banner controls: Overview / The journey | Two controls when landscape animation is enabled; ACF banner repeater retained for ordinary slide mode. The journey plays forward and returns to Overview; selecting Overview stops playback. |
| Arabic About heading correction | One-time admin migration changes only the exact old source wording, preserving custom headings and other field values. |
| Centered language label and balanced hamburger bars | Updated existing theme CSS in both languages. |
| Clickable portfolio regions and idle highlight animation | SVG pointer handling, faint region highlights and a hint state that ends on interaction. Reduced-motion preferences are respected. |
| Back to Media Center link above news articles | Shared single-post template links to the current language homepage's news section. New wording is registered with Polylang. |

All 36 supplied article pages gain the return link. Other news/team/listing HTML differences are asset-version references only. News/team datasets, existing image files, fonts and video are unchanged. Added source development helpers and a duplicate font archive are not theme content and are not shipped.

Preserved the native WordPress template hierarchy, ACF fields/repeater loops, translated content records, separate Partners settings, tabbed Theme Settings, hidden maintenance menu entries, mt- class prefixes, original HTML IDs/anchors, rounded button hover, banner button spacing, compact sticky header and video dialog layering.

Validation: PHP/JavaScript syntax and ZIP integrity; isolated migration checks for exact correction, preservation of edits and idempotence; browser preview checks using source-derived DOM with current theme CSS/JS. The previous local WordPress installation is unavailable in this workspace. This release has not been installed or verified in staging ACF Pro; the staging admin session requires login. No live content changes were made.

## Live reference follow-up — 18 September 2026 (1.3.1)

Compared `/staging/` and `/staging/index-en.html` with the installed WordPress 1.3.0 homepage at the same 1440 × 900 viewport. All nine visible section heights matched in both languages. The source deliberately hides its homepage Team section, so the equivalent disabled WordPress section was preserved.

Restored details missing from the original WordPress conversion:
- Teal top and cream bottom wave dividers inside the portfolio map.
- Bold first line and explicit line breaks in the portfolio heading, supporting older one-line imported titles and custom multiline ACF headings.
- `.836em` spacing above the About subtitle.
- Superscript square-metre units in impact statistics, portfolio figures and project details. Text remains escaped; arbitrary HTML is not enabled.

Preserved the requested compact sticky header, rounded button hover, modal layering, banner button spacing, translated CPT links and native editable ACF content. No live content was overwritten. PHP/JavaScript/package validation and isolated formatting checks cover the update; installation and final rendered verification of 1.3.1 remain with the administrator.
