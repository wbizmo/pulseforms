<?php

if (!defined('ABSPATH')) {
    exit;
}

class WBIZFOBU_Private_Files {
    public function init() {
        add_filter('wp_handle_upload', [$this, 'privatize_submission_upload'], 10, 2);
        add_action('admin_post_wbizfobu_download_private_file', [$this, 'download_private_file']);
        add_action('init', [$this, 'migrate_legacy_submission_files'], 20);
    }

    public function privatize_submission_upload($upload, $context) {
        if (!$this->is_wbizfobu_submission() || !is_array($upload) || !empty($upload['error'])) {
            return $upload;
        }

        $source = isset($upload['file']) ? (string) $upload['file'] : '';
        if ($source === '' || !is_file($source)) {
            return $upload;
        }

        $moved = $this->move_to_private_storage($source);
        if (is_wp_error($moved)) {
            $upload['error'] = $moved->get_error_message();
            return $upload;
        }

        $upload['file'] = $moved['path'];
        $upload['url'] = $this->download_url($moved['stored_name']);

        return $upload;
    }

    public function migrate_legacy_submission_files() {
        if (get_option('wbizfobu_private_files_migrated_v1')) {
            return;
        }

        $directory = $this->private_directory();
        if (!$directory) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wbizfobu_submissions';
        $rows = $wpdb->get_results("SELECT id, files FROM {$table} WHERE files IS NOT NULL AND files <> ''");
        $uploads = wp_get_upload_dir();
        $public_root = !empty($uploads['basedir']) ? realpath($uploads['basedir']) : false;
        $success = true;

        foreach ($rows as $row) {
            $files = json_decode($row->files, true);
            if (!is_array($files) || empty($files)) {
                continue;
            }

            $changed = false;
            foreach ($files as &$file) {
                if (!is_array($file) || empty($file['path'])) {
                    continue;
                }

                $source = realpath((string) $file['path']);
                if (!$source || !is_file($source)) {
                    continue;
                }

                $normalized_source = wp_normalize_path($source);
                $normalized_private = trailingslashit(wp_normalize_path($directory));
                if (str_starts_with($normalized_source, $normalized_private)) {
                    $file['url'] = $this->download_url(basename($source));
                    $changed = true;
                    continue;
                }

                if (!$public_root) {
                    $success = false;
                    continue;
                }

                $normalized_public = trailingslashit(wp_normalize_path($public_root));
                if (!str_starts_with($normalized_source, $normalized_public)) {
                    continue;
                }

                $moved = $this->move_to_private_storage($source);
                if (is_wp_error($moved)) {
                    $success = false;
                    continue;
                }

                $file['path'] = $moved['path'];
                $file['url'] = $this->download_url($moved['stored_name']);
                $changed = true;
            }
            unset($file);

            if ($changed) {
                $updated = $wpdb->update(
                    $table,
                    ['files' => wp_json_encode($files)],
                    ['id' => absint($row->id)],
                    ['%s'],
                    ['%d']
                );
                if ($updated === false) {
                    $success = false;
                }
            }
        }

        if ($success) {
            update_option('wbizfobu_private_files_migrated_v1', current_time('mysql'), false);
        }
    }

    public function download_private_file() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to download submission files.', 'wbizmo-form-builder'), 403);
        }

        $requested = isset($_GET['file']) ? sanitize_file_name(wp_unslash($_GET['file'])) : '';
        $requested = basename($requested);
        if ($requested === '') {
            wp_die(esc_html__('Invalid file request.', 'wbizmo-form-builder'), 400);
        }

        $directory = $this->private_directory(false);
        $path = $directory ? trailingslashit($directory) . $requested : '';
        if (!$directory || !is_file($path) || !is_readable($path)) {
            wp_die(esc_html__('The requested submission file was not found.', 'wbizmo-form-builder'), 404);
        }

        $type = wp_check_filetype($requested);
        $mime = !empty($type['type']) ? $type['type'] : 'application/octet-stream';
        $download_name = preg_replace('/^[0-9a-f-]{36}-/i', '', $requested);
        $download_name = $download_name ?: 'submission-file';

        nocache_headers();
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . rawurlencode($download_name) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    private function move_to_private_storage($source) {
        $directory = $this->private_directory();
        if (!$directory) {
            return new WP_Error(
                'wbizfobu_private_directory_failed',
                __('Wbizmo Form Builder could not create its private upload directory.', 'wbizmo-form-builder')
            );
        }

        $original = sanitize_file_name(basename($source));
        $stored_name = wp_generate_uuid4() . '-' . $original;
        $destination = trailingslashit($directory) . $stored_name;

        $moved = @rename($source, $destination);
        if (!$moved) {
            $moved = @copy($source, $destination);
            if ($moved) {
                @unlink($source);
            }
        }

        if (!$moved) {
            return new WP_Error(
                'wbizfobu_private_move_failed',
                __('Wbizmo Form Builder could not move the upload into private storage.', 'wbizmo-form-builder')
            );
        }

        @chmod($destination, 0640);

        return [
            'path' => $destination,
            'stored_name' => $stored_name,
        ];
    }

    private function download_url($stored_name) {
        return add_query_arg(
            [
                'action' => 'wbizfobu_download_private_file',
                'file'   => basename($stored_name),
            ],
            admin_url('admin-post.php')
        );
    }

    private function is_wbizfobu_submission() {
        $action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : '';
        return $action === 'wbizfobu_submit_form';
    }

    private function private_directory($create = true) {
        $default = trailingslashit(dirname(ABSPATH)) . 'wbizfobu-private';
        $directory = apply_filters('wbizfobu_private_upload_dir', $default);
        $directory = untrailingslashit(wp_normalize_path((string) $directory));

        if ($directory === '') {
            return false;
        }

        if (!is_dir($directory) && $create && !wp_mkdir_p($directory)) {
            return false;
        }

        if (!is_dir($directory)) {
            return false;
        }

        if ($create) {
            @file_put_contents($directory . '/index.php', "<?php\nhttp_response_code(404);\nexit;\n");
            @file_put_contents($directory . '/.htaccess', "Require all denied\nDeny from all\n");
            @file_put_contents(
                $directory . '/web.config',
                '<?xml version="1.0" encoding="UTF-8"?><configuration><system.webServer><authorization><deny users="*" /></authorization></system.webServer></configuration>'
            );
        }

        return $directory;
    }
}
