# Formuel

Formuel is a lightweight WordPress form plugin that stores school registration submissions in the site database and outputs a clean, theme-friendly layout.

## Features
- Shortcode `[formuel]` renders a responsive registration form.
- Submissions stored in a dedicated database table with pricing and voucher data.
- CSV export and admin list view for received registrations.
- Minimal styling that adapts to most themes.

## Installation
1. Copy the `formuel` folder into `wp-content/plugins/`.
2. Activate **Formuel** in the WordPress admin.
3. Add `[formuel]` to any page or post.

## Database
On activation the plugin creates a table named `{prefix}formuel_entries` with participant, guardian, contact data, program, days, voucher code, total amount, and timestamps.

## Pricing & vouchers
- Set a base price per day in **Formuel → Settings**.
- Add voucher codes in the format `CODE,type,amount` (type is `percent` or `fixed`).

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
