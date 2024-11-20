<?php

namespace Modules\Ingredient\View\Components;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Component to render Ingredient hint tooltip.
 *
 * @package Modules\Ingredient\Components
 */
final class IngredientTipComponent extends Component
{
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Factory
    {
        return view('ingredient::components.ingredient-tip', ['data' => $this->prepareData()]);
    }

    /**
     * Prepare tooltip content data for rendering.
     */
    private function prepareData(): array
    {
        if (empty($this->data)) {
            return [];
        }

        $content = empty($this->data['content']) ? '' : '<p>' . $this->escapeChars($this->data['content']) . '</p>';

        $link = !empty($this->data['link_url']) && !empty($this->data['link_text']) ?
            sprintf(
                "<a href='%s' target='_blank' rel='noopener nofollow noreferrer'>%s</a>",
                $this->data['link_url'],
                $this->escapeChars($this->data['link_text'])
            ) :
            '';
        if (empty($content) && empty($link)) {
            return [];
        }
        return [
            'title'   => $this->escapeChars($this->data['title']),
            'content' => $content . $link,
        ];
    }

    private function escapeChars(string $str): string
    {
        return htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED);
    }
}
