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
        $values['subject'] = sanitize_text_field(wp_unslash($_POST['formuel_subject'] ?? ''));
        $values['inquiry_type'] = sanitize_text_field(wp_unslash($_POST['formuel_inquiry_type'] ?? ''));
        $values['other_details'] = sanitize_textarea_field(wp_unslash($_POST['formuel_other_details'] ?? ''));
        $values['newsletter_opt_in'] = !empty($_POST['formuel_newsletter']) ? 'yes' : 'no';
        $values['message'] = sanitize_textarea_field(wp_unslash($_POST['formuel_message'] ?? ''));

        $allowed_types = ['general', 'support', 'other'];
        if (!in_array($values['inquiry_type'], $allowed_types, true)) {
            $values['inquiry_type'] = 'general';
        }

        if ($values['inquiry_type'] !== 'other') {
            $values['other_details'] = '';
        }

        if (empty($values['name']) || empty($values['email']) || empty($values['subject']) || empty($values['message'])) {
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

        $columns = Formuel_DB::table_columns();
        $data = [
            'name' => $values['name'],
            'email' => $values['email'],
            'subject' => $values['subject'],
            'inquiry_type' => $values['inquiry_type'],
            'newsletter_opt_in' => $values['newsletter_opt_in'] === 'yes' ? 1 : 0,
            'other_details' => $values['other_details'],
            'message' => $values['message'],
            'attachment_url' => $attachment_url,
            'created_at' => current_time('mysql'),
        ];
        $formats = [
            'name' => '%s',
            'email' => '%s',
            'subject' => '%s',
            'inquiry_type' => '%s',
            'newsletter_opt_in' => '%d',
            'other_details' => '%s',
            'message' => '%s',
            'attachment_url' => '%s',
            'created_at' => '%s',
        ];

        if (!empty($columns)) {
            $data = array_intersect_key($data, array_flip($columns));
            $formats = array_values(array_intersect_key($formats, $data));
        }

        global $wpdb;
        $inserted = $wpdb->insert(
            Formuel_DB::table_name(),
            $data,
            $formats
        );

        if ($inserted === false) {
            self::redirect_with_status('error');
        }

        $recipient = Formuel_Admin::notify_recipient();
        if ($recipient !== null) {
            $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
            $subject = sprintf(__('New Formuel submission on %s', 'formuel'), $site_name);
            $body = sprintf(
                '<h2>%s</h2><p><strong>%s</strong> %s</p><p><strong>%s</strong> %s</p><p><strong>%s</strong> %s</p><p><strong>%s</strong> %s</p>%s<p><strong>%s</strong> %s</p><p><strong>%s</strong><br>%s</p>%s',
                esc_html__('New form submission', 'formuel'),
                esc_html__('Name:', 'formuel'),
                esc_html($values['name']),
                esc_html__('Email:', 'formuel'),
                esc_html($values['email']),
                esc_html__('Subject:', 'formuel'),
                esc_html($values['subject']),
                esc_html__('Inquiry type:', 'formuel'),
                esc_html($values['inquiry_type']),
                $values['other_details'] !== '' ? sprintf('<p><strong>%s</strong><br>%s</p>', esc_html__('Other details:', 'formuel'), nl2br(esc_html($values['other_details']))) : '',
                esc_html__('Newsletter opt-in:', 'formuel'),
                $values['newsletter_opt_in'] === 'yes' ? esc_html__('Yes', 'formuel') : esc_html__('No', 'formuel'),
                esc_html__('Message:', 'formuel'),
                nl2br(esc_html($values['message'])),
                $attachment_url ? sprintf('<p><strong>%s</strong> <a href="%s">%s</a></p>', esc_html__('Attachment:', 'formuel'), esc_url($attachment_url), esc_html__('View file', 'formuel')) : ''
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
}
