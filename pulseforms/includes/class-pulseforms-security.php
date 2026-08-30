<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Security {
    public function init() {
        add_action('wp_ajax_pulseforms_submit_form', [$this, 'normalize_client_ip'], 0);
        add_action('wp_ajax_nopriv_pulseforms_submit_form', [$this, 'normalize_client_ip'], 0);
        add_filter('upload_dir', [$this, 'private_submission_upload_dir']);
        add_action('admin_post_pulseforms_download_submission_file', [$this, 'download_submission_file']);
    }

    public function normalize_client_ip() {
        if (apply_filters('pulseforms_trust_proxy_headers', false)) {
            return;
        }

        unset($_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function private_submission_upload_dir($dirs) {
        if (!$this->is_submission_request()) {
            return $dirs;
        }

        $base = $this->private_base_dir();
        $subdir = isset($dirs['subdir']) ? (string) $dirs['subdir'] : '';
        $path = rtrim($base, '/\\') . $subdir;

        if (!wp_mkdir_p($path) || !is_writable($path)) {
            $dirs['error'] = __('PulseForms could not create its private upload directory.', 'pulseforms');
            return $dirs;
        }

        $this->write_deny_files($base);

        $dirs['basedir'] = $base;
        $dirs['path'] = $path;
        $dirs['baseurl'] = '';
        $dirs['url'] = '';

        return $dirs;
    }

    public function download_submission_file() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to download submission files.', 'pulseforms'), 403);
        }

        $submission_id = isset($_GET['submission_id']) ? absint($_GET['submission_id']) : 0;
        $field_id = isset($_GET['field_id']) ? sanitize_key(wp_unslash($_GET['field_id'])) : '';

        if (!$submission_id || !$field_id) {
            wp_die(esc_html__('Invalid submission file request.', 'pulseforms'), 400);
        }

        check_admin_referer('pulseforms_download_submission_file_' . $submission_id . '_' . $field_id);

        global $wpdb;
        $submission = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT files FROM {$wpdb->prefix}pulseforms_submissions WHERE id = %d",
                $submission_id
            )
        );

        if (!$submission) {
            wp_die(esc_html__('Submission not found.', 'pulseforms'), 404);
        }

        $files = json_decode($submission->files, true);
        $file = is_array($files) && isset($files[$field_id]) && is_array($files[$field_id])
            ? $files[$field_id]
            : null;

        if (!$file || empty($file['path'])) {
            wp_die(esc_html__('Submission file not found.', 'pulseforms'), 404);
        }

        $real = realpath((string) $file['path']);
        if (!$real || !$this->is_allowed_submission_path($real) || !is_file($real) || !is_readable($real)) {
            wp_die(esc_html__('Submission file is unavailable.', 'pulseforms'), 404);
        }

        $name = !empty($file['name']) ? sanitize_file_name($file['name']) : basename($real);
        $mime = !empty($file['type']) ? sanitize_text_field($file['type']) : 'application/octet-stream';

        nocache_headers();
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($real));
        header('Content-Disposition: attachment; filename="' . rawurlencode($name) . '"');
        readfile($real);
        exit;
    }

    private function is_submission_request() {
        $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
        return wp_doing_ajax() && $action === 'pulseforms_submit_form';
    }

    private function private_base_dir() {
        $wordpress_root = rtrim(ABSPATH, '/\\');
        $default = dirname($wordpress_root) . DIRECTORY_SEPARATOR . 'pulseforms-private-uploads';
        return (string) apply_filters('pulseforms_private_upload_dir', $default);
    }

    private function is_allowed_submission_path($real) {
        $private = realpath($this->private_base_dir());
        if ($private && str_starts_with($real, rtrim($private, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            return true;
        }

        $uploads = wp_upload_dir();
        $legacy = !empty($uploads['basedir']) ? realpath($uploads['basedir']) : false;
        return $legacy && str_starts_with($real, rtrim($legacy, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
    }

    private function write_deny_files($base) {
        if (!is_dir($base)) {
            return;
        }

        $htaccess = rtrim($base, '/\\') . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Require all denied\nDeny from all\n");
        }

        $index = rtrim($base, '/\\') . DIRECTORY_SEPARATOR . 'index.php';
        if (!file_exists($index)) {
            @file_put_contents($index, "<?php\nhttp_response_code(404);\nexit;\n");
        }
    }
}
