<?php
/**
 * Admin settings page.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht\Admin;

use Qndrs\TelraamInzicht\Api\TrafficReportRepository;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Registers and renders the plugin settings page.
 */
final class SettingsPage
{
    private const OPTION_NAME = 'qndrs_telraam_inzicht_options';
    private const SETTINGS_GROUP = 'qndrs_telraam_inzicht_settings';
    private const PAGE_SLUG = 'qndrs-telraam-inzicht';

    /**
     * Register WordPress hooks.
     */
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_options_page']);
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_post_qndrs_telraam_inzicht_clear_cache', [self::class, 'handle_clear_cache']);
    }

    /**
     * Return normalized plugin options.
     *
     * @return array{
     *     api_token: string,
     *     default_segment_id: string,
     *     default_days: int,
     *     cache_duration_minutes: int
     * }
     */
    public static function get_options(): array
    {
        $options = get_option(self::OPTION_NAME, []);

        if (! is_array($options)) {
            $options = [];
        }

        return array_merge(self::get_default_options(), $options);
    }

    /**
     * Add the settings page below Settings.
     */
    public static function add_options_page(): void
    {
        add_options_page(
            __('Qndrs Telraam Inzicht', 'qndrs-telraam-inzicht'),
            __('Qndrs Telraam Inzicht', 'qndrs-telraam-inzicht'),
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'render_page']
        );
    }

    /**
     * Register settings, sections and fields.
     */
    public static function register_settings(): void
    {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [self::class, 'sanitize_options'],
                'default' => self::get_default_options(),
            ]
        );

        add_settings_section(
            'qndrs_telraam_inzicht_api_section',
            __('Telraam API', 'qndrs-telraam-inzicht'),
            [self::class, 'render_api_section'],
            self::PAGE_SLUG
        );

        add_settings_field(
            'api_token',
            __('API token', 'qndrs-telraam-inzicht'),
            [self::class, 'render_api_token_field'],
            self::PAGE_SLUG,
            'qndrs_telraam_inzicht_api_section'
        );

        add_settings_field(
            'default_segment_id',
            __('Default segment ID', 'qndrs-telraam-inzicht'),
            [self::class, 'render_default_segment_id_field'],
            self::PAGE_SLUG,
            'qndrs_telraam_inzicht_api_section'
        );

        add_settings_field(
            'default_days',
            __('Default period', 'qndrs-telraam-inzicht'),
            [self::class, 'render_default_days_field'],
            self::PAGE_SLUG,
            'qndrs_telraam_inzicht_api_section'
        );

        add_settings_field(
            'cache_duration_minutes',
            __('Cache duration', 'qndrs-telraam-inzicht'),
            [self::class, 'render_cache_duration_field'],
            self::PAGE_SLUG,
            'qndrs_telraam_inzicht_api_section'
        );
    }

    /**
     * Sanitize settings before storing them.
     *
     * @param mixed $input Raw option input.
     * @return array{
     *     api_token: string,
     *     default_segment_id: string,
     *     default_days: int,
     *     cache_duration_minutes: int
     * }
     */
    public static function sanitize_options(mixed $input): array
    {
        $existing = self::get_options();

        if (! is_array($input)) {
            $input = [];
        }

        $api_token = $existing['api_token'];
        if (isset($input['api_token']) && '' !== trim((string) $input['api_token'])) {
            $api_token = sanitize_text_field(wp_unslash((string) $input['api_token']));
        }

        $default_segment_id = $existing['default_segment_id'];
        if (isset($input['default_segment_id'])) {
            $default_segment_id = preg_replace('/[^0-9]/', '', sanitize_text_field(wp_unslash((string) $input['default_segment_id'])));
            $default_segment_id = '' !== $default_segment_id ? $default_segment_id : self::get_default_options()['default_segment_id'];
        }

        $default_days = $existing['default_days'];
        if (isset($input['default_days'])) {
            $default_days = self::clamp(absint($input['default_days']), 1, 90);
        }

        $cache_duration_minutes = $existing['cache_duration_minutes'];
        if (isset($input['cache_duration_minutes'])) {
            $cache_duration_minutes = self::clamp(absint($input['cache_duration_minutes']), 5, 1440);
        }

        return [
            'api_token' => $api_token,
            'default_segment_id' => $default_segment_id,
            'default_days' => $default_days,
            'cache_duration_minutes' => $cache_duration_minutes,
        ];
    }

    /**
     * Render the settings page.
     */
    public static function render_page(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage Telraam settings.', 'qndrs-telraam-inzicht'));
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php self::render_admin_notices(); ?>
            <p>
                <?php esc_html_e('Configure the Telraam API connection and default display settings for traffic statistics.', 'qndrs-telraam-inzicht'); ?>
            </p>

            <form action="options.php" method="post">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button(__('Save settings', 'qndrs-telraam-inzicht'));
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e('Cache', 'qndrs-telraam-inzicht'); ?></h2>
            <p>
                <?php esc_html_e('Clear cached traffic data for the currently configured default segment and period.', 'qndrs-telraam-inzicht'); ?>
            </p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <?php wp_nonce_field('qndrs_telraam_inzicht_clear_cache'); ?>
                <input type="hidden" name="action" value="qndrs_telraam_inzicht_clear_cache" />
                <?php submit_button(__('Clear cache', 'qndrs-telraam-inzicht'), 'secondary', 'submit', false); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Clear cached traffic data for the current default segment and period.
     */
    public static function handle_clear_cache(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to clear the Telraam cache.', 'qndrs-telraam-inzicht'));
        }

        check_admin_referer('qndrs_telraam_inzicht_clear_cache');

        $options = self::get_options();

        TrafficReportRepository::delete_cache(
            $options['default_segment_id'],
            $options['default_days']
        );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => self::PAGE_SLUG,
                    'cache-cleared' => '1',
                    'notice-nonce' => wp_create_nonce('qndrs_telraam_inzicht_cache_notice'),
                ],
                admin_url('options-general.php')
            )
        );
        exit;
    }

    /**
     * Render admin notices for settings actions.
     */
    private static function render_admin_notices(): void
    {
        if (! isset($_GET['cache-cleared'], $_GET['notice-nonce'])) {
            return;
        }

        if (! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_GET['notice-nonce'])), 'qndrs_telraam_inzicht_cache_notice')) {
            return;
        }

        if ('1' !== sanitize_text_field(wp_unslash((string) $_GET['cache-cleared']))) {
            return;
        }

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('The Telraam cache has been cleared.', 'qndrs-telraam-inzicht') . '</p></div>';
    }

    /**
     * Render section introduction.
     */
    public static function render_api_section(): void
    {
        echo '<p>' . esc_html__('Enter your Telraam API token and choose sensible defaults for frontend shortcodes.', 'qndrs-telraam-inzicht') . '</p>';
        echo '<p>' . esc_html__('API connection testing will be added after the API client is connected to the caching layer.', 'qndrs-telraam-inzicht') . '</p>';
    }

    /**
     * Render API token field.
     */
    public static function render_api_token_field(): void
    {
        $options = self::get_options();
        ?>
        <input
            type="password"
            id="<?php echo esc_attr(self::OPTION_NAME); ?>_api_token"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[api_token]"
            value=""
            class="regular-text"
            autocomplete="off"
        />
        <p class="description">
            <?php
            if ('' !== $options['api_token']) {
                esc_html_e('An API token is currently saved. Leave this field empty to keep the existing token.', 'qndrs-telraam-inzicht');
            } else {
                esc_html_e('Paste your Telraam API token. It will not be shown on the frontend.', 'qndrs-telraam-inzicht');
            }
            ?>
        </p>
        <?php
    }

    /**
     * Render default segment ID field.
     */
    public static function render_default_segment_id_field(): void
    {
        $options = self::get_options();
        ?>
        <input
            type="text"
            id="<?php echo esc_attr(self::OPTION_NAME); ?>_default_segment_id"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[default_segment_id]"
            value="<?php echo esc_attr($options['default_segment_id']); ?>"
            class="regular-text"
            inputmode="numeric"
            pattern="[0-9]+"
        />
        <p class="description">
            <?php esc_html_e('Default Telraam segment ID used when a shortcode does not specify an ID.', 'qndrs-telraam-inzicht'); ?>
        </p>
        <?php
    }

    /**
     * Render default period field.
     */
    public static function render_default_days_field(): void
    {
        $options = self::get_options();
        ?>
        <input
            type="number"
            id="<?php echo esc_attr(self::OPTION_NAME); ?>_default_days"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[default_days]"
            value="<?php echo esc_attr((string) $options['default_days']); ?>"
            class="small-text"
            min="1"
            max="90"
            step="1"
        />
        <span><?php esc_html_e('days', 'qndrs-telraam-inzicht'); ?></span>
        <p class="description">
            <?php esc_html_e('Traffic report requests are limited to a maximum period of 90 days.', 'qndrs-telraam-inzicht'); ?>
        </p>
        <?php
    }

    /**
     * Render cache duration field.
     */
    public static function render_cache_duration_field(): void
    {
        $options = self::get_options();
        ?>
        <input
            type="number"
            id="<?php echo esc_attr(self::OPTION_NAME); ?>_cache_duration_minutes"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[cache_duration_minutes]"
            value="<?php echo esc_attr((string) $options['cache_duration_minutes']); ?>"
            class="small-text"
            min="5"
            max="1440"
            step="1"
        />
        <span><?php esc_html_e('minutes', 'qndrs-telraam-inzicht'); ?></span>
        <p class="description">
            <?php esc_html_e('Caching protects your Telraam API quota. The Telraam API allows limited request rates.', 'qndrs-telraam-inzicht'); ?>
        </p>
        <?php
    }

    /**
     * Default plugin options.
     *
     * @return array{
     *     api_token: string,
     *     default_segment_id: string,
     *     default_days: int,
     *     cache_duration_minutes: int
     * }
     */
    private static function get_default_options(): array
    {
        return [
            'api_token' => '',
            'default_segment_id' => '9000010390',
            'default_days' => 7,
            'cache_duration_minutes' => 60,
        ];
    }

    /**
     * Clamp an integer between a minimum and maximum.
     */
    private static function clamp(int $value, int $minimum, int $maximum): int
    {
        return max($minimum, min($maximum, $value));
    }
}
