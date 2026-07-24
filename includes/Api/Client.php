<?php
/**
 * Telraam API client.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht\Api;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Handles requests to the Telraam API.
 */
final class Client
{
    private const TRAFFIC_REPORT_ENDPOINT = 'https://telraam-api.net/v1/reports/traffic';
    private const REQUEST_TIMEOUT = 15;
    private const MAX_DAYS = 90;

    /**
     * Telraam API token.
     */
    private string $api_token;

    /**
     * @param string $api_token Telraam API token.
     */
    public function __construct(string $api_token)
    {
        $this->api_token = trim($api_token);
    }

    /**
     * Fetch traffic report data for a segment.
     *
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of days to request.
     * @return array<string, mixed>|\WP_Error
     */
    public function get_traffic_report(string $segment_id, int $days): array|\WP_Error
    {
        $segment_id = preg_replace('/[^0-9]/', '', $segment_id) ?? '';
        $days = max(1, min(self::MAX_DAYS, $days));

        if ('' === $this->api_token) {
            return new \WP_Error(
                'qndrs_telraam_inzicht_missing_api_token',
                __('The Telraam API token is missing.', 'qndrs-telraam-inzicht')
            );
        }

        if ('' === $segment_id) {
            return new \WP_Error(
                'qndrs_telraam_inzicht_missing_segment_id',
                __('The Telraam segment ID is missing.', 'qndrs-telraam-inzicht')
            );
        }

        $response = wp_remote_post(
            self::TRAFFIC_REPORT_ENDPOINT,
            [
                'timeout' => self::REQUEST_TIMEOUT,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Api-Key' => $this->api_token,
                ],
                'body' => wp_json_encode($this->build_traffic_report_payload($segment_id, $days)),
            ]
        );

        if (is_wp_error($response)) {
            return new \WP_Error(
                'qndrs_telraam_inzicht_request_failed',
                __('The Telraam API request failed.', 'qndrs-telraam-inzicht'),
                [
                    'reason' => $response->get_error_message(),
                ]
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            return new \WP_Error(
                'qndrs_telraam_inzicht_unexpected_status',
                sprintf(
                    /* translators: %d: HTTP response status code. */
                    __('The Telraam API returned an unexpected HTTP status: %d.', 'qndrs-telraam-inzicht'),
                    $status_code
                ),
                [
                    'status_code' => $status_code,
                ]
            );
        }

        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            return new \WP_Error(
                'qndrs_telraam_inzicht_invalid_json',
                __('The Telraam API returned invalid JSON.', 'qndrs-telraam-inzicht')
            );
        }

        return $decoded;
    }

    /**
     * Build the traffic report request payload.
     *
     * @param string $segment_id Telraam segment ID.
     * @param int    $days Number of days.
     * @return array{
     *     id: string,
     *     time_start: string,
     *     time_end: string,
     *     level: string,
     *     format: string
     * }
     */
    private function build_traffic_report_payload(string $segment_id, int $days): array
    {
        $end = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $start = $end->modify(sprintf('-%d days', $days));

        return [
            'id' => $segment_id,
            'time_start' => $start->format('Y-m-d H:i:s\Z'),
            'time_end' => $end->format('Y-m-d H:i:s\Z'),
            'level' => 'segments',
            'format' => 'per-hour',
        ];
    }
}
