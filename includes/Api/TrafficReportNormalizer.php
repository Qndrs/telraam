<?php
/**
 * Normalizes Telraam traffic report responses.
 *
 * @package Qndrs_Telraam_Inzicht
 */

declare(strict_types=1);

namespace Qndrs\TelraamInzicht\Api;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Converts raw Telraam API responses into stable rows and summary totals.
 */
final class TrafficReportNormalizer
{
    /**
     * Normalize a Telraam traffic report response.
     *
     * @param array<string, mixed> $report Raw Telraam API response.
     * @return array{
     *     rows: array<int, array{
     *         time: string,
     *         pedestrians: int,
     *         two_wheelers: int,
     *         cars: int,
     *         heavy_vehicles: int,
     *         uptime: float|null
     *     }>,
     *     summary: array{
     *         pedestrians: int,
     *         two_wheelers: int,
     *         cars: int,
     *         heavy_vehicles: int,
     *         average_uptime: float|null
     *     }
     * }
     */
    public function normalize(array $report): array
    {
        $rows = array_map(
            [self::class, 'normalize_row'],
            self::extract_rows($report)
        );

        return [
            'rows' => $rows,
            'summary' => self::summarize_rows($rows),
        ];
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
     * Normalize a single traffic row.
     *
     * @param array<string, mixed> $row Raw row.
     * @return array{
     *     time: string,
     *     pedestrians: int,
     *     two_wheelers: int,
     *     cars: int,
     *     heavy_vehicles: int,
     *     uptime: float|null
     * }
     */
    private static function normalize_row(array $row): array
    {
        return [
            'time' => self::read_string($row, ['date', 'datetime', 'time', 'time_start']),
            'pedestrians' => self::read_int($row, ['pedestrian', 'pedestrians']),
            'two_wheelers' => self::read_int($row, ['bike', 'bikes', 'bicycle', 'two_wheelers']),
            'cars' => self::read_int($row, ['car', 'cars']),
            'heavy_vehicles' => self::read_int($row, ['heavy', 'heavy_vehicle', 'heavy_vehicles', 'truck', 'trucks']),
            'uptime' => self::read_float($row, ['uptime']),
        ];
    }

    /**
     * Summarize normalized traffic rows.
     *
     * @param array<int, array{
     *     time: string,
     *     pedestrians: int,
     *     two_wheelers: int,
     *     cars: int,
     *     heavy_vehicles: int,
     *     uptime: float|null
     * }> $rows Normalized rows.
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
            $summary['pedestrians'] += $row['pedestrians'];
            $summary['two_wheelers'] += $row['two_wheelers'];
            $summary['cars'] += $row['cars'];
            $summary['heavy_vehicles'] += $row['heavy_vehicles'];

            if (null !== $row['uptime']) {
                $uptime_sum += $row['uptime'];
                ++$uptime_count;
            }
        }

        if ($uptime_count > 0) {
            $summary['average_uptime'] = $uptime_sum / $uptime_count;
        }

        return $summary;
    }

    /**
     * Read an integer value from the first matching row key.
     *
     * @param array<string, mixed> $row Raw row.
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
     * @param array<string, mixed> $row Raw row.
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
     * @param array<string, mixed> $row Raw row.
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
}
