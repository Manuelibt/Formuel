<?php
/**
 * Plugin Name: Formuel
 * Description: Lightweight form plugin that stores submissions in the WordPress database.
 * Version: 0.2.0
 * Author: Formuel
 * Text Domain: formuel
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-formuel-plugin.php';

Formuel_Plugin::init();
