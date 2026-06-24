<?php

if (!defined('ABSPATH')) {
    exit;
}

class Wbizmo Form Builder_Form_Renderer {
    public function init() {
        add_shortcode('pulseform', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
    }

    public function enqueue_public_assets() {
        wp_enqueue_style(
            'pulseforms-public',
            PULSEFORMS_URL . 'assets/css/public.css',
            [],
            PULSEFORMS_VERSION
        );

        wp_enqueue_script(
            'pulseforms-public',
            PULSEFORMS_URL . 'assets/js/public.js',
            ['jquery'],
            PULSEFORMS_VERSION,
            true
        );

        wp_localize_script(
            'pulseforms-public',
            'Wbizmo Form BuilderPublic',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('pulseforms_public_nonce'),
            ]
        );
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts(['id' => 0], $atts, 'pulseform');
        $form_id = absint($atts['id']);

        if (!$form_id) {
            return $this->render_error_notice(__('Form ID is missing.', 'wbizmo-form-builder'));
        }

        $form = $this->get_form($form_id);

        if (!$form || $form->status !== 'active') {
            return $this->render_error_notice(__('This form is unavailable.', 'wbizmo-form-builder'));
        }

        $fields = json_decode($form->fields, true);
        $settings = json_decode($form->settings, true);
        $style_settings = json_decode($form->style_settings, true);

        if (!is_array($fields)) {
            Wbizmo Form Builder_Logger::log('error', 'form_render_failed', 'Wbizmo Form Builder could not render form because fields JSON is invalid.', [
                'form_id'    => $form_id,
                'form_name'  => $form->name,
                'page_url'   => $this->current_page_url(),
                'raw_fields' => $form->fields,
            ]);

            return $this->render_error_notice(__('Something went wrong. Please try again later.', 'wbizmo-form-builder'));
        }

        if (!is_array($settings)) {
            $settings = [];
        }

        if (!is_array($style_settings)) {
            $style_settings = [];
        }

        $theme = isset($style_settings['theme']) ? sanitize_key($style_settings['theme']) : 'aurora';
        $style_mode = isset($style_settings['style_mode']) ? sanitize_key($style_settings['style_mode']) : 'pulse';
        $submit_text = isset($settings['submit_text']) ? sanitize_text_field($settings['submit_text']) : __('Submit', 'wbizmo-form-builder');
        $captcha_enabled = !empty($settings['captcha_enabled']);

        $primary_color = isset($style_settings['primary_color']) && $style_settings['primary_color']
            ? sanitize_hex_color($style_settings['primary_color'])
            : '#0E2238';

        $accent_color = isset($style_settings['accent_color']) && $style_settings['accent_color']
            ? sanitize_hex_color($style_settings['accent_color'])
            : '#C5A572';

        $field_radius = isset($style_settings['field_radius']) ? absint($style_settings['field_radius']) : 14;
        $button_radius = isset($style_settings['button_radius']) ? absint($style_settings['button_radius']) : 14;
        $inline_style = sprintf(
            '--pf-public-primary:%s;--pf-public-accent:%s;--pf-public-radius:%dpx;--pf-public-button-radius:%dpx;',
            esc_attr($primary_color),
            esc_attr($accent_color),
            esc_attr($field_radius),
            esc_attr($button_radius)
        );

        $captcha_a = wp_rand(2, 9);
        $captcha_b = wp_rand(2, 9);
        $captcha_answer = $captcha_a + $captcha_b;

        ob_start();
        ?>
        <div
            class="pulseforms-wrapper pulseforms-theme-<?php echo esc_attr($theme); ?> pulseforms-style-<?php echo esc_attr($style_mode); ?>"
            data-form-id="<?php echo esc_attr($form_id); ?>"
            style="<?php echo esc_attr($inline_style); ?>"
        >
            <form class="pulseforms-form" method="post" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="pulseforms_submit_form">
                <input type="hidden" name="pulseforms_form_id" value="<?php echo esc_attr($form_id); ?>">
                <input type="hidden" name="pulseforms_page_url" value="<?php echo esc_url($this->current_page_url()); ?>">
                <input type="hidden" name="pulseforms_nonce" value="<?php echo esc_attr(wp_create_nonce('pulseforms_submit_' . $form_id)); ?>">

                <?php if ($captcha_enabled) : ?>
                    <input type="hidden" name="pulseforms_captcha_hash" value="<?php echo esc_attr(wp_hash((string) $captcha_answer)); ?>">
                <?php endif; ?>

                <div class="pulseforms-hp-field" aria-hidden="true">
                    <label>
                        <?php esc_html_e('Leave this field empty', 'wbizmo-form-builder'); ?>
                        <input type="text" name="pulseforms_website" tabindex="-1" autocomplete="off">
                    </label>
                </div>

                <div class="pulseforms-fields">
                    <?php foreach ($fields as $field) : ?>
                        <?php echo $this->render_field($field); ?>
                    <?php endforeach; ?>

                    <?php if ($captcha_enabled) : ?>
                        <div class="pulseforms-field pulseforms-field-captcha pulseforms-width-full">
                            <label for="pulseforms_captcha_<?php echo esc_attr($form_id); ?>">
                                <?php echo esc_html(sprintf(__('Security check: What is %d + %d?', 'wbizmo-form-builder'), $captcha_a, $captcha_b)); ?>
                                <span class="pulseforms-required">*</span>
                            </label>

                            <input
                                type="number"
                                id="pulseforms_captcha_<?php echo esc_attr($form_id); ?>"
                                name="pulseforms_captcha_answer"
                                placeholder="<?php esc_attr_e('Enter answer', 'wbizmo-form-builder'); ?>"
                                required
                            >
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pulseforms-actions">
                    <button type="submit" class="pulseforms-submit">
                        <span class="pulseforms-submit-text"><?php echo esc_html($submit_text); ?></span>
                        <span class="pulseforms-loader" aria-hidden="true"></span>
                    </button>
                </div>

                <div class="pulseforms-feedback" role="status" aria-live="polite"></div>
            </form>
        </div>
        <?php

        return ob_get_clean();
    }

    private function get_form($form_id) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wbizmo_form_builder_forms WHERE id = %d", $form_id)
        );
    }

    private function render_field($field) {
        $id = isset($field['id']) ? sanitize_key($field['id']) : 'field_' . wp_rand(1000, 9999);
        $type = isset($field['type']) ? sanitize_key($field['type']) : 'text';
        $label = isset($field['label']) ? sanitize_text_field($field['label']) : ucfirst($id);
        $placeholder = isset($field['placeholder']) ? sanitize_text_field($field['placeholder']) : '';
        $required = !empty($field['required']);
        $required_attr = $required ? ' required' : '';
        $width = isset($field['width']) ? sanitize_key($field['width']) : 'full';

        $name = 'pulseforms_fields[' . $id . ']';
        $field_id = 'pulseforms_' . $id . '_' . wp_rand(1000, 9999);

        ob_start();
        ?>
        <div class="pulseforms-field pulseforms-field-<?php echo esc_attr($type); ?> pulseforms-width-<?php echo esc_attr($width); ?>">
            <?php if (!in_array($type, ['hidden', 'html'], true)) : ?>
                <label for="<?php echo esc_attr($field_id); ?>">
                    <?php echo esc_html($label); ?>
                    <?php if ($required) : ?>
                        <span class="pulseforms-required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>

            <?php
            switch ($type) {
                case 'email':
                    $this->input_field('email', $field_id, $name, $placeholder, $required);
                    break;

                case 'phone':
                    $this->input_field('tel', $field_id, $name, $placeholder, $required);
                    break;

                case 'number':
                    $this->input_field('number', $field_id, $name, $placeholder, $required);
                    break;

                case 'password':
                    $this->input_field('password', $field_id, $name, $placeholder, $required);
                    break;

                case 'textarea':
                    ?>
                    <textarea id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"<?php echo esc_attr($required_attr); ?>></textarea>
                    <?php
                    break;

                case 'select':
                    $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : ['Option One', 'Option Two'];
                    ?>
                    <select id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($name); ?>"<?php echo esc_attr($required_attr); ?>>
                        <option value=""><?php esc_html_e('Select an option', 'wbizmo-form-builder'); ?></option>
                        <?php foreach ($options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    break;

                case 'checkbox':
                    $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : ['Yes'];
                    ?>
                    <div class="pulseforms-choice-list">
                        <?php foreach ($options as $index => $option) : ?>
                            <label class="pulseforms-choice">
                                <input type="checkbox" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($option); ?>"<?php echo $required && $index === 0 ? ' required' : ''; ?>>
                                <span class="pulseforms-checkbox-ui"></span>
                                <span><?php echo esc_html($option); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                case 'radio':
                    $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : ['Option One', 'Option Two'];
                    ?>
                    <div class="pulseforms-choice-list">
                        <?php foreach ($options as $option) : ?>
                            <label class="pulseforms-choice">
                                <input type="radio" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($option); ?>"<?php echo esc_attr($required_attr); ?>>
                                <span class="pulseforms-radio-ui"></span>
                                <span><?php echo esc_html($option); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                case 'toggle':
                    ?>
                    <label class="pulseforms-toggle">
                        <input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1">
                        <span class="pulseforms-toggle-ui"></span>
                        <span><?php echo esc_html($placeholder ?: __('Enable option', 'wbizmo-form-builder')); ?></span>
                    </label>
                    <?php
                    break;

                case 'date':
                    $this->input_field('date', $field_id, $name, $placeholder, $required);
                    break;

                case 'file':
                    ?>
                    <div class="pulseforms-file">
                        <input type="file" id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($name); ?>"<?php echo esc_attr($required_attr); ?>>
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <span class="material-symbols-outlined">upload_file</span>
                            <strong><?php esc_html_e('Choose file', 'wbizmo-form-builder'); ?></strong>
                            <small><?php esc_html_e('Click to upload', 'wbizmo-form-builder'); ?></small>
                        </label>
                    </div>
                    <?php
                    break;

                case 'hidden':
                    ?>
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="">
                    <?php
                    break;

                case 'html':
                    ?>
                    <div class="pulseforms-html-block">
                        <?php echo wp_kses_post($placeholder); ?>
                    </div>
                    <?php
                    break;

                case 'text':
                default:
                    $this->input_field('text', $field_id, $name, $placeholder, $required);
                    break;
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    private function input_field($type, $field_id, $name, $placeholder, $required) {
        $required_attr = $required ? ' required' : '';
        ?>
        <input type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"<?php echo esc_attr($required_attr); ?>>
        <?php
    }

    private function render_error_notice($message) {
        return '<div class="pulseforms-wrapper"><div class="pulseforms-system-notice"><span class="material-symbols-outlined">error</span>' . esc_html($message) . '</div></div>';
    }

    private function current_page_url() {
        global $wp;

        if (isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
            $scheme = is_ssl() ? 'https://' : 'http://';
            return esc_url_raw($scheme . sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) . sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])));
        }

        return home_url(add_query_arg([], $wp->request));
    }
}