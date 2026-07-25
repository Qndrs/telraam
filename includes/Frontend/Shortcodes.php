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
                'title' => __('Traffic data', 'qndrs-traffic-display-for-telraam'),
                'view' => 'summary',
            ],
            is_array($attributes) ? $attributes : [],
            self::SHORTCODE
        );

        $segment_id = preg_replace('/[^0-9]/', '', (string) $attributes['id']) ?? '';
        $days = max(1, min(90, absint($attributes['days'])));
        $rows_limit = self::parse_rows_limit((string) $attributes['rows']);
        $title = self::parse_title((string) $attributes['title']);
        $view = sanitize_key((string) $attributes['view']);

        if ('' === $segment_id) {
            return self::render_error(__('No Telraam segment ID was provided.', 'qndrs-traffic-display-for-telraam'));
        }

        $repository = new TrafficReportRepository(
            new Client($options['api_token']),
            $options['cache_duration_minutes']
        );

        $report = $repository->get_traffic_report($segment_id, $days);

        if (is_wp_error($report)) {
            return self::render_error($report->get_error_message());
        }

        return self::render_report($report, $segment_id, $days, $view, $rows_limit, $title);
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
     * Parse the optional visible title.
     *
     * Empty title values hide the visible plugin heading while preserving an accessible heading.
     */
    private static function parse_title(string $title): ?string
    {
        $title = trim(wp_strip_all_tags($title));

        return '' === $title ? null : $title;
    }

    /**
     * Render a report.
     *
     * @param array<string, mixed> $report Telraam API response.
     * @param string               $segment_id Telraam segment ID.
     * @param int                  $days Number of requested days.
     * @param string               $view Requested shortcode view.
     * @param int|null             $rows_limit Maximum number of table rows, or null for all rows.
     * @param string|null          $title Visible heading text, or null to hide it visually.
     */
    private static function render_report(array $report, string $segment_id, int $days, string $view, ?int $rows_limit, ?string $title): string
    {
        $normalized_report = (new TrafficReportNormalizer())->normalize($report);
        $rows = $normalized_report['rows'];
        $summary = $normalized_report['summary'];
        $component_id = wp_unique_id('qndrs-traffic-display-for-telraam-');
        $heading_id = $component_id . 'heading';
        $summary_heading_id = $component_id . 'summary-heading';
        $heading_class = null === $title ? ' class="qndrs-traffic-display-for-telraam__screen-reader-text"' : '';
        $heading_text = $title ?? __('Traffic data', 'qndrs-traffic-display-for-telraam');

        ob_start();
        ?>
        <section class="qndrs-traffic-display-for-telraam qndrs-traffic-display-for-telraam--<?php echo esc_attr($view); ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
            <header class="qndrs-traffic-display-for-telraam__header">
                <h2 id="<?php echo esc_attr($heading_id); ?>"<?php echo $heading_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($heading_text); ?></h2>
                <p class="qndrs-traffic-display-for-telraam__meta">
                    <?php echo esc_html(self::format_segment_period($segment_id, $days)); ?>
                </p>
            </header>

            <section class="qndrs-traffic-display-for-telraam__summary-section" aria-labelledby="<?php echo esc_attr($summary_heading_id); ?>">
                <h3 id="<?php echo esc_attr($summary_heading_id); ?>"><?php esc_html_e('Traffic totals', 'qndrs-traffic-display-for-telraam'); ?></h3>
                <dl class="qndrs-traffic-display-for-telraam__summary">
                    <div class="qndrs-traffic-display-for-telraam__summary-item qndrs-traffic-display-for-telraam__summary-item--pedestrians">
                        <dt><?php esc_html_e('Pedestrians', 'qndrs-traffic-display-for-telraam'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['pedestrians'])); ?></dd>
                    </div>
                    <div class="qndrs-traffic-display-for-telraam__summary-item qndrs-traffic-display-for-telraam__summary-item--two-wheelers">
                        <dt><?php esc_html_e('Two-wheelers', 'qndrs-traffic-display-for-telraam'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['two_wheelers'])); ?></dd>
                    </div>
                    <div class="qndrs-traffic-display-for-telraam__summary-item qndrs-traffic-display-for-telraam__summary-item--cars">
                        <dt><?php esc_html_e('Cars', 'qndrs-traffic-display-for-telraam'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['cars'])); ?></dd>
                    </div>
                    <div class="qndrs-traffic-display-for-telraam__summary-item qndrs-traffic-display-for-telraam__summary-item--heavy-vehicles">
                        <dt><?php esc_html_e('Heavy vehicles', 'qndrs-traffic-display-for-telraam'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['heavy_vehicles'])); ?></dd>
                    </div>
                    <div class="qndrs-traffic-display-for-telraam__summary-item qndrs-traffic-display-for-telraam__summary-item--night">
                        <dt><?php esc_html_e('Night traffic', 'qndrs-traffic-display-for-telraam'); ?></dt>
                        <dd><?php echo esc_html(self::format_count($summary['night'])); ?></dd>
                    </div>
                    <div class="qndrs-traffic-display-for-telraam__summary-item qndrs-traffic-display-for-telraam__summary-item--uptime">
                        <dt><?php esc_html_e('Uptime', 'qndrs-traffic-display-for-telraam'); ?></dt>
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
                __('Segment %s, last day.', 'qndrs-traffic-display-for-telraam'),
                $segment_id
            );
        }

        return sprintf(
            /* translators: 1: segment ID, 2: number of days. */
            __('Segment %1$s, last %2$d days.', 'qndrs-traffic-display-for-telraam'),
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
                __('Hourly traffic rows for segment %s, last day.', 'qndrs-traffic-display-for-telraam'),
                $segment_id
            );
        } else {
            $period = sprintf(
                /* translators: 1: segment ID, 2: number of days. */
                __('Hourly traffic rows for segment %1$s, last %2$d days.', 'qndrs-traffic-display-for-telraam'),
                $segment_id,
                $days
            );
        }

        if ($visible_rows >= $total_rows) {
            return $period;
        }

        return sprintf(
            /* translators: 1: table caption, 2: number of visible rows, 3: total number of rows. */
            __('%1$s Showing %2$d of %3$d rows.', 'qndrs-traffic-display-for-telraam'),
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
     *     night: int,
     *     uptime: float|null
     * }> $rows Normalized traffic rows.
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of requested days.
     * @param int|null $rows_limit Maximum number of table rows, or null for all rows.
     */
    private static function render_table(array $rows, string $segment_id, int $days, ?int $rows_limit): string
    {
        if ([] === $rows) {
            return '<p class="qndrs-traffic-display-for-telraam__empty">' . esc_html__('No traffic rows were returned by the Telraam API.', 'qndrs-traffic-display-for-telraam') . '</p>';
        }

        usort(
            $rows,
            static function (array $first, array $second): int {
                $first_timestamp = strtotime($first['time']) ?: 0;
                $second_timestamp = strtotime($second['time']) ?: 0;

                return $second_timestamp <=> $first_timestamp;
            }
        );

        $visible_rows = null === $rows_limit ? $rows : array_slice($rows, 0, $rows_limit);
        $caption = self::format_table_caption($segment_id, $days, count($visible_rows), count($rows));

        ob_start();
        ?>
        <p class="qndrs-traffic-display-for-telraam__table-caption"><?php echo esc_html($caption); ?></p>
        <div class="qndrs-traffic-display-for-telraam__table-wrapper">
            <table class="qndrs-traffic-display-for-telraam__table">
                <caption><?php echo esc_html($caption); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e('Time', 'qndrs-traffic-display-for-telraam'); ?></th>
                        <th scope="col"><?php esc_html_e('Pedestrians', 'qndrs-traffic-display-for-telraam'); ?></th>
                        <th scope="col"><?php esc_html_e('Two-wheelers', 'qndrs-traffic-display-for-telraam'); ?></th>
                        <th scope="col"><?php esc_html_e('Cars', 'qndrs-traffic-display-for-telraam'); ?></th>
                        <th scope="col"><?php esc_html_e('Heavy vehicles', 'qndrs-traffic-display-for-telraam'); ?></th>
                        <th scope="col"><?php esc_html_e('Night', 'qndrs-traffic-display-for-telraam'); ?></th>
                        <th scope="col"><?php esc_html_e('Uptime', 'qndrs-traffic-display-for-telraam'); ?></th>
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
                            <td><?php echo esc_html(self::format_count($row['night'])); ?></td>
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
            return esc_html__('Unknown', 'qndrs-traffic-display-for-telraam');
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
            '<div class="qndrs-traffic-display-for-telraam qndrs-traffic-display-for-telraam--error" role="alert" aria-live="polite"><p>%s</p></div>',
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
            return __('Unknown', 'qndrs-traffic-display-for-telraam');
        }

        if ($uptime <= 1.0) {
            return sprintf(
                /* translators: %s: uptime percentage. */
                __('%s%%', 'qndrs-traffic-display-for-telraam'),
                number_format_i18n($uptime * 100, 1)
            );
        }

        return number_format_i18n($uptime, 1);
    }
}
