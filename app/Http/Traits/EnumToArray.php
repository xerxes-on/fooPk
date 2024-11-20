<?php

namespace App\Http\Traits;

trait EnumToArray
{
    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function namesLower(): array
    {
        return array_map('strtolower', self::names());
    }

    public static function forSelect(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_column(self::cases(), 'name')
        );
    }
}
