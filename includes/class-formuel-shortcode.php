<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Formuel_Shortcode
{
    public const NONCE_ACTION = 'formuel_submit';
    private const FORM_CACHE_TTL = 300;

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
        $base_price = (float) get_option('formuel_base_price', 0);

        if (!empty($_GET['formuel_status'])) {
            $message = sanitize_text_field(wp_unslash($_GET['formuel_status']));
        }

        $cached = self::get_cached_submission();
        if (!empty($cached)) {
            $values = array_merge($values, $cached['values'] ?? []);
            $errors = $cached['errors'] ?? [];
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
        $errors = [];
        $values['participant_name'] = sanitize_text_field(wp_unslash($_POST['formuel_participant_name'] ?? ''));
        $values['guardian_name'] = sanitize_text_field(wp_unslash($_POST['formuel_guardian_name'] ?? ''));
        $values['email'] = sanitize_email(wp_unslash($_POST['formuel_email'] ?? ''));
        $values['phone'] = sanitize_text_field(wp_unslash($_POST['formuel_phone'] ?? ''));
        $values['program'] = sanitize_text_field(wp_unslash($_POST['formuel_program'] ?? ''));
        $values['days'] = (int) wp_unslash($_POST['formuel_days'] ?? 0);
        $values['voucher_code'] = strtoupper(sanitize_text_field(wp_unslash($_POST['formuel_voucher_code'] ?? '')));
        $values['message'] = sanitize_textarea_field(wp_unslash($_POST['formuel_message'] ?? ''));
        $values['submitted_at'] = (int) wp_unslash($_POST['formuel_timestamp'] ?? 0);

        if (!empty($_POST['formuel_hp'])) {
            self::redirect_with_status('error');
        }

        if (empty($values['participant_name'])) {
            $errors['participant_name'] = __('Please enter the participant name.', 'formuel');
        }

        if (empty($values['guardian_name'])) {
            $errors['guardian_name'] = __('Please enter the guardian name.', 'formuel');
        }

        if (empty($values['email'])) {
            $errors['email'] = __('Please enter a valid email.', 'formuel');
        }

        if (empty($values['program'])) {
            $errors['program'] = __('Please select a program.', 'formuel');
        }

        if ($values['days'] <= 0) {
            $errors['days'] = __('Please select the number of days.', 'formuel');
        }

        if (empty($values['message'])) {
            $errors['message'] = __('Please add a short note.', 'formuel');
        }

        if (!empty($values['submitted_at']) && (time() - $values['submitted_at'] < 3)) {
            $errors['form'] = __('Please wait a moment before submitting.', 'formuel');
        }

        if (!empty($errors)) {
            self::cache_submission($values, $errors);
            self::redirect_with_status('error');
        }

        $pricing = self::calculate_total($values['days'], $values['voucher_code']);

        global $wpdb;
        $wpdb->insert(
            Formuel_DB::table_name(),
            [
                'participant_name' => $values['participant_name'],
                'guardian_name' => $values['guardian_name'],
                'email' => $values['email'],
                'phone' => $values['phone'],
                'program' => $values['program'],
                'days' => $values['days'],
                'voucher_code' => $values['voucher_code'],
                'total_amount' => $pricing['total'],
                'message' => $values['message'],
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%f', '%s', '%s']
        );

        if ((bool) get_option('formuel_notify_email', false)) {
            $recipient = get_option('formuel_notify_recipient', get_option('admin_email'));
            $subject = get_option('formuel_notify_subject', __('New Formuel registration', 'formuel'));
            $body = sprintf(
                "New registration received:\n\nParticipant: %s\nGuardian: %s\nEmail: %s\nPhone: %s\nProgram: %s\nDays: %d\nVoucher: %s\nTotal: %.2f\nMessage: %s\n",
                $values['participant_name'],
                $values['guardian_name'],
                $values['email'],
                $values['phone'],
                $values['program'],
                $values['days'],
                $values['voucher_code'],
                $pricing['total'],
                $values['message']
            );
            wp_mail($recipient, $subject, $body);
        }

        self::redirect_with_status('success');
    }

    private static function default_values(): array
    {
        return [
            'participant_name' => '',
            'guardian_name' => '',
            'email' => '',
            'phone' => '',
            'program' => '',
            'days' => 1,
            'voucher_code' => '',
            'message' => '',
            'submitted_at' => 0,
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
        set_transient(self::cache_key(), [
            'values' => $values,
            'errors' => $errors,
        ], self::FORM_CACHE_TTL);
    }

    private static function get_cached_submission(): array
    {
        $cached = get_transient(self::cache_key());
        if (!is_array($cached)) {
            return [];
        }
        delete_transient(self::cache_key());
        return $cached;
    }

    private static function cache_key(): string
    {
        $fingerprint = md5((string) ($_SERVER['REMOTE_ADDR'] ?? '') . (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        return 'formuel_submission_' . $fingerprint;
    }

    private static function calculate_total(int $days, string $voucher_code): array
    {
        $base_price = (float) get_option('formuel_base_price', 0);
        $subtotal = $days * $base_price;
        $voucher = self::find_voucher($voucher_code);
        $discount = 0.0;

        if (!empty($voucher)) {
            if ($voucher['type'] === 'percent') {
                $discount = $subtotal * ($voucher['amount'] / 100);
            } else {
                $discount = $voucher['amount'];
            }
        }

        $total = max(0, $subtotal - $discount);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    private static function find_voucher(string $voucher_code): array
    {
        $raw = (string) get_option('formuel_voucher_codes', '');
        if ($raw === '' || $voucher_code === '') {
            return [];
        }

        $lines = preg_split('/\\r\\n|\\r|\\n/', $raw);
        foreach ($lines as $line) {
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) < 3) {
                continue;
            }
            if (strtoupper($parts[0]) !== $voucher_code) {
                continue;
            }
            $type = $parts[1] === 'fixed' ? 'fixed' : 'percent';
            $amount = max(0, (float) $parts[2]);

            return [
                'type' => $type,
                'amount' => $amount,
            ];
        }

        return [];
    }
}
