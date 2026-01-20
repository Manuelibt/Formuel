<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-formuel-entries-table.php';

final class Formuel_Admin
{
    public static function register(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', [self::class, 'register_menu']);
        add_action('admin_post_formuel_export_csv', [self::class, 'export_csv']);
        self::register_settings();
    }

    public static function register_menu(): void
    {
        add_menu_page(
            __('Formuel', 'formuel'),
            __('Formuel', 'formuel'),
            'manage_options',
            'formuel',
            [self::class, 'render_entries_page'],
            'dashicons-clipboard',
            58
        );

        add_submenu_page(
            'formuel',
            __('Entries', 'formuel'),
            __('Entries', 'formuel'),
            'manage_options',
            'formuel',
            [self::class, 'render_entries_page']
        );

        add_submenu_page(
            'formuel',
            __('Settings', 'formuel'),
            __('Settings', 'formuel'),
            'manage_options',
            'formuel-settings',
            [self::class, 'render_settings_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting('formuel_settings', 'formuel_base_price', [
            'type' => 'number',
            'sanitize_callback' => static function ($value) {
                return max(0, (float) $value);
            },
            'default' => 0,
        ]);

        register_setting('formuel_settings', 'formuel_voucher_codes', [
            'type' => 'string',
            'sanitize_callback' => static function ($value) {
                return sanitize_textarea_field((string) $value);
            },
            'default' => '',
        ]);

        register_setting('formuel_settings', 'formuel_notify_email', [
            'type' => 'boolean',
            'sanitize_callback' => static function ($value) {
                return (bool) $value;
            },
            'default' => false,
        ]);

        register_setting('formuel_settings', 'formuel_notify_recipient', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => get_option('admin_email'),
        ]);

        register_setting('formuel_settings', 'formuel_notify_subject', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('New Formuel registration', 'formuel'),
        ]);
    }

    public static function render_entries_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'formuel'));
        }

        $table = new Formuel_Entries_Table();
        $table->prepare_items();

        $export_url = wp_nonce_url(
            admin_url('admin-post.php?action=formuel_export_csv'),
            'formuel_export_csv'
        );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Formuel Entries', 'formuel'); ?></h1>
            <p>
                <a class="button button-primary" href="<?php echo esc_url($export_url); ?>">
                    <?php echo esc_html__('Export CSV', 'formuel'); ?>
                </a>
            </p>
            <form method="post">
                <?php $table->search_box(__('Search', 'formuel'), 'formuel-search'); ?>
                <?php $table->display(); ?>
            </form>
        </div>
        <?php
    }

    public static function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'formuel'));
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Formuel Settings', 'formuel'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('formuel_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="formuel_base_price"><?php echo esc_html__('Base price per day', 'formuel'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" min="0" id="formuel_base_price" name="formuel_base_price" value="<?php echo esc_attr(get_option('formuel_base_price', 0)); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="formuel_voucher_codes"><?php echo esc_html__('Voucher codes', 'formuel'); ?></label>
                        </th>
                        <td>
                            <textarea id="formuel_voucher_codes" name="formuel_voucher_codes" rows="6" class="large-text code"><?php echo esc_textarea(get_option('formuel_voucher_codes', '')); ?></textarea>
                            <p class="description">
                                <?php echo esc_html__('One per line: CODE,type,amount (type: percent or fixed). Example: FERIEN10,percent,10', 'formuel'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Email notifications', 'formuel'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="formuel_notify_email" value="1" <?php checked((bool) get_option('formuel_notify_email', false)); ?> />
                                <?php echo esc_html__('Send notification email on new registration', 'formuel'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="formuel_notify_recipient"><?php echo esc_html__('Notification recipient', 'formuel'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="formuel_notify_recipient" name="formuel_notify_recipient" value="<?php echo esc_attr(get_option('formuel_notify_recipient', get_option('admin_email'))); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="formuel_notify_subject"><?php echo esc_html__('Notification subject', 'formuel'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="formuel_notify_subject" name="formuel_notify_subject" value="<?php echo esc_attr(get_option('formuel_notify_subject', __('New Formuel registration', 'formuel'))); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function export_csv(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'formuel'));
        }

        check_admin_referer('formuel_export_csv');

        $rows = Formuel_DB::get_entries();
        $filename = 'formuel-entries-' . gmdate('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'ID',
            'Participant',
            'Guardian',
            'Email',
            'Phone',
            'Program',
            'Days',
            'Voucher',
            'Total',
            'Message',
            'Created',
        ]);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'],
                $row['participant_name'],
                $row['guardian_name'],
                $row['email'],
                $row['phone'],
                $row['program'],
                $row['days'],
                $row['voucher_code'],
                $row['total_amount'],
                $row['message'],
                $row['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}
