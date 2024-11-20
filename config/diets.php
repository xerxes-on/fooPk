<?php

// diets keys from Formular field "Was ist Dir an Deinem Plan besonders wichtig?"
// keys = keys form formular
// values = array of diets which included in diets
// uses in app\Helpers\Calculation.php, uses by config( 'diets' )
return [
//    'gluten_free'=>[21], // disabled 20190716
//    'lactose_free'=>[22], // disabled 20190716
    'aip'       => [14],            // created 20190716
    'ketogenic' => [],
// if checked - if checked - user need to be assigned KH 30         app\Helpers\Calculation.php::calcUserNutrients
    'low_carb' => [],
// if checked - if checked - user need to be assigned KH 50         app\Helpers\Calculation.php::calcUserNutrients
    'moderate_carb' => [],
// created 20190716 if checked - user need to be assigned KH 100    app\Helpers\Calculation.php::calcUserNutrients
    'paleo' => [18],
// probably deprecated
    'pescetarisch' => [19],   // created 20190716
    'pascetarian'  => [19],   // created 20230103
    'pescetarian'  => [19],   // created 20230103
    'vegan'        => [16],          //20200729 Added
    'vegetarian'   => [17],
    'custom_KH'    => [
        'ketogenic'     => 30,          //app\Helpers\Calculation.php::calcUserNutrients
        'low_carb'      => 50,           //app\Helpers\Calculation.php::calcUserNutrients
        'moderate_carb' => 100,         //app\Helpers\Calculation.php::calcUserNutrients
    ]
];
