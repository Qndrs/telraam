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

require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Api/Client.php';
require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Api/TrafficReportRepository.php';
require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Api/TrafficReportNormalizer.php';
require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Admin/SettingsPage.php';
require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Frontend/Shortcodes.php';

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
        if (is_admin()) {
            Admin\SettingsPage::register();
        }

        Frontend\Shortcodes::register();
    }
}
