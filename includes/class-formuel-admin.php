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

    public static function register_menu(): void
    {
        add_options_page(
            __('Formuel', 'formuel'),
            __('Formuel', 'formuel'),
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'render_page']
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

    public static function render_page(): void
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
