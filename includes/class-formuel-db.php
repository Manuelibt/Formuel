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
            participant_name varchar(200) NOT NULL,
            guardian_name varchar(200) NOT NULL,
            email varchar(200) NOT NULL,
            phone varchar(100) NOT NULL,
            program varchar(200) NOT NULL,
            days int(11) NOT NULL,
            voucher_code varchar(100) NOT NULL,
            total_amount decimal(10,2) NOT NULL,
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

    public static function get_entries(int $limit = 1000, int $offset = 0, string $search = ''): array
    {
        global $wpdb;

        $table = self::table_name();
        $where = '';
        $params = [];

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where = "WHERE participant_name LIKE %s OR guardian_name LIKE %s OR email LIKE %s OR program LIKE %s";
            $params = [$like, $like, $like, $like];
        }

        $query = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
    }

    public static function count_entries(string $search = ''): int
    {
        global $wpdb;

        $table = self::table_name();
        $where = '';
        $params = [];

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where = "WHERE participant_name LIKE %s OR guardian_name LIKE %s OR email LIKE %s OR program LIKE %s";
            $params = [$like, $like, $like, $like];
        }

        $query = "SELECT COUNT(*) FROM {$table} {$where}";
        if (!empty($params)) {
            return (int) $wpdb->get_var($wpdb->prepare($query, $params));
        }

        return (int) $wpdb->get_var($query);
    }
}
