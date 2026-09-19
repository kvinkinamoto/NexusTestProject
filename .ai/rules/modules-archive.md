---
paths:
  - 'app/Nexus/Modules/**'
  - 'app/Nexus/Plugins/**'
  - 'app/Nexus/Widgets/**'
---

# Mirror module/plugin/widget changes into the modules archive

Whenever a file under `app/Nexus/Modules/{Name}/**`, `app/Nexus/Plugins/{Name}/**`, or
`app/Nexus/Widgets/{Name}/**` is added, edited, or removed in this project (or a sibling
project built on the same `nodex/nexus` package — e.g. Testovenexus, booksite), copy that
module/plugin/widget's current folder state into the matching location under
`C:\OSPanel1\home\nexus-modules-archive\{modules|plugins|widgets}\{Name}\` before finishing
the task. The archive is the publication-ready, deduplicated snapshot of every module/plugin/
widget across every `nodex/nexus`-based project on this machine (see its own `README.md`/
`DUPLICATES.md`) — it must never silently drift out of sync with what the live projects
actually ship.

Do not blindly overwrite an archive copy that is a deliberately merged/canonical variant
(built by reconciling differences between two or more source projects — see `DUPLICATES.md`
section 4a/4b/4c for that history) without checking whether the incoming change still applies
cleanly to the merged version; when unsure, surface the conflict instead of overwriting.

A brand-new module/plugin/widget that has no archive entry yet gets added, not skipped.
This does not apply to the `packages/nodex/nexus` framework package itself (the archive holds
content built *on* the framework, not the framework).
