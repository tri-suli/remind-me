<?php

namespace App\Enums;

enum Gender: int
{
    case MALE = 1;
    case FEMALE = 0;

    /**
     * Get the gender description
     *
     * @return string
     */
    public function title(): string
    {
        return match ($this) {
            self::MALE   => 'Male',
            self::FEMALE => 'Female'
        };
    }

    /**
     * Get all enum values
     *
     * @return array
     */
    public static function values(): array
    {
        return [self::MALE->value, self::FEMALE->value];
    }
}
