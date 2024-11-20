<?php

namespace App\View\Components;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Component to render Google fonts
 *
 * @package App\View\Components
 */
final class GoogleFonts extends Component
{
    /**
     * Create a new component instance.
     *
     * @param array $fonts Google fonts array variations
     */
    public function __construct(private readonly array $fonts)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Factory
    {
        return view('components.google-fonts', ['url' => $this->prepareFontsUrl()]);
    }

    /**
     * Generate Google fonts url. Return empty string if some params are wrong.
     */
    private function prepareFontsUrl(): string
    {
        $fontsString = [];
        foreach ($this->fonts as $font) {
            if (!is_string($font['family']) || !is_array($font['wght'])) {
                continue;
            }
            $fontsString[] = sprintf(
                'family=%s:wght@%s',
                str_replace([' ', '_'], '+', $font['family']),
                implode(';', $font['wght'])
            );
        }

        return empty($fontsString) ? '' : 'https://fonts.googleapis.com/css2?' . implode('&', $fontsString) . '&display=swap';
    }
}
