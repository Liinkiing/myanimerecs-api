<?php


namespace App\Enum;


final class AnimeListItemWatchingStatus implements EnumType
{
    public const CURRENTLY_WATCHING = 1;
    public const COMPLETED = 2;
    public const ON_HOLD = 3;
    public const DROPPED = 4;
    public const PLAN_TO_WATCH = 6;

    public static function isValid($value): bool
    {
        return \in_array($value, self::getAvailableTypes(), true);
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::CURRENTLY_WATCHING,
            self::COMPLETED,
            self::ON_HOLD,
            self::DROPPED,
            self::PLAN_TO_WATCH
        ];
    }

    public static function getAvailableTypesToString(): string
    {
        return implode(' | ', self::getAvailableTypes());
    }
}
