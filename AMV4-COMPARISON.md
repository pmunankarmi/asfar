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
