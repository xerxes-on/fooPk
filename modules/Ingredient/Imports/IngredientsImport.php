<?php

namespace Modules\Ingredient\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Ingredient\Models\Ingredient;

/**@deprecated */
final class IngredientsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (is_null($row['lebensmittel'])) {
            return null;
        }

        return new Ingredient(
            [
                'de' => [
                    'name' => $row['lebensmittel'],
                ],
                'en' => [
                    'name' => $row['lebensmittel'],
                ],
                'category_id'     => 1,
                'proteins'        => (float)(is_null($row['proteinmenge_gramm100g']) ? 0 : $row['proteinmenge_gramm100g']),
                'fats'            => (float)(is_null($row['fettmenge_gramm100g']) ? 0 : $row['fettmenge_gramm100g']),
                'carbohydrates'   => (float)(is_null($row['kohlenhydratmenge_gramm100g']) ? 0 : $row['kohlenhydratmenge_gramm100g']),
                'calories'        => (float)(is_null($row['kilokalorien_100g']) ? 0 : $row['kilokalorien_100g']),
                'unit_id'         => 4,
                'image_file_name' => null,
            ]
        );
    }

    public function headingRow(): int
    {
        return 10;
    }
}
