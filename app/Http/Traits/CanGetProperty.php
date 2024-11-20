<?php

namespace App\Http\Traits;

trait CanGetProperty
{
    /**
     * Get object property
     */
    public function getProperty(string $property)
    {
        return $this->$property ?? null;
    }
}
