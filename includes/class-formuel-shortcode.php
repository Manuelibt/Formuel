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
        $errors = [];
        $message = '';
        $timestamp = (int) current_time('timestamp');

        if (!empty($_GET['formuel_status'])) {
            $message = sanitize_text_field(wp_unslash($_GET['formuel_status']));
        }

        if ($message === 'error') {
            $cached = self::get_cached_submission();
            if (!empty($cached)) {
                $values = $cached['values'] ?? $values;
                $errors = $cached['errors'] ?? [];
                self::clear_cached_submission();
            }
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

        $honeypot = sanitize_text_field(wp_unslash($_POST['formuel_hp'] ?? ''));
        if ($honeypot !== '') {
            self::redirect_with_status('error');
        }

        $submitted_at = absint($_POST['formuel_time'] ?? 0);
        $now = (int) current_time('timestamp');
        if ($submitted_at === 0 || ($now - $submitted_at) < 3) {
            self::redirect_with_status('error');
        }

        $values = self::default_values();
        $values['name'] = sanitize_text_field(wp_unslash($_POST['formuel_name'] ?? ''));
        $values['email'] = sanitize_email(wp_unslash($_POST['formuel_email'] ?? ''));
        $values['subject'] = sanitize_text_field(wp_unslash($_POST['formuel_subject'] ?? ''));
        $values['inquiry_type'] = sanitize_text_field(wp_unslash($_POST['formuel_inquiry_type'] ?? ''));
        $values['other_details'] = sanitize_textarea_field(wp_unslash($_POST['formuel_other_details'] ?? ''));
        $values['newsletter_opt_in'] = !empty($_POST['formuel_newsletter']) ? 'yes' : 'no';
        $values['message'] = sanitize_textarea_field(wp_unslash($_POST['formuel_message'] ?? ''));

        $errors = [];

        if (empty($values['name'])) {
            $errors['name'] = esc_html__('Please enter your name.', 'formuel');
        }

        if (empty($values['email'])) {
            $errors['email'] = esc_html__('Please enter your email address.', 'formuel');
        }

        if (empty($values['message'])) {
            $errors['message'] = esc_html__('Please enter a message.', 'formuel');
        }

        if (!empty($errors)) {
            self::cache_submission($values, $errors);
            self::redirect_with_status('error');
        }

        if ($values['inquiry_type'] === 'other' && empty($values['other_details'])) {
            self::redirect_with_status('error');
        }

        $attachment_url = '';
        if (!empty($_FILES['formuel_attachment']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $upload = wp_handle_upload(
                $_FILES['formuel_attachment'],
                ['test_form' => false]
            );
            if (isset($upload['url'])) {
                $attachment_url = $upload['url'];
            }
        }

        global $wpdb;
        $wpdb->insert(
            Formuel_DB::table_name(),
            [
                'name' => $values['name'],
                'email' => $values['email'],
                'subject' => $values['subject'],
                'inquiry_type' => $values['inquiry_type'],
                'newsletter_opt_in' => $values['newsletter_opt_in'] === 'yes' ? 1 : 0,
                'other_details' => $values['other_details'],
                'message' => $values['message'],
                'attachment_url' => $attachment_url,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );

        self::clear_cached_submission();
        self::redirect_with_status('success');
    }

    private static function default_values(): array
    {
        return [
            'name' => '',
            'email' => '',
            'subject' => '',
            'inquiry_type' => 'general',
            'other_details' => '',
            'newsletter_opt_in' => 'no',
            'message' => '',
        ];
    }

    private static function redirect_with_status(string $status): void
    {
        $redirect = add_query_arg('formuel_status', $status, wp_get_referer() ?: home_url('/'));
        wp_safe_redirect($redirect);
        exit;
    }

    private static function cache_submission(array $values, array $errors): void
    {
        set_transient(self::cached_submission_key(), [
            'values' => $values,
            'errors' => $errors,
        ], MINUTE_IN_SECONDS * 15);
    }

    private static function get_cached_submission(): array
    {
        $cached = get_transient(self::cached_submission_key());
        if (!is_array($cached)) {
            return [];
        }

        return $cached;
    }

    private static function clear_cached_submission(): void
    {
        delete_transient(self::cached_submission_key());
    }

    private static function cached_submission_key(): string
    {
        $user_id = get_current_user_id();
        if ($user_id > 0) {
            return 'formuel_submission_' . $user_id;
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : 'unknown';
        $hash = md5($ip . '|' . $agent);

        return 'formuel_submission_' . $hash;
    }
}
