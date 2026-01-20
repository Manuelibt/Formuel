<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

final class Formuel_Entries_List_Table extends WP_List_Table
{
    private const PER_PAGE = 20;

    public function get_columns(): array
    {
        return [
            'id' => __('ID', 'formuel'),
            'name' => __('Name', 'formuel'),
            'email' => __('E-Mail', 'formuel'),
            'message' => __('Nachricht', 'formuel'),
            'created_at' => __('Erstellt am', 'formuel'),
        ];
    }

    public function column_default($item, $column_name)
    {
        if (array_key_exists($column_name, $item)) {
            return esc_html((string) $item[$column_name]);
        }

        return '';
    }

    public function prepare_items(): void
    {
        global $wpdb;

        $table = Formuel_DB::table_name();
        $current_page = $this->get_pagenum();
        $offset = (int) ($current_page - 1) * self::PER_PAGE;

        [$where_sql, $params] = $this->get_filter_where();

        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
        $prepared_count_sql = $params === [] ? $count_sql : $wpdb->prepare($count_sql, $params);
        $total_items = (int) $wpdb->get_var($prepared_count_sql);

        $items_sql = "SELECT id, name, email, message, created_at FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $items_params = array_merge($params, [self::PER_PAGE, $offset]);

        $this->items = $wpdb->get_results($wpdb->prepare($items_sql, $items_params), ARRAY_A);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => self::PER_PAGE,
            'total_pages' => (int) ceil($total_items / self::PER_PAGE),
        ]);
    }

    private function get_filter_where(): array
    {
        global $wpdb;

        $where = '1=1';
        $params = [];

        $email = sanitize_text_field(wp_unslash((string) ($_GET['formuel_email'] ?? '')));
        if ($email !== '') {
            $where .= ' AND email LIKE %s';
            $params[] = '%' . $wpdb->esc_like($email) . '%';
        }

        $date = sanitize_text_field(wp_unslash((string) ($_GET['formuel_date'] ?? '')));
        if ($date !== '') {
            $start = $date . ' 00:00:00';
            $end = $date . ' 23:59:59';
            $where .= ' AND created_at BETWEEN %s AND %s';
            $params[] = $start;
            $params[] = $end;
        }

        return [$where, $params];
    }
}

final class Formuel_Entries_Admin
{
    public static function register_menu(): void
    {
        add_menu_page(
            __('Formuel Einträge', 'formuel'),
            __('Formuel Einträge', 'formuel'),
            'manage_options',
            'formuel-entries',
            [self::class, 'render_page'],
            'dashicons-feedback',
            26
        );
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Du hast keine Berechtigung, diese Seite zu sehen.', 'formuel'));
        }

        $list_table = new Formuel_Entries_List_Table();
        $list_table->prepare_items();

        $email = sanitize_text_field(wp_unslash((string) ($_GET['formuel_email'] ?? '')));
        $date = sanitize_text_field(wp_unslash((string) ($_GET['formuel_date'] ?? '')));

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Formuel Einträge', 'formuel') . '</h1>';
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="formuel-entries" />';
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions">';
        echo '<label class="screen-reader-text" for="formuel-date">' . esc_html__('Datum', 'formuel') . '</label>';
        echo '<input type="date" id="formuel-date" name="formuel_date" value="' . esc_attr($date) . '" />';
        echo '<label class="screen-reader-text" for="formuel-email">' . esc_html__('E-Mail', 'formuel') . '</label>';
        echo '<input type="text" id="formuel-email" name="formuel_email" placeholder="' . esc_attr__('E-Mail filtern', 'formuel') . '" value="' . esc_attr($email) . '" />';
        submit_button(__('Filter', 'formuel'), 'secondary', '', false);
        echo '</div>';
        echo '</div>';
        $list_table->display();
        echo '</form>';
        echo '</div>';
    }
}
