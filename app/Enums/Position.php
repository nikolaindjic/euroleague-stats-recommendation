<?php

namespace App\Enums;

class Position
{
    public const GUARD = 'G';
    public const FORWARD = 'F';
    public const CENTER = 'C';

    public const GUARD_LABEL = 'Guard';
    public const FORWARD_LABEL = 'Forward';
    public const CENTER_LABEL = 'Center';

    /**
     * Get all position codes
     */
    public static function all(): array
    {
        return [
            self::GUARD,
            self::FORWARD,
            self::CENTER,
        ];
    }

    /**
     * Get all position labels
     */
    public static function labels(): array
    {
        return [
            self::GUARD => self::GUARD_LABEL,
            self::FORWARD => self::FORWARD_LABEL,
            self::CENTER => self::CENTER_LABEL,
        ];
    }

    /**
     * Get position label from code
     */
    public static function label(string $code): string
    {
        return self::labels()[$code] ?? $code;
    }

    /**
     * Get position code from label
     */
    public static function code(string $label): ?string
    {
        $flipped = array_flip(self::labels());
        return $flipped[$label] ?? null;
    }

    /**
     * Check if a position string matches a given position
     */
    public static function matches(string $positionString, string $position): bool
    {
        $positionString = strtoupper(trim($positionString));

        // Direct match
        if ($positionString === $position) {
            return true;
        }

        // Label match
        $label = self::label($position);
        if (stripos($positionString, $label) !== false) {
            return true;
        }

        // Code match
        if (stripos($positionString, $position) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get positions for a category (for filtering)
     */
    public static function getPositionsForCategory(string $category): array
    {
        return match($category) {
            self::GUARD_LABEL => [self::GUARD, self::GUARD_LABEL],
            self::FORWARD_LABEL => [self::FORWARD, self::FORWARD_LABEL],
            self::CENTER_LABEL => [self::CENTER, self::CENTER_LABEL],
            default => [self::GUARD, self::GUARD_LABEL],
        };
    }
}

