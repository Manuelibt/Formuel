<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

final class Formuel_Entries_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'formuel_entry',
            'plural' => 'formuel_entries',
            'ajax' => false,
        ]);
    }

    public function get_columns(): array
    {
        return [
            'id' => __('ID', 'formuel'),
            'participant_name' => __('Participant', 'formuel'),
            'guardian_name' => __('Guardian', 'formuel'),
            'email' => __('Email', 'formuel'),
            'phone' => __('Phone', 'formuel'),
            'program' => __('Program', 'formuel'),
            'days' => __('Days', 'formuel'),
            'voucher_code' => __('Voucher', 'formuel'),
            'total_amount' => __('Total', 'formuel'),
            'created_at' => __('Created', 'formuel'),
        ];
    }

    public function prepare_items(): void
    {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

        $data = Formuel_DB::get_entries($per_page, ($current_page - 1) * $per_page, $search);
        $total_items = Formuel_DB::count_entries($search);

        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);
    }

    public function column_default($item, $column_name)
    {
        if ($column_name === 'total_amount') {
            return esc_html(number_format((float) $item[$column_name], 2));
        }

        return esc_html($item[$column_name] ?? '');
    }
}
