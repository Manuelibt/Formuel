<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Formuel_DB
{
    private const TABLE_SUFFIX = 'formuel_entries';

    public static function plugin_file(): string
    {
        return dirname(__DIR__) . '/formuel.php';
    }

    public static function table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    public static function activate(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table = self::table_name();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            email varchar(200) NOT NULL,
            message text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function deactivate(): void
    {
        // Keep data on deactivation by default.
    }

    public static function uninstall(): void
    {
        global $wpdb;

        $table = self::table_name();
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }
}
