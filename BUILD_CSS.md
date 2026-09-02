# Rebuilding the compiled CSS

This project used to load Tailwind from the CDN (`cdn.tailwindcss.com`),
which ships the full JIT compiler to every visitor's browser and
recompiles all styles on every page load. That's very slow.

It now uses a precompiled, purged, minified CSS file at
`assets/css/tailwind.css`, checked into the repo, so no build step is
needed on the server (important for shared hosting like InfinityFree,
which has no SSH/Node).

## If you add new Tailwind classes to any PHP/JS file

The compiled CSS only contains the classes it found when it was built.
If you add a page with new class names, rebuild it locally (needs
Node.js — not required on the server, just wherever you edit code):

```bash
cd build
npm install -D tailwindcss@3
npx tailwindcss -i ./input.css -o ../assets/css/tailwind.css --minify
```

(A ready-to-use `tailwind.config.js` and `input.css` are in this repo.)

Then re-upload the updated `assets/css/tailwind.css` to your server —
no other files change.

## Note on dynamically-built class names

If you ever write PHP that builds a Tailwind class by concatenating a
variable into the middle of it (e.g. `'bg-' . $color . '-100'`), the
compiler can't see that at build time and the style will be missing.
Always write the full class as a literal string, even inside a
ternary or array (e.g. `'bg-emerald-100'`) — that's safe and is the
pattern already used throughout this codebase.
