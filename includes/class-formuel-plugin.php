<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-formuel-db.php';
require_once __DIR__ . '/class-formuel-shortcode.php';

final class Formuel_Plugin
{
    private const VERSION = '0.1.0';

    public static function init(): void
    {
        add_action('init', [self::class, 'register_assets']);
        add_action('init', [Formuel_Shortcode::class, 'register']);
        register_activation_hook(Formuel_DB::plugin_file(), [Formuel_DB::class, 'activate']);
        register_deactivation_hook(Formuel_DB::plugin_file(), [Formuel_DB::class, 'deactivate']);
    }

    public static function register_assets(): void
    {
        $plugin_url = plugin_dir_url(Formuel_DB::plugin_file());
        wp_register_style(
            'formuel-style',
            $plugin_url . 'assets/css/formuel.css',
            [],
            self::VERSION
        );
        wp_register_script(
            'formuel-script',
            $plugin_url . 'assets/js/formuel.js',
            ['jquery'],
            self::VERSION,
            true
        );
    }
}
