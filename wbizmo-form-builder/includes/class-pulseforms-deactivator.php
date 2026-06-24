<?php

if (!defined('ABSPATH')) {
    exit;
}

class Wbizmo Form Builder_Deactivator {
    public static function deactivate() {
        wp_clear_scheduled_hook('wbizmo_form_builder_daily_cleanup');
    }
}