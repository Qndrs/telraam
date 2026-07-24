<?php
/**
 * Main plugin bootstrap class.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Coordinates plugin services.
 */
final class Plugin
{
    /**
     * Boot the plugin.
     */
    public static function boot(): void
    {
        self::load_textdomain();

        if (is_admin()) {
            require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Admin/SettingsPage.php';

            Admin\SettingsPage::register();
        }
    }

    /**
     * Load plugin translations.
     */
    private static function load_textdomain(): void
    {
        load_plugin_textdomain(
            'qndrs-telraam-inzicht',
            false,
            dirname(QNDRS_TELRAAM_INZICHT_PLUGIN_BASENAME) . '/languages'
        );
    }
}
