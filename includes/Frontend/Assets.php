<?php
/**
 * Frontend assets.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht\Frontend;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Registers frontend styles.
 */
final class Assets
{
    private const STYLE_HANDLE = 'qndrs-traffic-display-for-telraam';

    /**
     * Register frontend asset hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend_styles']);
    }

    /**
     * Enqueue the base frontend stylesheet.
     */
    public static function enqueue_frontend_styles(): void
    {
        wp_enqueue_style(
            self::STYLE_HANDLE,
            QNDRS_TELRAAM_INZICHT_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            QNDRS_TELRAAM_INZICHT_VERSION
        );
    }
}
