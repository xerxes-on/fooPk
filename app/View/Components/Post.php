<?php

namespace App\View\Components;

use App\Models\Post as PostModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Single post component.
 *
 * @package App\View\Components
 */
final class Post extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public PostModel $post)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.post');
    }
}
