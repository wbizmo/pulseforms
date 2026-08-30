<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Private_Files {
    public function init() {
        // WordPress performs extension/MIME validation first; this filter moves the
        // accepted upload out of the public media directory before PulseForms stores it.
        add_filter('wp_handle_upload', [$this, 'privatize_submission_upload'], 10, 2);
        add_action('admin_post_pulseforms_download_private_file', [$this, 'download_private_file']);

        // The existing limiter/logger should never trust caller-supplied forwarding
        // headers. Default to REMOTE_ADDR unless a deployment adds its own trusted
        // proxy layer before WordPress.
        if ($this->is_pulseforms_submission()) {
            unset($_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR']);
        }
    }

    public function privatize_submission_upload($upload, $context) {
        if (!$this->is_pulseforms_submission() || !is_array($upload) || !empty($upload['error'])) {
            return $upload;
        }

        $source = isset($upload['file']) ? (string) $upload['file'] : '';
        if ($source === '' || !is_file($source)) {
            return $upload;
        }

        $directory = $this->private_directory();
        if (!$directory) {
            $upload['error'] = __('PulseForms could not create its private upload directory.', 'pulseforms');
            return $upload;
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
            $upload['error'] = __('PulseForms could not move the upload into private storage.', 'pulseforms');
            return $upload;
        }

        @chmod($destination, 0640);

        $upload['file'] = $destination;
        $upload['url'] = add_query_arg(
            [
                'action' => 'pulseforms_download_private_file',
                'file'   => $stored_name,
            ],
            admin_url('admin-post.php')
        );

        return $upload;
    }

    public function download_private_file() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to download submission files.', 'pulseforms'), 403);
        }

        $requested = isset($_GET['file']) ? sanitize_file_name(wp_unslash($_GET['file'])) : '';
        $requested = basename($requested);
        if ($requested === '') {
            wp_die(esc_html__('Invalid file request.', 'pulseforms'), 400);
        }

        $directory = $this->private_directory(false);
        $path = $directory ? trailingslashit($directory) . $requested : '';
        if (!$directory || !is_file($path) || !is_readable($path)) {
            wp_die(esc_html__('The requested submission file was not found.', 'pulseforms'), 404);
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

    private function is_pulseforms_submission() {
        $action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : '';
        return $action === 'pulseforms_submit_form';
    }

    private function private_directory($create = true) {
        $default = trailingslashit(dirname(ABSPATH)) . 'pulseforms-private';
        $directory = apply_filters('pulseforms_private_upload_dir', $default);
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
            // Defense in depth if a host overrides the directory into a web-served path.
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
