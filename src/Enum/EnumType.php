<?php

namespace App\Enum;

interface EnumType
{
    public static function isValid($value): bool;

    public static function getAvailableTypes(): array;

    public static function getAvailableTypesToString(): string;
}
