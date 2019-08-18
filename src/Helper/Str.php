<?php


namespace App\Helper;


final class Str
{
    public static function startsWith(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }
}
