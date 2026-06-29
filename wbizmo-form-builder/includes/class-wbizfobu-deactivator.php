<?php

if (!defined('ABSPATH')) {
    exit;
}

class WBIZFOBU_Deactivator {
    public static function deactivate() {
        wp_clear_scheduled_hook('wbizfobu_daily_cleanup');
    }
}