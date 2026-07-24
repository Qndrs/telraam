<?php
/**
 * Cached Telraam traffic report access.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht\Api;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Retrieves traffic reports with WordPress transient caching.
 */
final class TrafficReportRepository
{
    private const CACHE_KEY_PREFIX = 'qndrs_telraam_inzicht_traffic_';

    private Client $client;
    private int $cache_duration_minutes;

    /**
     * @param Client $client Telraam API client.
     * @param int    $cache_duration_minutes Cache duration in minutes.
     */
    public function __construct(Client $client, int $cache_duration_minutes)
    {
        $this->client = $client;
        $this->cache_duration_minutes = max(5, min(1440, $cache_duration_minutes));
    }

    /**
     * Get traffic report data from cache or API.
     *
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of days.
     * @param bool   $force_refresh Whether to bypass cache.
     * @return array<string, mixed>|\WP_Error
     */
    public function get_traffic_report(string $segment_id, int $days, bool $force_refresh = false): array|\WP_Error
    {
        $segment_id = preg_replace('/[^0-9]/', '', $segment_id) ?? '';
        $days = max(1, min(90, $days));
        $cache_key = self::build_cache_key($segment_id, $days);

        if (! $force_refresh) {
            $cached = get_transient($cache_key);

            if (is_array($cached)) {
                return $cached;
            }
        }

        $response = $this->client->get_traffic_report($segment_id, $days);

        if (is_wp_error($response)) {
            return $response;
        }

        set_transient($cache_key, $response, $this->cache_duration_minutes * MINUTE_IN_SECONDS);

        return $response;
    }

    /**
     * Delete cached traffic report data for a specific segment and period.
     *
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of days.
     */
    public static function delete_cache(string $segment_id, int $days): void
    {
        $segment_id = preg_replace('/[^0-9]/', '', $segment_id) ?? '';
        $days = max(1, min(90, $days));

        delete_transient(self::build_cache_key($segment_id, $days));
    }

    /**
     * Build a stable transient key.
     *
     * WordPress transient names are limited in length, so use a short hash for
     * variable request arguments.
     *
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of days.
     */
    private static function build_cache_key(string $segment_id, int $days): string
    {
        return self::CACHE_KEY_PREFIX . md5(
            wp_json_encode(
                [
                    'segment_id' => $segment_id,
                    'days' => $days,
                    'level' => 'segments',
                    'format' => 'per-hour',
                ]
            ) ?: ''
        );
    }
}
