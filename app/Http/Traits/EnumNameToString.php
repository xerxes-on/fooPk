<?php

declare(strict_types=1);

namespace App\Http\Traits;

/**
 * Alter enum name.
 *
 * @package App\Http\Traits
 */
trait EnumNameToString
{
    public function lowerName(): string
    {
        return strtolower($this->name);
    }

    public function ucName(): string
    {
        return ucfirst($this->lowerName());
    }
}
