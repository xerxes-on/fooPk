<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Light representation of a custom (created from common one) recipe.
 */
abstract class ResourceWithDynamicImageSize extends JsonResource
{
    public function __construct(mixed $resource, protected string $imageSize = 'small')
    {
        parent::__construct($resource);
    }
}
