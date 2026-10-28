# Staging section comparison — 19 September 2026

Reference: https://asfar.primedigital.dev/staging/ and its English homepage.
Compared the live WordPress homepage in both languages at the same browser viewport, including rendered text, component sizes, fonts, spacing and controls. WordPress wrappers and attachment classes were excluded from the structural comparison.

| Section | Findings and outcome |
| --- | --- |
| Header | Previous compact sticky override was shorter than staging. Restored the source header height for this exact-match request. |
| Hero | Restored source button spacing, caption spacer and the 124-unit wave geometry instead of the generic 44-unit divider. Native ACF slides and accessible heading hierarchy remain. |
| About | Content, subtitle spacing, card typography and PIF artwork matched after 1.3.1. |
| Vision | Heading and three-card typography, sizing and spacing matched. |
| Investment Impact | Layout, values and superscript units matched. |
| Portfolio Map | Restored staging's initial hint state, scroll-driven region walkthrough, release after visiting all regions, region/dash selection and eight-second autoplay following interaction. Preserved corrected SVG targeting and the rose landscape. |
| Strategic Investments | Arabic arrow paths were reversed; now language-aware. Restored the light second heading line and source arrow/divider placement outside the grid. Native repeater retained. |
| News | Shared Arabic arrow direction corrected. Added date descending / ID ascending ordering so imported same-date articles retain their source order. WordPress typographic quotation conversion remains. |
| Team | Staging deliberately hides this section; retained the disabled homepage setting. |
| Partners | Nine logos, heading and spacing matched. |
| FAQ | Content/layout matched; live expansion verified. |
| Footer / contact | Logos and layout verified after lazy images loaded. Live contact expansion works; the WordPress form remains a server-backed form, not staging's mailto demonstration. No test message was sent. |

## Validation

The preview renders the actual PHP map and Strategic Investments templates with fixture WordPress/ACF records. Verified Arabic next movement, arrow paths, light heading weight and final-card disabled state; English next movement and arrow paths; map sequence Al-Baha → Yanbu → Aseer → Strategic Investments and scroll release. Mobile preview at 390px had no horizontal page overflow. Retained the existing mobile sector overflow fix, rounded button hover and video dialog layering.

PHP/JavaScript syntax and package integrity checks passed. This is not a pixel-by-pixel certification or a full local WordPress installation. The release still requires installation in WordPress admin before the final live appearance can be checked. No live content was overwritten.
