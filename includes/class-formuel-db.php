<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Formuel_DB
{
    private const TABLE_SUFFIX = 'formuel_entries';
    private const DB_VERSION_OPTION = 'formuel_db_version';
    private const DB_VERSION = 2;

    public static function plugin_file(): string
    {
        return dirname(__DIR__) . '/formuel.php';
    }

    public static function table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    public static function table_columns(): array
    {
        global $wpdb;

        $table = self::table_name();
        $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);
        if (!is_array($columns)) {
            return [];
        }

        return $columns;
    }

    public static function activate(): void
    {
        self::ensure_schema();
    }

    public static function maybe_upgrade(): void
    {
        $current_version = (int) get_option(self::DB_VERSION_OPTION, 0);
        if ($current_version < self::DB_VERSION) {
            self::ensure_schema();
        }
    }

    private static function ensure_schema(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table = self::table_name();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            email varchar(200) NOT NULL,
            subject varchar(200) NOT NULL,
            inquiry_type varchar(50) NOT NULL,
            newsletter_opt_in tinyint(1) NOT NULL DEFAULT 0,
            other_details text NOT NULL,
            message text NOT NULL,
            attachment_url text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
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
