<?php
/**
 * Frontend shortcodes.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht\Frontend;

use Qndrs\TelraamInzicht\Admin\SettingsPage;
use Qndrs\TelraamInzicht\Api\Client;
use Qndrs\TelraamInzicht\Api\TrafficReportNormalizer;
use Qndrs\TelraamInzicht\Api\TrafficReportRepository;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Registers and renders plugin shortcodes.
 */
final class Shortcodes
{
    private const SHORTCODE = 'qndrs_telraam_segment';

    /**
     * Register shortcodes.
     */
    public static function register(): void
    {
        add_shortcode(self::SHORTCODE, [self::class, 'render_segment']);
    }

    /**
     * Render Telraam segment statistics.
     *
     * @param array<string, mixed>|string $attributes Shortcode attributes.
     */
    public static function render_segment(array|string $attributes = []): string
    {
        $options = SettingsPage::get_options();

        $attributes = shortcode_atts(
            [
                'id' => $options['default_segment_id'],
                'days' => (string) $options['default_days'],
                'rows' => '24',
                'view' => 'summary',
            ],
            is_array($attributes) ? $attributes : [],
            self::SHORTCODE
        );

        $segment_id = preg_replace('/[^0-9]/', '', (string) $attributes['id']) ?? '';
        $days = max(1, min(90, absint($attributes['days'])));
        $rows_limit = self::parse_rows_limit((string) $attributes['rows']);
        $view = sanitize_key((string) $attributes['view']);

        if ('' === $segment_id) {
            return self::render_error(__('No Telraam segment ID was provided.', 'qndrs-telraam-inzicht'));
        }

        $repository = new TrafficReportRepository(
            new Client($options['api_token']),
            $options['cache_duration_minutes']
        );

        $report = $repository->get_traffic_report($segment_id, $days);

        if (is_wp_error($report)) {
            return self::render_error($report->get_error_message());
        }

        return self::render_report($report, $segment_id, $days, $view, $rows_limit);
    }

    /**
     * Parse the optional rows limit.
     */
    private static function parse_rows_limit(string $rows): ?int
    {
        if ('all' === strtolower(trim($rows))) {
            return null;
        }

        $limit = absint($rows);

        return $limit > 0 ? min($limit, 500) : 24;
    }

    /**
     * Render a report.
     *
     * @param array<string, mixed> $report Telraam API response.
     * @param string               $segment_id Telraam segment ID.
     * @param int                  $days Number of requested days.
     * @param string               $view Requested shortcode view.
     * @param int|null             $rows_limit Maximum number of table rows, or null for all rows.
     */
    private static function render_report(array $report, string $segment_id, int $days, string $view, ?int $rows_limit): string
    {
        $normalized_report = (new TrafficReportNormalizer())->normalize($report);
        $rows = $normalized_report['rows'];
        $summary = $normalized_report['summary'];
        $component_id = wp_unique_id('qndrs-telraam-inzicht-');
        $heading_id = $component_id . 'heading';
        $summary_heading_id = $component_id . 'summary-heading';

        ob_start();
        ?>
        <section class="qndrs-telraam-inzicht qndrs-telraam-inzicht--<?php echo esc_attr($view); ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
            <header class="qndrs-telraam-inzicht__header">
                <h2 id="<?php echo esc_attr($heading_id); ?>"><?php esc_html_e('Telraam traffic statistics', 'qndrs-telraam-inzicht'); ?></h2>
                <p class="qndrs-telraam-inzicht__meta">
                    <?php echo esc_html(self::format_segment_period($segment_id, $days)); ?>
                </p>
            </header>

            <section class="qndrs-telraam-inzicht__summary-section" aria-labelledby="<?php echo esc_attr($summary_heading_id); ?>">
                <h3 id="<?php echo esc_attr($summary_heading_id); ?>"><?php esc_html_e('Traffic totals', 'qndrs-telraam-inzicht'); ?></h3>
                <dl class="qndrs-telraam-inzicht__summary">
                    <div class="qndrs-telraam-inzicht__summary-item qndrs-telraam-inzicht__summary-item--pedestrians">
                        <dt><?php esc_html_e('Pedestrians', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['pedestrians'])); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item qndrs-telraam-inzicht__summary-item--two-wheelers">
                        <dt><?php esc_html_e('Two-wheelers', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['two_wheelers'])); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item qndrs-telraam-inzicht__summary-item--cars">
                        <dt><?php esc_html_e('Cars', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['cars'])); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item qndrs-telraam-inzicht__summary-item--heavy-vehicles">
                        <dt><?php esc_html_e('Heavy vehicles', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['heavy_vehicles'])); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item qndrs-telraam-inzicht__summary-item--uptime">
                        <dt><?php esc_html_e('Average uptime', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html(self::format_uptime($summary['average_uptime'])); ?></dd>
                    </div>
                </dl>
            </section>

            <?php if ('table' === $view || 'summary-table' === $view) : ?>
                <?php echo self::render_table($rows, $segment_id, $days, $rows_limit); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </section>
        <?php

        return self::clean_markup((string) ob_get_clean());
    }

    /**
     * Format the segment and period metadata text.
     */
    private static function format_segment_period(string $segment_id, int $days): string
    {
        if (1 === $days) {
            return sprintf(
                /* translators: %s: segment ID. */
                __('Segment %s, last day.', 'qndrs-telraam-inzicht'),
                $segment_id
            );
        }

        return sprintf(
            /* translators: 1: segment ID, 2: number of days. */
            __('Segment %1$s, last %2$d days.', 'qndrs-telraam-inzicht'),
            $segment_id,
            $days
        );
    }

    /**
     * Format the traffic table caption.
     */
    private static function format_table_caption(string $segment_id, int $days, int $visible_rows, int $total_rows): string
    {
        if (1 === $days) {
            $period = sprintf(
                /* translators: %s: segment ID. */
                __('Hourly traffic rows for segment %s, last day.', 'qndrs-telraam-inzicht'),
                $segment_id
            );
        } else {
            $period = sprintf(
                /* translators: 1: segment ID, 2: number of days. */
                __('Hourly traffic rows for segment %1$s, last %2$d days.', 'qndrs-telraam-inzicht'),
                $segment_id,
                $days
            );
        }

        if ($visible_rows >= $total_rows) {
            return $period;
        }

        return sprintf(
            /* translators: 1: table caption, 2: number of visible rows, 3: total number of rows. */
            __('%1$s Showing %2$d of %3$d rows.', 'qndrs-telraam-inzicht'),
            $period,
            $visible_rows,
            $total_rows
        );
    }

    /**
     * Render a compact data table.
     *
     * @param array<int, array{
     *     time: string,
     *     pedestrians: int,
     *     two_wheelers: int,
     *     cars: int,
     *     heavy_vehicles: int,
     *     uptime: float|null
     * }> $rows Normalized traffic rows.
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of requested days.
     * @param int|null $rows_limit Maximum number of table rows, or null for all rows.
     */
    private static function render_table(array $rows, string $segment_id, int $days, ?int $rows_limit): string
    {
        if ([] === $rows) {
            return '<p class="qndrs-telraam-inzicht__empty">' . esc_html__('No traffic rows were returned by the Telraam API.', 'qndrs-telraam-inzicht') . '</p>';
        }

        $visible_rows = null === $rows_limit ? $rows : array_slice($rows, 0, $rows_limit);
        $caption = self::format_table_caption($segment_id, $days, count($visible_rows), count($rows));

        ob_start();
        ?>
        <p class="qndrs-telraam-inzicht__table-caption"><?php echo esc_html($caption); ?></p>
        <div class="qndrs-telraam-inzicht__table-wrapper">
            <table class="qndrs-telraam-inzicht__table">
                <caption><?php echo esc_html($caption); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e('Time', 'qndrs-telraam-inzicht'); ?></th>
                        <th scope="col"><?php esc_html_e('Pedestrians', 'qndrs-telraam-inzicht'); ?></th>
                        <th scope="col"><?php esc_html_e('Two-wheelers', 'qndrs-telraam-inzicht'); ?></th>
                        <th scope="col"><?php esc_html_e('Cars', 'qndrs-telraam-inzicht'); ?></th>
                        <th scope="col"><?php esc_html_e('Heavy vehicles', 'qndrs-telraam-inzicht'); ?></th>
                        <th scope="col"><?php esc_html_e('Uptime', 'qndrs-telraam-inzicht'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visible_rows as $row) : ?>
                        <tr>
                            <th scope="row"><?php echo self::render_time($row['time']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
                            <td><?php echo esc_html(self::format_count($row['pedestrians'])); ?></td>
                            <td><?php echo esc_html(self::format_count($row['two_wheelers'])); ?></td>
                            <td><?php echo esc_html(self::format_count($row['cars'])); ?></td>
                            <td><?php echo esc_html(self::format_count($row['heavy_vehicles'])); ?></td>
                            <td><?php echo esc_html(self::format_uptime($row['uptime'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php

        return self::clean_markup((string) ob_get_clean());
    }

    /**
     * Render an accessible time element for a Telraam timestamp.
     */
    private static function render_time(string $raw_time): string
    {
        if ('' === $raw_time) {
            return esc_html__('Unknown', 'qndrs-telraam-inzicht');
        }

        $timestamp = strtotime($raw_time);

        if (false === $timestamp) {
            return esc_html($raw_time);
        }

        return sprintf(
            '<time datetime="%1$s">%2$s</time>',
            esc_attr(gmdate('c', $timestamp)),
            esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp))
        );
    }

    /**
     * Render a public-safe error message.
     */
    private static function render_error(string $message): string
    {
        return sprintf(
            '<div class="qndrs-telraam-inzicht qndrs-telraam-inzicht--error" role="alert" aria-live="polite"><p>%s</p></div>',
            esc_html($message)
        );
    }

    /**
     * Remove template indentation whitespace between HTML tags.
     */
    private static function clean_markup(string $markup): string
    {
        return trim((string) preg_replace('/>\s+</', '><', $markup));
    }

    /**
     * Format a traffic count for display.
     */
    private static function format_count(int $count): string
    {
        return number_format_i18n($count);
    }

    /**
     * Format uptime for display.
     */
    private static function format_uptime(?float $uptime): string
    {
        if (null === $uptime) {
            return __('Unknown', 'qndrs-telraam-inzicht');
        }

        if ($uptime <= 1.0) {
            return sprintf(
                /* translators: %s: uptime percentage. */
                __('%s%%', 'qndrs-telraam-inzicht'),
                number_format_i18n($uptime * 100, 1)
            );
        }

        return number_format_i18n($uptime, 1);
    }
}
