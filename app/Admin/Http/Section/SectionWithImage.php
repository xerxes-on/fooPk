<?php

declare(strict_types=1);

namespace App\Admin\Http\Section;

use SleepingOwl\Admin\Model\SectionModelConfiguration;

/**
 * Base class SectionWithImage
 *
 * @package App\Http\Section
 */
abstract class SectionWithImage extends SectionModelConfiguration
{
    /**
     * Default image template
     */
    private string $imageTemplate = '<a href="%s" data-toggle="lightbox"><img class="thumbnail" src="%s" alt="" width="%dpx"></a>';

    /**
     * Retrieve formatted image with link
     */
    public function getImageWithUrl(string $url, string $src, int $width = 80): string
    {
        return sprintf($this->imageTemplate, $url, $src, $width);
    }
}
