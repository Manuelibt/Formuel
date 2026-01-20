<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Formuel_Shortcode
{
    public const NONCE_ACTION = 'formuel_submit';

    public static function register(): void
    {
        add_shortcode('formuel', [self::class, 'render']);
        add_action('init', [self::class, 'handle_submission']);
    }

    public static function render(): string
    {
        wp_enqueue_style('formuel-style');
        wp_enqueue_script('formuel-script');

        $values = self::default_values();
        $message = '';

        if (!empty($_GET['formuel_status'])) {
            $message = sanitize_text_field(wp_unslash($_GET['formuel_status']));
        }

        ob_start();
        include __DIR__ . '/../templates/form.php';
        return (string) ob_get_clean();
    }

    public static function handle_submission(): void
    {
        if (empty($_POST['formuel_submit'])) {
            return;
        }

        if (empty($_POST['formuel_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['formuel_nonce'])), self::NONCE_ACTION)) {
            wp_die(esc_html__('Security check failed.', 'formuel'));
        }

        $values = self::default_values();
        $values['name'] = sanitize_text_field(wp_unslash($_POST['formuel_name'] ?? ''));
        $values['email'] = sanitize_email(wp_unslash($_POST['formuel_email'] ?? ''));
        $values['message'] = sanitize_textarea_field(wp_unslash($_POST['formuel_message'] ?? ''));

        if (empty($values['name']) || empty($values['email']) || empty($values['message'])) {
            self::redirect_with_status('error');
        }

        global $wpdb;
        $wpdb->insert(
            Formuel_DB::table_name(),
            [
                'name' => $values['name'],
                'email' => $values['email'],
                'message' => $values['message'],
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s']
        );

        $recipient = Formuel_Admin::notify_recipient();
        if ($recipient !== null) {
            $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
            $subject = sprintf(__('New Formuel submission on %s', 'formuel'), $site_name);
            $body = sprintf(
                '<h2>%s</h2><p><strong>%s</strong> %s</p><p><strong>%s</strong> %s</p><p><strong>%s</strong><br>%s</p>',
                esc_html__('New form submission', 'formuel'),
                esc_html__('Name:', 'formuel'),
                esc_html($values['name']),
                esc_html__('Email:', 'formuel'),
                esc_html($values['email']),
                esc_html__('Message:', 'formuel'),
                nl2br(esc_html($values['message']))
            );

            wp_mail($recipient, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
        }

        self::redirect_with_status('success');
    }

    private static function default_values(): array
    {
        return [
            'name' => '',
            'email' => '',
            'message' => '',
        ];
    }

    private static function redirect_with_status(string $status): void
    {
        $redirect = add_query_arg('formuel_status', $status, wp_get_referer() ?: home_url('/'));
        wp_safe_redirect($redirect);
        exit;
    }
}
