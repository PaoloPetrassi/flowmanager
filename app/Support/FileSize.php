<?php

namespace App\Support;

final class FileSize
{
    /**
     * Format a byte count without requiring PHP's intl extension.
     */
    public static function format(int|float|null $bytes, int $precision = 2): string
    {
        if ($bytes === null || ! is_finite((float) $bytes)) {
            return '—';
        }

        $value = max(0, (float) $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        $decimals = $unitIndex === 0 ? 0 : max(0, $precision);
        $formatted = number_format($value, $decimals, '.', '');

        if ($decimals > 0) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted.' '.$units[$unitIndex];
    }
}
