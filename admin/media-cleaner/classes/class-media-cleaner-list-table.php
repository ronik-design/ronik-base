<?php
// Ensure ABSPATH is defined for CLI/testing environments
if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__, 5) . '/');
}
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Media_Cleaner_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'media_cleaner_item',
            'plural'   => 'media_cleaner_items',
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        $columns = [
            'cb'             => '<input type="checkbox" />',
            'file_thumbnail' => 'Thumbnail',
            'file_type'      => 'Type',
            'file_size'      => 'File Size',
            'file_id'        => 'ID',
            'file_media_link'=> 'Media Link',
            'file_path'      => 'Path',
        ];
        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = array(
            'file_size' => array('file_size', false),
        );
        return $sortable_columns;
    }

    public function prepare_items() {
        $data = $this->get_sample_data();
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Sorting logic
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : '';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        if ($orderby === 'file_size') {
            usort($data, function($a, $b) use ($order) {
                $a_size = $this->parse_size($a['file_size']);
                $b_size = $this->parse_size($b['file_size']);
                if ($a_size == $b_size) return 0;
                if ($order === 'asc') {
                    return ($a_size < $b_size) ? -1 : 1;
                } else {
                    return ($a_size > $b_size) ? -1 : 1;
                }
            });
        }

        // Pagination
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, ($current_page - 1) * $per_page, $per_page);
        $this->items = $data;
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="media_cleaner_item[]" value="%s" />', $item['file_id']);
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'file_thumbnail':
                case 'file_type':
            case 'file_media_link':
                return $item[$column_name]; // Allow HTML
            case 'file_id':
            case 'file_size':
            case 'file_path':
                return esc_html($item[$column_name]);
            default:
                return print_r($item, true);
        }
    }

    private function get_sample_data() {
        // Use home_url() instead of hardcoded URL for portability
        $api_url = home_url('/wp-json/mediacleaner/v1/mediacollector/large?filter=all');
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            return [];
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $items = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                $file_id = isset($item['id']) ? esc_html($item['id']) : '';
                $file_path = isset($item['media_file']) ? esc_html($item['media_file']) : '';
                $items[] = [
                    'file_id'         => $file_id,
                    'file_thumbnail'  => isset($item['img-thumb']) ? $item['img-thumb'] : '',
                    'file_type'       => isset($item['media_file_type']) ? esc_html($item['media_file_type']) : '',
                    'file_size'       => isset($item['media_size']) ? esc_html($item['media_size']) : '',
                    'file_path'       => $file_path,
                    'file_media_link' => $file_path ? '<a href="' . esc_url($file_path) . '" target="_blank">View Media</a>' : '',
                ];
            }
        }
        return $items;
    }

    private function parse_size($size_str) {
        // Accepts strings like '1.2 MB', '500 KB', etc.
        if (preg_match('/([\d.]+)\s*(KB|MB|GB|B)/i', $size_str, $matches)) {
            $size = (float)$matches[1];
            $unit = strtoupper($matches[2]);
            switch ($unit) {
                case 'GB': return $size * 1024 * 1024 * 1024;
                case 'MB': return $size * 1024 * 1024;
                case 'KB': return $size * 1024;
                case 'B':
                default: return $size;
            }
        }
        return 0;
    }
}
