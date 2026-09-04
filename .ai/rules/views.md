---
paths:
  - 'packages/nodex/nexus/src/resources/views/**/*.blade.php'
---

# Views

## Never build a Tailwind class via runtime interpolation
A Tailwind utility class name must appear as a literal string somewhere in the file for the JIT scanner to generate its CSS — a Blade-interpolated fragment like `lg:col-span-{{ $span }}` is never generated (no build error, the class is just silently inert; see section_cards.blade.php git history for the real incident this caused, which looked fine at narrow viewports and broke the layout at real desktop width). Use an inline `style="..."` attribute for a genuinely dynamic value instead. A ternary between two full literal class strings (e.g. `{{ $cond ? "sm:col-span-2" : "" }}`) is safe and the normal way to do conditional classes — the literal text is still present in the file for the scanner to find.
