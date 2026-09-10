# ASFAR WordPress conversion

Installable theme: `dist/asfar.zip`. Image package: `dist/asfar-media.zip`. Theme source: `asfar/`. Image originals are kept separately in `media/asfar-media/` and are installed into WordPress uploads, never the theme directory.

See [setup and release instructions](asfar/SETUP.md) and [verification report](asfar/TEST-REPORT.md).

Build with `python3 tools/package.py v1.0.1` (PHP and Node.js must be on PATH). The release tag must match both theme version declarations.

`tools/convert.py`, `tools/adapt-js.py` and `tools/refine.py` are the one-time source migration scripts, retained for provenance. The final PHP templates and field definitions are the maintained source of truth. Do not rerun the migration scripts on an edited theme: they regenerate field IDs and templates.

Tests require an isolated WordPress installation containing imported content. The local browser test paths/credentials are test fixtures only and must be adapted for another machine. Never run fixture tests against a production database.
