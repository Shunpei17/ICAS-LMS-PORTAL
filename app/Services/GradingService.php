<?php

namespace App\Services;

class GradingService
{
    /**
     * Map percentage ranges to GPA values according to institutional table.
     * Returns null for dropped/invalid ranges.
     */
    public function toGpa(?float $percent): ?string
    {
        if ($percent === null) {
            return null;
        }

        $p = (float) $percent;

        // Dropped / unofficial ranges
        if ($p <= 50.0) {
            return 'Dropped';
        }

        // Mapping provided by requirement (ranges inclusive as sensible)
        if ($p >= 99 && $p <= 100) {
            return '1.00';
        }
        if ($p >= 96 && $p <= 98) {
            return '1.25';
        }
        if ($p >= 93 && $p <= 95) {
            return '1.50';
        }
        if ($p >= 90 && $p <= 92) {
            return '1.75';
        }
        if ($p >= 87 && $p <= 89) {
            return '2.00';
        }
        if ($p >= 84 && $p <= 86) {
            return '2.25';
        }
        if ($p >= 81 && $p <= 83) {
            return '2.50';
        }
        if ($p >= 78 && $p <= 80) {
            return '2.75';
        }
        if ($p >= 75 && $p <= 77) {
            return '3.00';
        }

        // Fallback
        return 'Dropped';
    }

    public function isPassing(?float $percent): bool
    {
        if ($percent === null) {
            return false;
        }

        return $percent >= 75.0;
    }
}
