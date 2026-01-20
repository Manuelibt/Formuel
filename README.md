# Formuel

Formuel is a lightweight WordPress form plugin that stores submissions in the site database and outputs a clean, theme-friendly layout.

## Features
- Shortcode `[formuel]` renders a responsive contact form.
- Submissions stored in a dedicated database table.
- Minimal styling that adapts to most themes.

## Installation
1. Copy the `formuel` folder into `wp-content/plugins/`.
2. Activate **Formuel** in the WordPress admin.
3. Add `[formuel]` to any page or post.

## Database
On activation the plugin creates a table named `{prefix}formuel_entries` with name, email, message, and timestamp fields.

## Localization
All user-facing strings are wrapped with `__()` or `esc_html__()` using the `formuel` text domain. Generate or refresh the translation template with a gettext tool such as WP-CLI:

```bash
wp i18n make-pot . languages/formuel.pot
```

If WP-CLI is not available, you can generate the POT file with `xgettext` by targeting the PHP sources and using the same keywords.

## Tests
The project ships with a PHPUnit scaffold compatible with the WordPress test suite.

1. Install the WordPress test suite (see the official WP handbook).
2. Set the required environment variables for database access (DB_NAME, DB_USER, DB_PASSWORD, DB_HOST).
3. Run:
   ```bash
   phpunit
   ```

## Uninstall
Removing the plugin via WordPress will drop the custom table and delete stored entries.
