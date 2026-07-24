<?php
/**
 * Plugin Name: Qndrs Telraam Inzicht
 * Plugin URI: https://github.com/Qndrs/telraam
 * Description: Display Telraam traffic statistics on your WordPress website.
 * Version: 0.2.0
 * Requires at least: 6.5
 * Requires PHP: 8.3
 * Author: Qndrs
 * Author URI: https://qndrs.nl
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qndrs-telraam-inzicht
 * Domain Path: /languages
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('QNDRS_TELRAAM_INZICHT_VERSION', '0.2.0');
define('QNDRS_TELRAAM_INZICHT_MINIMUM_PHP_VERSION', '8.3.0');
define('QNDRS_TELRAAM_INZICHT_PLUGIN_FILE', __FILE__);
define('QNDRS_TELRAAM_INZICHT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QNDRS_TELRAAM_INZICHT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QNDRS_TELRAAM_INZICHT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Validate runtime requirements during activation.
 */
function qndrs_telraam_inzicht_activate(): void
{
    if (version_compare(PHP_VERSION, QNDRS_TELRAAM_INZICHT_MINIMUM_PHP_VERSION, '<')) {
        deactivate_plugins(QNDRS_TELRAAM_INZICHT_PLUGIN_BASENAME);

        wp_die(
            esc_html(
                sprintf(
                    /* translators: 1: required PHP version, 2: current PHP version. */
                    __('Qndrs Telraam Inzicht requires PHP %1$s or higher. Your server is running PHP %2$s.', 'qndrs-telraam-inzicht'),
                    QNDRS_TELRAAM_INZICHT_MINIMUM_PHP_VERSION,
                    PHP_VERSION
                )
            ),
            esc_html__('Qndrs Telraam Inzicht activation failed', 'qndrs-telraam-inzicht'),
            [
                'back_link' => true,
            ]
        );
    }
}

register_activation_hook(__FILE__, 'qndrs_telraam_inzicht_activate');

/**
 * Show an admin notice when the plugin is loaded on an unsupported PHP version.
 */
function qndrs_telraam_inzicht_php_version_notice(): void
{
    if (! current_user_can('activate_plugins')) {
        return;
    }

    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html(
            sprintf(
                /* translators: 1: required PHP version, 2: current PHP version. */
                __('Qndrs Telraam Inzicht requires PHP %1$s or higher. Your server is running PHP %2$s. The plugin has not been loaded.', 'qndrs-telraam-inzicht'),
                QNDRS_TELRAAM_INZICHT_MINIMUM_PHP_VERSION,
                PHP_VERSION
            )
        )
    );
}

if (version_compare(PHP_VERSION, QNDRS_TELRAAM_INZICHT_MINIMUM_PHP_VERSION, '<')) {
    add_action('admin_notices', 'qndrs_telraam_inzicht_php_version_notice');
    return;
}

require_once QNDRS_TELRAAM_INZICHT_PLUGIN_DIR . 'includes/Plugin.php';

add_action('plugins_loaded', static function (): void {
    Qndrs\TelraamInzicht\Plugin::boot();
});
