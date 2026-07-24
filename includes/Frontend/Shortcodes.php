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
        $rows = self::extract_rows($report);
        $summary = self::summarize_rows($rows);

        ob_start();
        ?>
        <div class="qndrs-telraam-inzicht qndrs-telraam-inzicht--<?php echo esc_attr($view); ?>">
            <h2><?php esc_html_e('Telraam traffic statistics', 'qndrs-telraam-inzicht'); ?></h2>
            <p>
                <?php
                printf(
                    /* translators: 1: segment ID, 2: number of days. */
                    esc_html__('Segment %1$s, last %2$d days.', 'qndrs-telraam-inzicht'),
                    esc_html($segment_id),
                    esc_html((string) $days)
                );
                ?>
            </p>

            <dl class="qndrs-telraam-inzicht__summary">
                <div>
                    <dt><?php esc_html_e('Pedestrians', 'qndrs-telraam-inzicht'); ?></dt>
                    <dd><?php echo esc_html((string) $summary['pedestrians']); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('Two-wheelers', 'qndrs-telraam-inzicht'); ?></dt>
                    <dd><?php echo esc_html((string) $summary['two_wheelers']); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('Cars', 'qndrs-telraam-inzicht'); ?></dt>
                    <dd><?php echo esc_html((string) $summary['cars']); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('Heavy vehicles', 'qndrs-telraam-inzicht'); ?></dt>
                    <dd><?php echo esc_html((string) $summary['heavy_vehicles']); ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('Average uptime', 'qndrs-telraam-inzicht'); ?></dt>
                    <dd><?php echo esc_html(self::format_uptime($summary['average_uptime'])); ?></dd>
                </div>
            </dl>

            <?php if ('table' === $view || 'summary-table' === $view) : ?>
                <?php echo self::render_table($rows); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Extract traffic rows from known Telraam response shapes.
     *
     * @param array<string, mixed> $report Telraam API response.
     * @return array<int, array<string, mixed>>
     */
    private static function extract_rows(array $report): array
    {
        if (isset($report['report']) && is_array($report['report'])) {
            return array_values(array_filter($report['report'], 'is_array'));
        }

        if (isset($report['data']) && is_array($report['data'])) {
            return array_values(array_filter($report['data'], 'is_array'));
        }

        if (array_is_list($report)) {
            return array_values(array_filter($report, 'is_array'));
        }

        return [];
    }

    /**
     * Summarize traffic rows.
     *
     * @param array<int, array<string, mixed>> $rows Traffic rows.
     * @return array{
     *     pedestrians: int,
     *     two_wheelers: int,
     *     cars: int,
     *     heavy_vehicles: int,
     *     average_uptime: float|null
     * }
     */
    private static function summarize_rows(array $rows): array
    {
        $summary = [
            'pedestrians' => 0,
            'two_wheelers' => 0,
            'cars' => 0,
            'heavy_vehicles' => 0,
            'average_uptime' => null,
        ];

        $uptime_sum = 0.0;
        $uptime_count = 0;

        foreach ($rows as $row) {
            $summary['pedestrians'] += self::read_int($row, ['pedestrian', 'pedestrians']);
            $summary['two_wheelers'] += self::read_int($row, ['bike', 'bikes', 'bicycle', 'two_wheelers']);
            $summary['cars'] += self::read_int($row, ['car', 'cars']);
            $summary['heavy_vehicles'] += self::read_int($row, ['heavy', 'heavy_vehicle', 'heavy_vehicles', 'truck', 'trucks']);

            $uptime = self::read_float($row, ['uptime']);
            if (null !== $uptime) {
                $uptime_sum += $uptime;
                ++$uptime_count;
            }
        }

        if ($uptime_count > 0) {
            $summary['average_uptime'] = $uptime_sum / $uptime_count;
        }

        return $summary;
    }

    /**
     * Render a compact data table.
     *
     * @param array<int, array<string, mixed>> $rows Traffic rows.
     */
    private static function render_table(array $rows): string
    {
        if ([] === $rows) {
            return '<p>' . esc_html__('No traffic rows were returned by the Telraam API.', 'qndrs-telraam-inzicht') . '</p>';
        }

        ob_start();
        ?>
        <table class="qndrs-telraam-inzicht__table">
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
                <?php foreach (array_slice($rows, 0, 24) as $row) : ?>
                    <tr>
                        <td><?php echo esc_html(self::read_string($row, ['date', 'datetime', 'time', 'time_start'])); ?></td>
                        <td><?php echo esc_html((string) self::read_int($row, ['pedestrian', 'pedestrians'])); ?></td>
                        <td><?php echo esc_html((string) self::read_int($row, ['bike', 'bikes', 'bicycle', 'two_wheelers'])); ?></td>
                        <td><?php echo esc_html((string) self::read_int($row, ['car', 'cars'])); ?></td>
                        <td><?php echo esc_html((string) self::read_int($row, ['heavy', 'heavy_vehicle', 'heavy_vehicles', 'truck', 'trucks'])); ?></td>
                        <td><?php echo esc_html(self::format_uptime(self::read_float($row, ['uptime']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Render a public-safe error message.
     */
    private static function render_error(string $message): string
    {
        return sprintf(
            '<div class="qndrs-telraam-inzicht qndrs-telraam-inzicht--error"><p>%s</p></div>',
            esc_html($message)
        );
    }

    /**
     * Read an integer value from the first matching row key.
     *
     * @param array<string, mixed> $row Traffic row.
     * @param array<int, string>   $keys Candidate keys.
     */
    private static function read_int(array $row, array $keys): int
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return max(0, (int) round((float) $row[$key]));
            }
        }

        return 0;
    }

    /**
     * Read a float value from the first matching row key.
     *
     * @param array<string, mixed> $row Traffic row.
     * @param array<int, string>   $keys Candidate keys.
     */
    private static function read_float(array $row, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return (float) $row[$key];
            }
        }

        return null;
    }

    /**
     * Read a string value from the first matching row key.
     *
     * @param array<string, mixed> $row Traffic row.
     * @param array<int, string>   $keys Candidate keys.
     */
    private static function read_string(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_scalar($row[$key])) {
                return (string) $row[$key];
            }
        }

        return '';
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
