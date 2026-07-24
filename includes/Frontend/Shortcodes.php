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
                'view' => 'summary',
            ],
            is_array($attributes) ? $attributes : [],
            self::SHORTCODE
        );

        $segment_id = preg_replace('/[^0-9]/', '', (string) $attributes['id']) ?? '';
        $days = max(1, min(90, absint($attributes['days'])));
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

        return self::render_report($report, $segment_id, $days, $view);
    }

    /**
     * Render a report.
     *
     * @param array<string, mixed> $report Telraam API response.
     * @param string               $segment_id Telraam segment ID.
     * @param int                  $days Number of requested days.
     * @param string               $view Requested shortcode view.
     */
    private static function render_report(array $report, string $segment_id, int $days, string $view): string
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
                    <div class="qndrs-telraam-inzicht__summary-item">
                        <dt><?php esc_html_e('Pedestrians', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html((string) $summary['pedestrians']); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item">
                        <dt><?php esc_html_e('Two-wheelers', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html((string) $summary['two_wheelers']); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item">
                        <dt><?php esc_html_e('Cars', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html((string) $summary['cars']); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item">
                        <dt><?php esc_html_e('Heavy vehicles', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html((string) $summary['heavy_vehicles']); ?></dd>
                    </div>
                    <div class="qndrs-telraam-inzicht__summary-item">
                        <dt><?php esc_html_e('Average uptime', 'qndrs-telraam-inzicht'); ?></dt>
                        <dd><?php echo esc_html(self::format_uptime($summary['average_uptime'])); ?></dd>
                    </div>
                </dl>
            </section>

            <?php if ('table' === $view || 'summary-table' === $view) : ?>
                <?php echo self::render_table($rows, $segment_id, $days); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Format the segment and period metadata text.
     */
    private static function format_segment_period(string $segment_id, int $days): string
    {
        if (1 === $days) {
            return sprintf(
                /* translators: %s: segment ID. */
                __('Segment %s, last 1 day.', 'qndrs-telraam-inzicht'),
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
                __('Hourly traffic rows for segment %s, last 1 day.', 'qndrs-telraam-inzicht'),
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
     */
    private static function render_table(array $rows, string $segment_id, int $days): string
    {
        if ([] === $rows) {
            return '<p class="qndrs-telraam-inzicht__empty">' . esc_html__('No traffic rows were returned by the Telraam API.', 'qndrs-telraam-inzicht') . '</p>';
        }

        $visible_rows = array_slice($rows, 0, 24);

        ob_start();
        ?>
        <div class="qndrs-telraam-inzicht__table-wrapper">
            <table class="qndrs-telraam-inzicht__table">
                <caption>
                    <?php echo esc_html(self::format_table_caption($segment_id, $days, count($visible_rows), count($rows))); ?>
                </caption>
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
                            <th scope="row"><?php echo esc_html('' !== $row['time'] ? $row['time'] : __('Unknown', 'qndrs-telraam-inzicht')); ?></th>
                            <td><?php echo esc_html((string) $row['pedestrians']); ?></td>
                            <td><?php echo esc_html((string) $row['two_wheelers']); ?></td>
                            <td><?php echo esc_html((string) $row['cars']); ?></td>
                            <td><?php echo esc_html((string) $row['heavy_vehicles']); ?></td>
                            <td><?php echo esc_html(self::format_uptime($row['uptime'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php

        return (string) ob_get_clean();
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
