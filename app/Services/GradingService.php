<?php

namespace App\Services;

class GradingService
{
    /**
     * The institutional passing grade threshold.
     * This is a school-wide constant — locked at 75%.
     */
    public const PASSING_GRADE = 75.0;

    /**
     * Grade Equivalency Table (GPA).
     * This is the school's global standard, accessible to all portals.
     *
     * @return array<int, array{min: float, max: float, gpa: string}>
     */
    public static function gradeEquivalencyTable(): array
    {
        return [
            ['min' => 99, 'max' => 100, 'gpa' => '1.00'],
            ['min' => 96, 'max' => 98,  'gpa' => '1.25'],
            ['min' => 93, 'max' => 95,  'gpa' => '1.50'],
            ['min' => 90, 'max' => 92,  'gpa' => '1.75'],
            ['min' => 87, 'max' => 89,  'gpa' => '2.00'],
            ['min' => 84, 'max' => 86,  'gpa' => '2.25'],
            ['min' => 81, 'max' => 83,  'gpa' => '2.50'],
            ['min' => 78, 'max' => 80,  'gpa' => '2.75'],
            ['min' => 75, 'max' => 77,  'gpa' => '3.00'],
        ];
    }

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

        // Use the centralised equivalency table
        foreach (self::gradeEquivalencyTable() as $row) {
            if ($p >= $row['min'] && $p <= $row['max']) {
                return $row['gpa'];
            }
        }

        // Below passing but above 50 — still a numeric fail
        if ($p < self::PASSING_GRADE) {
            return 'Dropped';
        }

        // Fallback
        return 'Dropped';
    }

    /**
     * Determine if a percentage grade meets the institutional passing standard.
     * Uses the hardcoded 75% threshold.
     */
    public function isPassing(?float $percent): bool
    {
        if ($percent === null) {
            return false;
        }

        return $percent >= self::PASSING_GRADE;
    }

    /**
     * Get human-readable remarks based on the passing grade threshold.
     */
    public function remarks(?float $percent): string
    {
        return $this->isPassing($percent) ? 'Pass' : 'Fail';
    }
}
