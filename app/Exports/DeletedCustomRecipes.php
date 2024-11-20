<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export deleted custom recipes.
 *
 * @package App\Exports
 */
class DeletedCustomRecipes implements FromView, ShouldAutoSize
{
    use Exportable;

    /**
     * @param Collection<int,\App\Models\CustomRecipe> $collection
     */
    public function __construct(public readonly Collection $collection)
    {
    }

    public function view(): View
    {
        return view('exports.deleted-custom-recipes', ['data' => $this->collection]);
    }
}
