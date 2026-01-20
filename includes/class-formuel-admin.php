<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Formuel_Admin
{
    private const OPTION_NOTIFY_EMAIL = 'formuel_notify_email';
    private const SETTINGS_GROUP = 'formuel_settings';
    private const PAGE_SLUG = 'formuel-settings';
    private const ENTRIES_PAGE_SLUG = 'formuel-entries';

    public static function register_menu(): void
    {
        add_menu_page(
            __('Formuel Entries', 'formuel'),
            __('Formuel', 'formuel'),
            'manage_options',
            self::ENTRIES_PAGE_SLUG,
            [self::class, 'render_entries_page'],
            'dashicons-feedback',
            30
        );

        add_submenu_page(
            self::ENTRIES_PAGE_SLUG,
            __('Settings', 'formuel'),
            __('Settings', 'formuel'),
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'render_settings_page']
        );

        add_submenu_page(
            self::ENTRIES_PAGE_SLUG,
            __('Entries', 'formuel'),
            __('Entries', 'formuel'),
            'manage_options',
            self::ENTRIES_PAGE_SLUG,
            [self::class, 'render_entries_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_NOTIFY_EMAIL,
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default' => '',
            ]
        );

        add_settings_section(
            'formuel_notifications',
            __('Notifications', 'formuel'),
            '__return_null',
            self::PAGE_SLUG
        );

        add_settings_field(
            self::OPTION_NOTIFY_EMAIL,
            __('Notification email', 'formuel'),
            [self::class, 'render_notify_email_field'],
            self::PAGE_SLUG,
            'formuel_notifications'
        );
    }

    public static function render_settings_page(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Formuel Settings', 'formuel'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function render_entries_page(): void
    {
        global $wpdb;

        $entries = $wpdb->get_results(
            "SELECT * FROM " . Formuel_DB::table_name() . " ORDER BY created_at DESC LIMIT 100",
            ARRAY_A
        );
        $columns = Formuel_DB::table_columns();
        $has_subject = in_array('subject', $columns, true);
        $has_inquiry_type = in_array('inquiry_type', $columns, true);
        $has_newsletter = in_array('newsletter_opt_in', $columns, true);
        $has_other_details = in_array('other_details', $columns, true);
        $has_attachment = in_array('attachment_url', $columns, true);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Formuel Entries', 'formuel'); ?></h1>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Date', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Name', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Email', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Subject', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Inquiry type', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Newsletter', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Other details', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Message', 'formuel'); ?></th>
                        <th><?php echo esc_html__('Attachment', 'formuel'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)) : ?>
                        <tr>
                            <td colspan="9"><?php echo esc_html__('No entries found.', 'formuel'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($entries as $entry) : ?>
                            <tr>
                                <td><?php echo esc_html($entry['created_at']); ?></td>
                                <td><?php echo esc_html($entry['name']); ?></td>
                                <td><?php echo esc_html($entry['email']); ?></td>
                                <td><?php echo $has_subject ? esc_html($entry['subject']) : esc_html__('—', 'formuel'); ?></td>
                                <td><?php echo $has_inquiry_type ? esc_html($entry['inquiry_type']) : esc_html__('—', 'formuel'); ?></td>
                                <td><?php echo $has_newsletter ? ($entry['newsletter_opt_in'] ? esc_html__('Yes', 'formuel') : esc_html__('No', 'formuel')) : esc_html__('—', 'formuel'); ?></td>
                                <td><?php echo $has_other_details ? nl2br(esc_html($entry['other_details'])) : esc_html__('—', 'formuel'); ?></td>
                                <td><?php echo nl2br(esc_html($entry['message'])); ?></td>
                                <td>
                                    <?php if ($has_attachment && !empty($entry['attachment_url'])) : ?>
                                        <a href="<?php echo esc_url($entry['attachment_url']); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php echo esc_html__('View', 'formuel'); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html__('—', 'formuel'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function render_notify_email_field(): void
    {
        $value = get_option(self::OPTION_NOTIFY_EMAIL, '');
        ?>
        <input
            type="email"
            class="regular-text"
            name="<?php echo esc_attr(self::OPTION_NOTIFY_EMAIL); ?>"
            value="<?php echo esc_attr((string) $value); ?>"
            placeholder="<?php echo esc_attr(get_option('admin_email')); ?>"
        />
        <p class="description">
            <?php echo esc_html__('Leave blank to disable email notifications.', 'formuel'); ?>
        </p>
        <?php
    }

    public static function notify_recipient(): ?string
    {
        $email = get_option(self::OPTION_NOTIFY_EMAIL, '');
        if (empty($email) || !is_email($email)) {
            return null;
        }

        return $email;
    }
}
