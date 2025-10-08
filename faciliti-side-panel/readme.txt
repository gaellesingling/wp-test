=== FACILITI-like Side Panel (Starter) ===
Contributors: chatgpt
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 0.1.0
License: GPLv2 or later

A tiny starter plugin that injects a slide-in side panel with empty placeholders (search, quick filters, categories, settings).
Uses pure JS/CSS with ARIA attributes, focus trap, ESC to close, overlay click, and a floating launcher button.

== Installation ==
1. Upload the `faciliti-side-panel` folder to `/wp-content/plugins/` or install the zip via Plugins → Add New → Upload.
2. Activate the plugin.
3. The launcher button appears on all front-end pages (in the bottom-right). It injects markup via `wp_footer`.
   Optionally use the `[faciliti_panel]` shortcode to place the panel manually in content or templates.

== Customize ==
- Edit `templates/panel.php` to replace placeholders with real UI.
- Adjust styles in `assets/css/panel.css`.
- Expand behavior in `assets/js/panel.js` (no framework required).

== Notes ==
- Keep classes/IDs if you want to retain the default behavior.
- Everything is intentionally minimal; build on it as needed.
