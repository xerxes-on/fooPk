<?php

namespace App\Helpers;

use App\Enums\LanguagesEnum;
use App\Enums\Questionnaire\Options\LifeStyleQuestionOptionsEnum;
use App\Enums\Questionnaire\Options\MealPerDayQuestionOptionsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Enums\Recipe\RecipeStatusEnum;
use App\Http\Traits\Queue\HandleLastStartedJob;
use App\Listeners\ClearUserRecipeCache;
use App\Models\CustomRecipe;
use App\Models\Ingestion;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeVariableIngredient;
use App\Models\User;
use App\Models\UserRecipe;
use App\Models\UserRecipeCalculated;
use App\Services\Users\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\{DB};
use Modules\Ingredient\Enums\IngredientCategoryEnum;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Ingredient\Models\IngredientUnit;
use Modules\Ingredient\Services\IngredientConversionService;

/**
 * Class Calculation
 * @package App\Helpers
 */
class Calculation
{
    use HandleLastStartedJob;

    // uses in self::_addRandomRecipe2user, $options['recipes_tag'],  $options['recipes_tag_selection']
    //$options['recipe_tags_prioritized'] = [
    //            // 0 level tag by answers in the formular
    //            // 1 level first time distribution if no tag from formular
    //            // 2+ any other tag
    //        ];
    public const RECIPE_DISTRIBUTION_FROM_TAG_TYPE_PREFERABLE = 'PREFERABLE';
    public const RECIPE_DISTRIBUTION_FROM_TAG_TYPE_STRICT = 'STRICT';
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    //TODO:Should be put in .env
    public static $ingradient_units;
    public static $cache;
    // private static $api_domain = 'https://foodpunk.de';
    // private static $api_domain = 'http://www.laravelportal.com';
    private static $api_token = 'gmHaSzNGr7YCLAJv';
    private static $api_domain = 'https://meinplan.foodpunk.de';


    /**
     * Uses for get questionare and predefined data
     *
     * @param $user_id
     * @return mixed
     */
    public static function prepareUserDataForCalculations($user_id)
    {
        $client_object = User::find($user_id);

        $client = $client_object->toArray();

        if ($client_object->isQuestionnaireExist()) {
            $latestQuestionnaireAnswers = $client_object->latestQuestionnaireFullAnswers;
        }

        $client['questionnaire'] = $latestQuestionnaireAnswers ?? null;

        $client['predefined_values'] = [
            'Kcal' => null,
            'KH' => null,
            'EW' => null,
            'F' => null,
            'ew_percents' => 20,
            'ingestion' => [
                'breakfast' => [
                    'percents' => 20,
                    'Kcal' => null,
                    'KH' => null,
                    'EW' => null,
                    'F' => null,
                ],
                'lunch' => [
                    'percents' => 40,
                    'Kcal' => null,
                    'KH' => null,
                    'EW' => null,
                    'F' => null,
                ],
                'dinner' => [
                    'percents' => 40,
                    'Kcal' => null,
                    'KH' => null,
                    'EW' => null,
                    'F' => null,
                ],
            ],
        ];

        // if this is init calculation on reset calculation
        // we will get KH from config( 'diets' ) by accepted diet key, priority same as in config

        if (!empty($latestQuestionnaireAnswers)) {
            if (!empty($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY])) {
                switch ($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY]) {
                    case MealPerDayQuestionOptionsEnum::STANDARD->value:
                        $client['predefined_values']['ingestion'] = [
                            'breakfast' => [
                                'percents' => 20,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'lunch' => [
                                'percents' => 40,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'dinner' => [
                                'percents' => 40,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                        ];
                        break;
                    case MealPerDayQuestionOptionsEnum::BREAKFAST_LUNCH->value:
                        $client['predefined_values']['ingestion'] = [
                            'breakfast' => [
                                'percents' => 40,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'lunch' => [
                                'percents' => 60,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'dinner' => [
                                'percents' => 0,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                        ];
                        break;
                    case MealPerDayQuestionOptionsEnum::BREAKFAST_DINNER->value:
                        $client['predefined_values']['ingestion'] = [
                            'breakfast' => [
                                'percents' => 40,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'lunch' => [
                                'percents' => 0,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'dinner' => [
                                'percents' => 60,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                        ];
                        break;
                    case MealPerDayQuestionOptionsEnum::LUNCH_DINNER->value:
                        $client['predefined_values']['ingestion'] = [
                            'breakfast' => [
                                'percents' => 0,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'lunch' => [
                                'percents' => 50,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                            'dinner' => [
                                'percents' => 50,
                                'Kcal' => null,
                                'KH' => null,
                                'EW' => null,
                                'F' => null,
                            ],
                        ];
                        break;
                }
            }
            if (!empty($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::DIETS])) {
                $dietsConfig = config('diets');
                $customKH = false;
                foreach ($dietsConfig['custom_KH'] as $diet => $KH) {
                    if ((!empty($KH)) && (in_array($diet, $latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::DIETS]))) {
                        $customKH = $KH;
                    }
                }
                if ($customKH) {
                    $client['predefined_values']['KH'] = $customKH;
                }
            }
        }

        return $client;
    }

    public static function _api_calculateNutrients($data)
    {
        $predefined_values = $data['predefined_values'];
        // TODO::  @Nick review additional['F'] in formular with Barbara
        $result = array(
            'KH' => 50, //Kohlenhydrate  [Carbohydrates]
            'EW' => 0, //Eiweis [Protein]
            'F' => 0, //Fett [Fat]
            'Kcal' => 0,
            'ew_percents' => 20,  // default ew_percents
            'kh_percents' => 8,  // default kh_percents
            'f_percents' => 73,  // default f_percents
            // TODO:: review wth Barbara % of fat
            'additional' => array(),
            'notices' => '',
            'predefined_values' => $predefined_values,
        );

        $questionnaireData = $data['questionnaire'];

        $requiredKeys = [
            QuestionnaireQuestionSlugsEnum::WEIGHT,
            QuestionnaireQuestionSlugsEnum::HEIGHT,
            QuestionnaireQuestionSlugsEnum::BIRTHDATE,
            QuestionnaireQuestionSlugsEnum::GENDER,
        ];


        $existAllRequiredFields = true;
        foreach ($requiredKeys as $requiredKey) {
            if (!isset($questionnaireData[$requiredKey])) {
                $existAllRequiredFields = false;
            }
        }
        if (!$existAllRequiredFields) {
            return false;
        }

        $data['weight'] = floatval($questionnaireData[QuestionnaireQuestionSlugsEnum::WEIGHT]);

        $data['height'] = floatval($questionnaireData[QuestionnaireQuestionSlugsEnum::HEIGHT]);
        $data['age'] = Carbon::createFromFormat('d.m.Y', $questionnaireData[QuestionnaireQuestionSlugsEnum::BIRTHDATE])->age;

        // -------------------------------------------------------
        // *** step 1 ***
        if ($questionnaireData[QuestionnaireQuestionSlugsEnum::GENDER] == self::GENDER_MALE) {
            $result['additional']['S'] = 5;
        } elseif ($questionnaireData[QuestionnaireQuestionSlugsEnum::GENDER] == self::GENDER_FEMALE) {
            $result['additional']['S'] = -161;
        }

        $result['additional']['G'] = 10.0 * $data['weight'] + 6.25 * $data['height'] - 5.0 * $data['age'] + $result['additional']['S'];


        // -------------------------------------------------------
        // *** step2 ***
        // calc activity
        $result['additional']['F'] = 1;
        if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::LIFESTYLE])) {
            if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::LIFESTYLE] == LifeStyleQuestionOptionsEnum::MAINLY_LYING)) {
                $result['additional']['F'] = 1.2;
            }  //vorwiegend liegend, z. B. im Krankenhaus
            if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::LIFESTYLE] == LifeStyleQuestionOptionsEnum::MAINLY_SITTING)) {
                $result['additional']['F'] = 1.4;
            } //vorwiegend sitzend, z. B. Bürojob
            if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::LIFESTYLE] == LifeStyleQuestionOptionsEnum::SITTING_STANDING)) {
                //        $result['additional']['F'] = 1.6;
                // updated 20190516
                $result['additional']['F'] = 1.5;
            } //sitzend / stehend, z. B. Hausfrau/-mann, Krankenpflege, Ärztin/Arzt
            if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::LIFESTYLE] == LifeStyleQuestionOptionsEnum::STANDING_WAKING)) {
                //        $result['additional']['F'] = 1.8;
                // updated 20190516
                $result['additional']['F'] = 1.6;
            } //stehend / gehend, z. B. Verkäufer-/in
            if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::LIFESTYLE] == LifeStyleQuestionOptionsEnum::ACTIVE)) {
                $result['additional']['F'] = 2.4;
            } //sehr aktiv, z. B. auf Baustellen, Profistportler/-in
        }

        $result['additional']['A'] = $result['additional']['G'] * $result['additional']['F'];


        // -------------------------------------------------------
        // *** step3 ***
        $sportsKcals = 0;
        if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS])) {
            if (
                !empty($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["intensive"])
                &&
                isset($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["intensive"]['frequency'])
                &&
                isset($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["intensive"]['duration'])
            ) {
                // updated 20210921 2.5 to 3.7
                // updated 20211012 3.7 to 5.0
                // updated 20211108 5.0 to 6.2
                $sportsKcals += 6.2 * $data['weight'] * (intval(
                            $questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["intensive"]['frequency']
                        ) * intval(
                            $questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["intensive"]['duration']
                        )) / 60;
            }

            if (
                !empty($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["medium"])
                &&
                isset($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["medium"]['frequency'])
                &&
                isset($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["medium"]['duration'])
            ) {
                $sportsKcals += 4.1 * $data['weight'] * (intval(
                            $questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["medium"]['frequency']
                        ) * intval(
                            $questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["medium"]['duration']
                        )) / 60;
            }

            if (
                !empty($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["easy"])
                &&
                isset($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["easy"]['frequency'])
                &&
                isset($questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["easy"]['duration'])

            ) {
                // updated 20210921 6.2 to 5.0
                // updated 20211012 5.0 to 3.7
                // updated 20211108 3.7 to 2.5
                $sportsKcals += 2.5 * $data['weight'] * (intval(
                            $questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["easy"]['frequency']
                        ) * intval(
                            $questionnaireData[QuestionnaireQuestionSlugsEnum::SPORTS]["easy"]['duration']
                        )) / 60;
            }
        }


        $result['additional']['SportEnergy'] = $sportsKcals;
        $result['Kcal'] = $result['additional']['DailyEnergy'] = ($result['additional']['SportEnergy'] + 7 * $result['additional']['A']) / 7;


        // -------------------------------------------------------
        // *** step 4 ***
        $disease_rate = array(
            'diabetes_typ_1' => 1,
            'diabetes_typ_2' => 1,
            'prediabetes' => 1,
            'insulin_resistance' => 1,
            // updated 20190516
            //        'hashimoto'          => 0.9,
            'hashimoto' => 0.95,
            // updated 20190516
            //        'hypothyroidism'     => 0.9,  //Schilddrüsenunterfunktion
            'hypothyroidism' => 0.95,  //Schilddrüsenunterfunktion
            // updated 20190516
            //        'hyperthyroidism'    => 1.1, //Schilddrüsenüberfunktion
            'hyperthyroidism' => 1.05, //Schilddrüsenüberfunktion
            'adrenal_Fatigue' => 1,
            //??  review with Barbara           'Lipedema'    => 1,
            //??  review with Barbara            'gout'    => 1,
            //'others'=>1,
        );

        if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::DISEASES])) {
            foreach ($disease_rate as $disease => $coef) {
                if (in_array($disease, $questionnaireData[QuestionnaireQuestionSlugsEnum::DISEASES])) {
                    $result['Kcal'] *= $coef;
                }
            }

            if (isset($questionnaireData[QuestionnaireQuestionSlugsEnum::DISEASES]['other'])) {
                $result['notices'] .= 'client has disease: ' . (($questionnaireData[QuestionnaireQuestionSlugsEnum::DISEASES]['other']) ?? ' selected, but has not typed ') . '!' . PHP_EOL;
            }
        }

        // man and weight >120kg
        if ($questionnaireData[QuestionnaireQuestionSlugsEnum::GENDER] == self::GENDER_MALE && $data[QuestionnaireQuestionSlugsEnum::WEIGHT] > 120) {
            $result['Kcal'] *= 0.9;
        } // woman and weight >90kg
        elseif ($questionnaireData[QuestionnaireQuestionSlugsEnum::GENDER] == self::GENDER_FEMALE && $data[QuestionnaireQuestionSlugsEnum::WEIGHT] > 90) {
            $result['Kcal'] *= 0.9;
        }


        // -------------------------------------------------------
        //*** step 5 ***
        $target_rate = array(
            'lose_weight' => 0.8, //Abnehmen (Körperfett verlieren)
            'healthy_weight' => 0.97, //Gewicht halten und definierter werden,  more_defined->healthy_weight

            // updated 20190516
            //        'increase_fitness' => 1, //Gewicht halten und Fitness steigern (z.B. die Energie im Alltag)
            'improve_fitness' => 1.1, //Gewicht halten und Fitness steigern (z.B. die Energie im Alltag)

            // updated 20190516
            //        'increase'         => 1.4, //Zunehmen (z.B. bei Untergewicht)
            'gain_weight' => 1.2, //Zunehmen (z.B. bei Untergewicht), increase -> gain_weight

            // updated 20190516
            //        'build_muscle'     => 1.4, //Muskelaufbau (aktiv an Muskelgewicht zunehmen)
            'build_muscle' => 1.2, //Muskelaufbau (aktiv an Muskelgewicht zunehmen)

            // updated 20190516
            //        'health_problem' => 1, // problems with health
            'improve_health' => 1.1, // problems with health  health_problem->improve_health
        );
        if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::MAIN_GOAL])) {
            foreach ($target_rate as $target => $coef) {
                if ($questionnaireData[QuestionnaireQuestionSlugsEnum::MAIN_GOAL] == $target) {
                    $result['Kcal'] *= $coef;
                }
            }

            if (!empty($questionnaireData[QuestionnaireQuestionSlugsEnum::MAIN_GOAL]['improve_health'])) {
                $result['notices'] .= "client has health problems: " . $data[QuestionnaireQuestionSlugsEnum::MAIN_GOAL]['improve_health'] . "!" . PHP_EOL;
            }
        }


        if (!empty($predefined_values['ew_percents'])) {
            $result['ew_percents'] = floatval($predefined_values['ew_percents']);
        }
        //    if ( ! empty( $predefined_values['kh_percents'] ) ) $result['kh_percents'] = intval( $predefined_values['kh_percents'] );
        //    if ( ! empty( $predefined_values['f_percents'] ) ) $result['f_percents'] = intval( $predefined_values['f_percents'] );


        $result['additional']['calculated_Kcal'] = $result['Kcal'];
        if (!empty($predefined_values['Kcal'])) {
            $result['Kcal'] = floatval($predefined_values['Kcal']);
        }

        $ew_percents = $result['ew_percents'] / 100;


        // -------------------------------------------------------
        // calculation Protein

        $result['additional']['calculated_EW'] = $result['Kcal'] * $ew_percents / 4;

        //    if ( empty( $predefined_values['EW'] ) )
        $result['EW'] = $result['additional']['calculated_EW'];
        //    else $result['EW'] = intval( $predefined_values['EW'] );


        // -------------------------------------------------------
        // calculation Carbohydrates


        $result['additional']['calculated_KH'] = false;

        // all recipes became KH 50 ???
        // TODO:: ask Barbara
        /*
        $carbs_rate = array(
            '100_carbs' => 100,
            '50_carbs'  => 50,
            '30_carbs'  => 30,
        );

        if (!empty($data['how_many_carbs']) && is_array($data['how_many_carbs'])) {
            foreach ($carbs_rate as $carb => $rate) {
                if (!empty($data['how_many_carbs'][$carb])) {
                    $result['additional']['calculated_KH'] = $rate;
                }
            }

            if (empty($result['additional']['calculated_KH'])) {
                $result['notices'] .= "client KH selected as: Egal, das was am besten funktioniert!".PHP_EOL;
            }
        }

        if (!empty($result['additional']['calculated_KH'])) {
            $result['KH'] = floatval($result['additional']['calculated_KH']);
        } else {
            if (!empty($predefined_values['KH'])) {
                $result['KH'] = floatval($predefined_values['KH']);
            } else {
                $result['KH'] = 50;
            }
        }
        */
        if (!empty($predefined_values['KH'])) {
            $result['KH'] = floatval($predefined_values['KH']);
        } else {
            $result['KH'] = 50;
        }


        // -------------------------------------------------------
        // calculation Fat
        $result['F'] = $result['additional']['calculated_F'] = ($result['Kcal'] - ($result['KH'] * 4 + $result['EW'] * 4)) / 9;

        //    if ( !empty( $predefined_values['F'] ) ) $result['F'] = $predefined_values['F'];
        //    else $result['F'] = $result['additional']['calculated_F'];


        $result['kh_percents'] = (($result['KH'] * 4 / $result['Kcal']) * 100);
        $result['f_percents'] = (($result['F'] * 9 / $result['Kcal']) * 100);

        if (!empty($predefined_values['ingestion'])) {
            $result['ingestion'] = $predefined_values['ingestion'];
            foreach ($result['ingestion'] as $type => $type_values) {
                $result['ingestion'][$type]['Kcal'] = (!empty($type_values['Kcal'])) ? $type_values['Kcal'] : round(
                    $result['Kcal'] * $type_values['percents'] / 100
                );
                $result['ingestion'][$type]['KH'] = (!empty($type_values['KH'])) ? $type_values['KH'] : round(
                    $result['KH'] * $type_values['percents'] / 100,
                    1
                );
                $result['ingestion'][$type]['EW'] = (!empty($type_values['EW'])) ? $type_values['EW'] : round(
                    $result['EW'] * $type_values['percents'] / 100,
                    1
                );
                $result['ingestion'][$type]['F'] = (!empty($type_values['F'])) ? $type_values['F'] : round(
                    $result['F'] * $type_values['percents'] / 100,
                    1
                );
            }
        }


        foreach ($result as &$value) {
            if ((!is_array($value)) and (is_numeric($value))) {
                $value = round($value, 1);
            }
        }

        $result['additional']['calculated_Kcal'] = round($result['additional']['calculated_Kcal']);
        $result['Kcal'] = round($result['Kcal']);
        $result['KH'] = round($result['KH'], 1);
        $result['additional']['calculated_KH'] = round($result['additional']['calculated_KH'], 1);
        $result['EW'] = round($result['EW'], 1);
        $result['additional']['calculated_EW'] = round($result['additional']['calculated_EW'], 1);
        $result['F'] = round($result['F'], 1);
        $result['additional']['calculated_F'] = round($result['additional']['calculated_F'], 1);
        $result['additional']['DailyEnergy'] = round($result['additional']['DailyEnergy']);


        return $result;
    }

    public static function calcUserNutrients($user_id, $client_data = null)
    {
        if (empty($client_data)) {
            $client_data = self::prepareUserDataForCalculations($user_id);
        }
        return self::_api_calculateNutrients($client_data);
    }

    /**
     * @param int $user_id
     * @param int|null $recipe_id
     *           //        $fixed_ingredients = [
     * //            ['id'=>1,'amount'=>15.5],
     * //            ['id'=>2,'amount'=>17.5],
     * //        ];
     * @param array|null $variable_ingradients
     * @param string|null $ingestion
     *
     * @return array
     */
    public static function getUserRecipe(
        $user_id,
        $recipe_id = null,
        $custom_fixed_ingredients = null,
        $variable_ingradients = [],
        $ingestion = null,
        $allowZeroIngredients = true
    )
    {
        $nutrients = self::getUserDietData($user_id);

        if (!empty($recipe_id)) {
            if ($recipe = Recipe::find($recipe_id)) {
                $recipe = self::prepareRecipeForCalculation($recipe, $variable_ingradients);
            }
        } else {
            $recipe = self::prepareClearRecipeForCalculation($variable_ingradients)->toArray();
        }

        // TODO:: @NickMost to think about if not exists ingestion, like snack
        $fixed_ingredients_array = [];
        if ((!empty($nutrients['ingestion'][$ingestion])) && (!empty($recipe))) {
            $Kcal = $nutrients['ingestion'][$ingestion]['Kcal'];
            $KH = $nutrients['ingestion'][$ingestion]['KH'];
            $EW = $nutrients['ingestion'][$ingestion]['EW'];
            $F = $nutrients['ingestion'][$ingestion]['F'];


            //        if (empty(self::$ingradient_units))
            {
                self::$ingradient_units = $ingradient_units = IngredientUnit::get()->keyBy('id')->toArray();
            }
            $ingradient_units = self::$ingradient_units;


            if ((!empty($recipe_id)) && ($recipe_object = Recipe::find($recipe_id))) {
                if (!empty($recipe_object->ingredients)) {
                    $fixed_ingredients = $recipe_object->ingredients;
                }
                if (!empty($recipe_object->variableIngredients)) {
                    $variable_ingradients = $recipe_object->variableIngredients->toArray();
                }


                $fixed_ingredients_array = [];
                if ((!empty($fixed_ingredients)) && (count($fixed_ingredients))) {
                    foreach ($fixed_ingredients as $ingredient) {
                        if (isset(IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']])) {
                            $category = IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']];
                            $ingredient->type = $category['short'];
                        }
                        $ingredient->unit = $ingradient_units[$ingredient->unit_id];
                        $ingredient->unit_data = $ingradient_units[$ingredient->unit_id];

                        if (isset($ingredient->pivot->amount)) {
                            $ingredient->amount = $ingredient->pivot->amount;
                        } else {
                            $ingredient->amount = 0;
                        }
                    }//
                    $fixed_ingredients_array = $fixed_ingredients->toArray();
                }
            }

            if (!empty($custom_fixed_ingredients)) {
                $fixed_ingredients_array = [];
                foreach ($custom_fixed_ingredients as $ingredient_input_data) {
                    $fixed_ingredients_array[] = self::prepareInredient(
                        $ingredient_input_data['id'],
                        $ingredient_input_data['amount']
                    )->toArray();
                }
            }

            $variable_ingradients_array = [];
            if ((!empty($variable_ingradients)) && (count($variable_ingradients))) {
                foreach ($variable_ingradients as $ingredient) {
                    $variable_ingradients_array[] = self::prepareInredient($ingredient['id'])->toArray();
                }
            }

            $recipe = self::calculateIngredients(
                $fixed_ingredients_array,
                $variable_ingradients_array,
                $Kcal,
                $KH,
                $EW,
                $F,
                $allowZeroIngredients
            );
            $recipe['ingestion'] = $ingestion;
        }

        return $recipe;
    }

    public static function getUserDietData($user_id)
    {
        return User::find($user_id)->dietdata;
    }

    public static function prepareRecipeForCalculation($recipe, $ingradients_variable = [])
    {
        $recipe->ingradients = $recipe->ingredients()->get();

        $recipe = self::calculateRecipeDefaultNutrients($recipe);
        if (!empty($ingradients_variable)) {
            $recipe = self::attachVariableIngradientsWithUnits($recipe, $ingradients_variable);
        }

        return $recipe;
    }

    public static function calculateRecipeDefaultNutrients($recipe = null)
    {
        if (empty($recipe)) {
            $recipe = new Recipe();
        }

        // recalculation KCal, EW, KH, F
        $recipe_nutrients = [
            'KCal' => 0,
            'EW' => 0,
            'KH' => 0,
            'F' => 0,
        ];

        if ((!empty($recipe->ingradients))) {
            foreach ($recipe->ingradients as $ingradient) {
                if (isset($ingradient->amount)) {
                    $ingradient_attributes_multiplier = $ingradient->amount / $ingradient->unit->default_amount;
                } elseif (isset($ingradient->pivot->amount)) {
                    $ingradient_attributes_multiplier = $ingradient->pivot->amount / $ingradient->unit->default_amount;
                }

                $recipe_nutrients['KCal'] += $ingradient->calories * $ingradient_attributes_multiplier;
                $recipe_nutrients['EW'] += $ingradient->proteins * $ingradient_attributes_multiplier;
                $recipe_nutrients['KH'] += $ingradient->carbohydrates * $ingradient_attributes_multiplier;
                $recipe_nutrients['F'] += $ingradient->fats * $ingradient_attributes_multiplier;
            }

            $recipe->ingradients = $recipe->ingradients->toArray();
        }

        foreach ($recipe_nutrients as $k => $v) {
            $recipe[$k] = $v;
        }

        return $recipe;
    }

    public static function attachVariableIngradientsWithUnits($recipe, $ingradients_variable = [])
    {
        $ingradients_variable = (array)$ingradients_variable;


        //        if (empty(self::$ingradient_units))
        {
            self::$ingradient_units = $ingradient_units = IngredientUnit::get()->keyBy('id')->toArray();
        }
        $ingradient_units = self::$ingradient_units;

        if (empty($ingradients_variable)) {
            $ingradients_variable = $recipe->variableIngredients()->get()->toArray();
        }

        foreach ($ingradients_variable as $k => $ingradient) {
            $ingradients_variable[$k]['unit'] = $ingradient_units[$ingradient['unit_id']];
        }
        $recipe->ingradients_variable = $ingradients_variable;

        return $recipe;
    }

    public static function prepareClearRecipeForCalculation($ingradients = [])
    {
        $recipe = self::calculateRecipeDefaultNutrients();
        $recipe = self::attachVariableIngradientsWithUnits($recipe, $ingradients);

        return $recipe;
    }

    public static function prepareInredient($ingredient_id, $amount = 0)
    {
        //        if (empty(self::$ingradient_units))
        {
            self::$ingradient_units = $ingradient_units = IngredientUnit::get()->keyBy('id')->toArray();
        }
        $ingradient_units = self::$ingradient_units;


        $ingredient = Ingredient::find($ingredient_id);
        if (isset(IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']])) {
            $category = IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']];
            $ingredient->type = $category['short'];
        }

        if (!empty($ingredient->category->tree_information['main_category']) && isset(IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']])) {
            $ingredient->allow_replacement = true;
        } else {
            $ingredient->allow_replacement = false;
        }


        $ingredient->unit = $ingradient_units[$ingredient->unit_id];
        $ingredient->unit_data = $ingradient_units[$ingredient->unit_id];
        $ingredient->amount = $amount;

        return $ingredient;
    }

    public static function calculateIngredients(
        $fixed = [],
        $variable = [],
        $Kcal = 0,
        $KH = 0,
        $EW = 0,
        $F = 0,
        $allowZeroIngredients = true
    )
    {
        $recipe_calculation_data = [
            'ingradients' => $fixed,
            'ingradients_variable' => $variable,
        ];

        $recipe = self::calculateRecipe($recipe_calculation_data, $Kcal, $KH, $EW, $F, $allowZeroIngredients);
        return $recipe;
    }

    public static function _api_gaussj($a, $b)
    {
        $n = count($a);
        for ($j = 0; $j < $n; $j++) {
            $ipiv[$j] = 0;
        }
        for ($i = 0; $i < $n; $i++) {
            $big = 0;
            for ($j = 0; $j < $n; $j++) {
                if ($ipiv[$j] != 1) {
                    for ($k = 0; $k < $n; $k++) {
                        if ($ipiv[$k] == 0) {
                            if (abs($a[$j][$k]) >= $big) {
                                $big = abs($a[$j][$k]);
                                $irow = $j;
                                $icol = $k;
                            }
                        } else {
                            if ($ipiv[$k] > 1) {
                                return false;//"Матрица сингулярна";
                            }
                        }
                    }
                }
            }
            $ipiv[$icol] = $ipiv[$icol] + 1;
            if ($irow != $icol) {
                for ($l = 0; $l < $n; $l++) {
                    $dum = $a[$irow][$l];
                    $a[$irow][$l] = $a[$icol][$l];
                    $a[$icol][$l] = $dum;
                }
                $dum = $b[$irow];
                $b[$irow] = $b[$icol];
                $b[$icol] = $dum;
            }
            $indxr[$i] = $irow;
            $indxc[$i] = $icol;
            if ($a[$icol][$icol] == 0) {
                return false;
            }//"Матрица сингулярна";
            $pivinv = 1 / $a[$icol][$icol];
            $a[$icol][$icol] = 1;
            for ($l = 0; $l < $n; $l++) {
                $a[$icol][$l] = $a[$icol][$l] * $pivinv;
            }
            $b[$icol] = $b[$icol] * $pivinv;
            for ($ll = 0; $ll < $n; $ll++) {
                if ($ll != $icol) {
                    $dum = $a[$ll][$icol];
                    $a[$ll][$icol] = 0;
                    for ($l = 0; $l < $n; $l++) {
                        $a[$ll][$l] = $a[$ll][$l] - $a[$icol][$l] * $dum;
                    }
                    $b[$ll] = $b[$ll] - $b[$icol] * $dum;
                }
            }
        }
        for ($l = $n - 1; $l >= 0; $l--) {
            if ($indxr[$l] != $indxc[$l]) {
                for ($k = 1; $k < $n; $k++) {
                    $dum = $a[$k][$indxr[$l]];
                    $a[$k][$indxr[$l]] = $a[$k][$indxc[$l]];
                    $a[$k][$indxc[$l]] = $dum;
                }
            }
        }
        // $b - решение уравнения
        // $a - обратная матрица
        //        return array($b, $a);
        return $b;
    }

    public static function _api_calculateNutrientsByKhKcal($KH, $KCal)
    {
        $result = [];
        $ew_percents = 0.2;
        $result['KCal'] = $KCal;
        $result['KH'] = $KH;
        $result['EW'] = $KCal * $ew_percents / 4;
        $result['F'] = ($KCal - ($KH * 4 + $result['EW'] * 4)) / 9;
        return $result;
    }

    public static function _api_calculateRecipeIngradients(
        $recipe = [],
        $Kcal = 0,
        $KH = 0,
        $EW = 0,
        $F = 0,
        $allowZeroIngredients = true
    )
    {
        //    var_dump($recipe);
        $recipe['notices'] = '';
        $recipe['errors'] = false;

        $empty_ingradients_variable = false;

        if (!empty($recipe['ingradients_variable'])) {
            foreach ($recipe['ingradients_variable'] as $key => $ingradient) {
                $recipe['ingradients_variable'][$key]['amount'] = 0;
            }
        } else {
            // empty ingradients_variable
            $empty_ingradients_variable = true;
        }
        $recipe['real_KCal'] = $recipe['real_EW'] = $recipe['real_F'] = $recipe['real_KH'] = 0;
        if (!empty($recipe['ingradients'])) {
            foreach ($recipe['ingradients'] as $key => $ingradient) {
                if (isset($ingradient['pivot']['amount'])) {
                    $recipe['ingradients'][$key]['amount'] = self::_api_round_up($ingradient['pivot']['amount']);
                }
                //            $recipe['ingradients'][$key]['amount_raw'] = $recipe['ingradients'][$key]['amount'];
                //            if (!$allowZeroIngredients && $recipe['ingradients'][$key]['amount_raw']<1){
                //                $recipe['errors']  = true;
                //                $recipe['notices'] .= ' recipe has ingredient '.$key.' which has less 1g, amount='.$recipe['ingradients'][$key]['amount_raw'].' ,';
                //            }
                $recipe['ingradients'][$key]['amount'] = self::_api_round_up($recipe['ingradients'][$key]['amount']);
                if (isset($recipe['ingradients'][$key]['amount'])) {
                    $factor = $recipe['ingradients'][$key]['unit_data']['default_amount'];
                    $recipe['real_KCal'] += $ingradient['calories'] * $recipe['ingradients'][$key]['amount'] / $factor;
                    $recipe['real_EW'] += $ingradient['proteins'] * $recipe['ingradients'][$key]['amount'] / $factor;
                    $recipe['real_F'] += $ingradient['fats'] * $recipe['ingradients'][$key]['amount'] / $factor;
                    $recipe['real_KH'] += $ingradient['carbohydrates'] * $recipe['ingradients'][$key]['amount'] / $factor;
                }
            }
        }

        $recipe['real_KCal'] = round(floatval($recipe['real_KCal']), 2);
        $recipe['real_EW'] = round(floatval($recipe['real_EW']), 2);
        $recipe['real_F'] = round(floatval($recipe['real_F']), 2);
        $recipe['real_KH'] = round(floatval($recipe['real_KH']), 2);


        if (!empty($recipe['KCal'])) {
            $recipe['KCal'] = round($recipe['KCal'], 2);
        }
        if (!empty($recipe['EW'])) {
            $recipe['EW'] = round($recipe['EW'], 2);
        }
        if (!empty($recipe['F'])) {
            $recipe['F'] = round($recipe['F'], 2);
        }
        if (!empty($recipe['KH'])) {
            $recipe['KH'] = round($recipe['KH'], 2);
        }

        if (empty($recipe['KCal'])) {
            $recipe['KCal'] = round($recipe['real_KCal'], 2);
        }
        if (empty($recipe['EW'])) {
            $recipe['EW'] = round($recipe['real_EW'], 2);
        }
        if (empty($recipe['F'])) {
            $recipe['F'] = round($recipe['real_F'], 2);
        }
        if (empty($recipe['KH'])) {
            $recipe['KH'] = round($recipe['real_KH'], 2);
        }

        $recipe['calculated_KCal'] = $recipe['KCal'];

        $recipe['calculated_KH'] = $recipe['KH'];
        $recipe['calculated_EW'] = $recipe['EW'];
        $recipe['calculated_F'] = $recipe['F'];


        $recipe['need_more_nutrients'] = $need_nutrients = [
            'KH' => round($KH - $recipe['KH'], 2),//KH
            'EW' => round($EW - $recipe['EW'], 2),//EW
            'F' => round($F - $recipe['F'], 2),//F
        ];

        $recipe['need_nutrients'] = [
            'KH' => round($KH, 2),
            'EW' => round($EW, 2),
            'F' => round($F, 2),
        ];

        $recipe_has_more_nutrients = false;
        if (!$empty_ingradients_variable) {
            foreach ($need_nutrients as $k => $value) {
                if ($need_nutrients[$k] < 0) {
                    $recipe_has_more_nutrients = true;
                }
            }
        }


        if ($recipe_has_more_nutrients == false) {
            //        if ( ! empty( $recipe['ingradients_variable'] ) ) {


            $need_variable_ingradients = true;

            if (($need_nutrients['KH'] >= 0) && ($need_nutrients['KH'] < 2) && ($need_nutrients['EW'] >= 0) && ($need_nutrients['EW'] < 2) && ($need_nutrients['F'] >= 0) && ($need_nutrients['F'] < 2)) {
                $need_variable_ingradients = false;
            }


            if ((count($recipe['ingradients_variable']) > 3) && $need_variable_ingradients) {
                $recipe['notices'] .= ' more than 3 ingradients!, ';
                $recipe['errors'] = true;
            } else {
                foreach ($recipe['ingradients_variable'] as $k => $v) {
                    $recipe['ingradients_variable'][$k]['amount'] = 0;
                }

                if ($need_variable_ingradients) {
                    if (count($recipe['ingradients_variable']) == 3) {
                        $matrix = [
                            [
                                floatval($recipe['ingradients_variable'][0]['carbohydrates']),
                                floatval($recipe['ingradients_variable'][1]['carbohydrates']),
                                floatval($recipe['ingradients_variable'][2]['carbohydrates']),
                                $need_nutrients['KH']
                            ],
                            [
                                floatval($recipe['ingradients_variable'][0]['proteins']),
                                floatval($recipe['ingradients_variable'][1]['proteins']),
                                floatval($recipe['ingradients_variable'][2]['proteins']),
                                $need_nutrients['EW']
                            ],
                            [
                                floatval($recipe['ingradients_variable'][0]['fats']),
                                floatval($recipe['ingradients_variable'][1]['fats']),
                                floatval($recipe['ingradients_variable'][2]['fats']),
                                $need_nutrients['F']
                            ],
                        ];

                        //            var_dump($matrix); die;


                        if ($calculations = self::_api_solveEquation($matrix)) {
                            $recipe['ingradients_variable'][0]['amount'] = self::_api_round_up($calculations[0]);
                            $recipe['ingradients_variable'][1]['amount'] = self::_api_round_up($calculations[1]);
                            $recipe['ingradients_variable'][2]['amount'] = self::_api_round_up($calculations[2]);

                            ///// calculate by type of ingradients
                            $need_calc_by_type = false;
                            $zero_values = false;
                            foreach ($calculations as $item) {
                                if ($item < 0) {
                                    $need_calc_by_type = true;
                                } elseif ($item == 0) {
                                    $zero_values = true;
                                }
                            }
                            // disabled calculation by type
                            if ($need_calc_by_type) {
                                //                                foreach ( $recipe['ingradients_variable'] as $index => $ingradient ) {
                                //                                    $recipe['ingradients_variable'][ $index ]['amount'] = self::_api_getAmountIngradientNutriensByType( $need_nutrients, $ingradient );
                                //                                }
                                //                                $recipe['notices'] .= 'matrix failed, calculated by type' . PHP_EOL;
                                $recipe['ingradients_variable'][0]['amount'] = $recipe['ingradients_variable'][1]['amount'] = $recipe['ingradients_variable'][2]['amount'] = 0;
                                //                                $recipe['notices'] .= 'matrix failed, exists ingredient with - value' . PHP_EOL;
                                $recipe['notices'] .= 'matrix failed' . PHP_EOL;
                                if ($zero_values) {
                                    $recipe['notices'] .= 'zero ingredient exists' . PHP_EOL;
                                }
                                $recipe['errors'] = true;
                            }
                        } else {
                            $recipe['notices'] .= 'need to make recipe manually' . PHP_EOL;
                            $recipe['errors'] = true;
                        }
                        //            var_dump( $recipe['ingradients_variable'][0]['amount']);
                        //            var_dump($recipe);

                    } else {
                        // if variable ingredients less than 3

                        if (count($recipe['ingradients_variable']) == 2) {
                            if (($recipe['ingradients_variable'][0]['carbohydrates'] == 0) && ($recipe['ingradients_variable'][1]['carbohydrates'] == 0)) {
                                $matrix = [
                                    [
                                        floatval($recipe['ingradients_variable'][0]['proteins']),
                                        floatval($recipe['ingradients_variable'][1]['proteins']),
                                        $need_nutrients['EW']
                                    ],
                                    [
                                        floatval($recipe['ingradients_variable'][0]['fats']),
                                        floatval($recipe['ingradients_variable'][1]['fats']),
                                        $need_nutrients['F']
                                    ],
                                ];
                                if ($calculations = self::_api_solveEquation($matrix)) {
                                    $recipe['ingradients_variable'][0]['amount'] = self::_api_round_up($calculations[0]);
                                    $recipe['ingradients_variable'][1]['amount'] = self::_api_round_up($calculations[1]);
                                } else {
                                    $recipe['notices'] .= 'need to make recipe manually' . PHP_EOL;
                                    $recipe['errors'] = true;
                                }
                            } else {
                                if (($recipe['ingradients_variable'][0]['proteins'] == 0) && ($recipe['ingradients_variable'][1]['proteins'] == 0)) {
                                    $matrix = [
                                        [
                                            floatval($recipe['ingradients_variable'][0]['carbohydrates']),
                                            floatval($recipe['ingradients_variable'][1]['carbohydrates']),
                                            $need_nutrients['KH']
                                        ],
                                        [
                                            floatval($recipe['ingradients_variable'][0]['fats']),
                                            floatval($recipe['ingradients_variable'][1]['fats']),
                                            $need_nutrients['F']
                                        ],
                                    ];
                                    if ($calculations = self::_api_solveEquation($matrix)) {
                                        $recipe['ingradients_variable'][0]['amount'] = self::_api_round_up($calculations[0]);
                                        $recipe['ingradients_variable'][1]['amount'] = self::_api_round_up($calculations[1]);
                                    } else {
                                        $recipe['notices'] .= 'need to make recipe manually' . PHP_EOL;
                                        $recipe['errors'] = true;
                                    }
                                } else {
                                    if (($recipe['ingradients_variable'][0]['fats'] == 0) && ($recipe['ingradients_variable'][1]['fats'] == 0)) {
                                        $matrix = [
                                            [
                                                floatval($recipe['ingradients_variable'][0]['carbohydrates']),
                                                floatval($recipe['ingradients_variable'][1]['carbohydrates']),
                                                $need_nutrients['KH']
                                            ],
                                            [
                                                floatval($recipe['ingradients_variable'][0]['proteins']),
                                                floatval($recipe['ingradients_variable'][1]['proteins']),
                                                $need_nutrients['EW']
                                            ],
                                        ];
                                        if ($calculations = self::_api_solveEquation($matrix)) {
                                            $recipe['ingradients_variable'][0]['amount'] = self::_api_round_up($calculations[0]);
                                            $recipe['ingradients_variable'][1]['amount'] = self::_api_round_up($calculations[1]);
                                        } else {
                                            $recipe['notices'] .= 'need to make recipe manually' . PHP_EOL;
                                            $recipe['errors'] = true;
                                        }
                                    } else {
                                        // calculate by type of ingradients
                                        $recipe['ingradients_variable'][0]['amount'] = self::_api_getAmountIngradientNutriensByType(
                                            $need_nutrients,
                                            $recipe['ingradients_variable'][0]
                                        );
                                        $recipe['ingradients_variable'][1]['amount'] = self::_api_getAmountIngradientNutriensByType(
                                            $need_nutrients,
                                            $recipe['ingradients_variable'][1]
                                        );
                                    }
                                }
                            }
                        } else {
                            if (count($recipe['ingradients_variable']) == 1) {
                                $ingradient = reset($recipe['ingradients_variable']);

                                // if exists ingradient type F,EW,KH
                                if (!empty($ingradient['type'])) {
                                    $ingradient['amount'] = self::_api_getAmountIngradientNutriensByType(
                                        $need_nutrients,
                                        $ingradient
                                    );
                                } else {
                                    if (($ingradient['carbohydrates'] != 0) && ($ingradient['proteins'] == 0) && ($ingradient['fats'] == 0)) {
                                        $ingradient['amount'] = self::_api_round_up(
                                            $need_nutrients['KH'] / $ingradient['carbohydrates']
                                        );
                                    } elseif (($ingradient['proteins'] != 0) && ($ingradient['carbohydrates'] == 0) && ($ingradient['fats'] == 0)) {
                                        $ingradient['amount'] = self::_api_round_up(
                                            $need_nutrients['EW'] / $ingradient['proteins']
                                        );
                                    } elseif (($ingradient['fats'] != 0) && ($ingradient['carbohydrates'] == 0) && ($ingradient['proteins'] == 0)) {
                                        $ingradient['amount'] = self::_api_round_up($need_nutrients['F'] / $ingradient['fats']);
                                    }
                                }

                                if (!empty($ingradient['amount'])) {
                                    $recipe['ingradients_variable'] = [$ingradient];
                                }
                            }
                        }
                    }
                }
                //recalculation amount in g/kg/ml etc...

                foreach ($recipe['ingradients_variable'] as $k => $ingradient) {
                    // calculation total nutrients
                    $recipe['calculated_KCal'] += $ingradient['calories'] * $ingradient['amount'];
                    $recipe['calculated_EW'] += $ingradient['proteins'] * $ingradient['amount'];
                    $recipe['calculated_F'] += $ingradient['fats'] * $ingradient['amount'];
                    $recipe['calculated_KH'] += $ingradient['carbohydrates'] * $ingradient['amount'];
                    // recalculate ingradient total amount
                    $recipe['ingradients_variable'][$k]['amount'] = $recipe['ingradients_variable'][$k]['amount'] * $recipe['ingradients_variable'][$k]['unit_data']['default_amount'];
                    $recipe['ingradients_variable'][$k]['amount'] = self::_api_round_up(
                        $recipe['ingradients_variable'][$k]['amount']
                    );
                }

                foreach ($recipe['ingradients_variable'] as $k => $ingradient) {
                    if ($ingradient['amount'] < 0) {
                        $recipe['notices'] .= 'need to make changes in recipe manually, amount less 0 (' . var_export(
                                [
                                    'id' => $ingradient['id'],
                                    'name' => $ingradient['name'],
                                    'amount' => $ingradient['amount']
                                ],
                                true
                            ) . ')' . PHP_EOL;
                        $recipe['errors'] = true;
                    } elseif (
                        !$allowZeroIngredients
                        && isset($ingradient['allow_replacement'])
                        && $ingradient['allow_replacement'] == true
                        && ($ingradient['amount'] < 1)
                    ) {
                        $recipe['errors'] = true;
                        $recipe['notices'] .= ' recipe has variable ingredient which has less 1g (' . var_export(
                                [
                                    'id' => $ingradient['id'],
                                    'name' => $ingradient['name'],
                                    'amount' => $ingradient['amount']
                                ],
                                true
                            ) . ') ,';
                    } elseif ($ingradient['amount'] == 0) {
                        $recipe['notices'] .= ' recipe has ingredients with amount 0 (' . var_export(
                                [
                                    'id' => $ingradient['id'],
                                    'name' => $ingradient['name'],
                                    'amount' => $ingradient['amount']
                                ],
                                true
                            ) . ')' . PHP_EOL;
                        $recipe['errors'] = true;
                    }
                }

                if (!$allowZeroIngredients) {
                    foreach ($recipe['ingradients'] as $k => $ingradient) {
                        if (
                            isset($ingradient['allow_replacement'])
                            && $ingradient['allow_replacement'] == true
                            && ($ingradient['amount'] < 1)
                        ) {
                            $recipe['errors'] = true;
                            $recipe['notices'] .= ' recipe has ingredients with amount 0 (' . var_export(
                                    [
                                        'id' => $ingradient['id'],
                                        'name' => $ingradient['name'],
                                        'amount' => $ingradient['amount']
                                    ],
                                    true
                                ) . ')' . PHP_EOL;
                        }
                    }
                }

                $recipe['calculated_KCal'] = round($recipe['calculated_KCal']);
                $recipe['calculated_EW'] = round($recipe['calculated_EW'], 1);
                $recipe['calculated_F'] = round($recipe['calculated_F'], 1);
                $recipe['calculated_KH'] = round($recipe['calculated_KH'], 1);
            }

            //        } else {

            //            if ( ( $need_nutrients['KH'] > 2 ) || ( $need_nutrients['EW'] > 2 ) || ( $need_nutrients['F'] > 2 ) ) {
            //                $recipe['errors']  = true;
            //            }

            //            $recipe['notices'] .= ' not exists variable ingradients, ';
            //        }
        } else {
            $recipe['errors'] = true;
            $recipe['notices'] .= ' recipe has more nutrients that need, ';
        }
        // TODO:: to think about notification when recipe is invalid...
        //var_dump($recipe);
        //    if (!empty($recipe['errors'])) {
        //        $data = var_export($recipe, true);
        //        mail("nm@lindenvalley.de",'FAILED RECIPE CALCULATION',$data);
        //    }
        return $recipe;
    }


    public static function _api_round_up($n, $step = 0.05)
    {
        $rem = fmod($n, $step);
        if ($rem < ($step / 2)) {
            return $n - $rem;
        } else {
            return $n - $rem + $step;
        }
    }

    public static function _api_solveEquation($matrix = [])
    {
        $source_matrix = [];
        $b = [];
        $matrix_count = count($matrix[0]);
        for ($i = 0; $i < $matrix_count - 1; $i++) {
            $source_matrix[$i] = [];
            $b[$i] = $matrix[$i][$matrix_count - 1];
            for ($j = 0; $j < $matrix_count - 1; $j++) {
                $source_matrix[$i][$j] = $matrix[$i][$j];
            }
        }
        $result = self::_api_gaussj($source_matrix, $b);
        return $result;
    }

    public static function _api_getAmountIngradientNutriensByType($need_nutrients, $ingradient)
    {
        $amount = false;
        if (!empty($ingradient['type'])) {
            switch ($ingradient['type']) {
                case 'KH':
                    $amount = self::_api_round_up($need_nutrients['KH'] / $ingradient['carbohydrates']);
                    break;
                case 'EW':
                    $amount = self::_api_round_up($need_nutrients['EW'] / $ingradient['proteins']);
                    break;
                case 'F':
                    $amount = self::_api_round_up($need_nutrients['F'] / $ingradient['fats']);
            }
        }
        return $amount;
    }

    public static function calculateRecipe($recipe, $Kcal = 0, $KH = 0, $EW = 0, $F = 0, $allowZeroIngredients = true)
    {
        $result = false;

        $post_data = [
            'recipe' => $recipe,
            'nutrients' => [
                'KCal' => $Kcal,
                'KH' => $KH,
                'EW' => $EW,
                'F' => $F,
            ],
        ];


        $recipe = $post_data['recipe'];
        $KCal = intval($post_data['nutrients']['KCal']);
        $KH = round(floatval($post_data['nutrients']['KH']), 2);
        $EW = round(floatval($post_data['nutrients']['EW']), 2);
        $F = round(floatval($post_data['nutrients']['F']), 2);
        if ((!empty($KH)) && (!empty($KCal))) {
            if (empty($EW)) {
                $calculated_nutrients = self::_api_calculateNutrientsByKhKcal($KH, $KCal);
                if (!empty($calculated_nutrients['EW'])) {
                    $EW = $calculated_nutrients['EW'];
                }
            }
            if (empty($F)) {
                if (empty($calculated_nutrients)) {
                    $calculated_nutrients = self::_api_calculateNutrientsByKhKcal($KH, $KCal);
                }
                if (!empty($calculated_nutrients['F'])) {
                    $F = $calculated_nutrients['F'];
                }
            }
        }

        if ($res = self::_api_calculateRecipeIngradients($recipe, $KCal, $KH, $EW, $F, $allowZeroIngredients)) {
            $recipe = $res;
        }
        $date = array(
            'unixtimestamp' => time(),
            'string' => date('Ymd H:i:s'),
        );
        $response['data']['date'] = $date;
        $recipe['date'] = $date;
        $response['data']['recipe'] = $recipe;
        $response['data']['nutrients'] = $post_data['nutrients'];

        if ((!empty($response['data'])) && (!empty($response['data']['recipe']))) {
            $result = $response['data']['recipe'];
        }

        return $result;
    }

    /**
     * calculate custom recipe by id
     *
     * @param $custom_recipe_id
     * @return array|bool
     */
    public static function calculateCustomRecipe($custom_recipe_id)
    {
        $result = [
            'errors' => true,
            'notices' => 'invalid data',
        ];

        if (!empty($custom_recipe_id)) {
            if ($recipe = CustomRecipe::find($custom_recipe_id)) {
                $nutrients = self::getUserDietData($recipe->user_id);

                $ingestion = $recipe->ingestion;
                $ingestion_key = $ingestion->key;

                if (!empty($nutrients['ingestion'][$ingestion_key])) {
                    $Kcal = $nutrients['ingestion'][$ingestion_key]['Kcal'];
                    $KH = $nutrients['ingestion'][$ingestion_key]['KH'];
                    $EW = $nutrients['ingestion'][$ingestion_key]['EW'];
                    $F = $nutrients['ingestion'][$ingestion_key]['F'];
                }

                $recipe_fixed_ingredients = [];
                $recipe_variable_ingredients = [];
                foreach ($recipe->ingredients as $ingredient) {
                    if ($ingredient->pivot->type == 'fixed') {
                        $recipe_fixed_ingredients[] = self::prepareInredient(
                            $ingredient['id'],
                            $ingredient->pivot->amount
                        )->toArray();
                    } else {
                        $recipe_variable_ingredients[] = self::prepareInredient(
                            $ingredient['id'],
                            $ingredient->pivot->amount
                        )->toArray();
                    }
                }

                if ((!empty($Kcal)) && (!empty($KH))) {
                    $calculated_recipe = self::calculateIngredients(
                        $recipe_fixed_ingredients,
                        $recipe_variable_ingredients,
                        $Kcal,
                        $KH,
                        $EW,
                        $F
                    );

                    if ($calculated_recipe['errors'] == false) {
                        $result = $calculated_recipe;
                    } else {
                        $result = [
                            'errors' => true,
                            'notices' => (!empty($calculated_recipe['notices'])) ? $calculated_recipe['notices'] : 'internal problems',
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Prepare calculated recipe to view
     *
     * @param $recipe
     * @param $ingestion
     * @param null $diet_data
     * @param null $ingestion_key
     * @return array|bool|null
     */
    public static function calcRecipe($recipe, $ingestion, $diet_data = null, $ingestion_key = null)
    {
        $calculated_recipe = null;

        // check user dietdata
        if (empty($diet_data)) {
            $user = \Auth::user();
            if (isset($user) && isset($user->dietdata)) {
                $diet_data = $user->dietdata;
            }
        }

        // check ingestion key
        if (empty($ingestion_key) && !is_null($ingestion)) {
            $ingestion_key = $ingestion->key;
        }

        if (!is_null($ingestion_key) && !empty($diet_data['ingestion'][$ingestion_key])) {
            $nutriens = $diet_data['ingestion'][$ingestion_key];

            //        if (empty(self::$ingradient_units))
            {
                self::$ingradient_units = $ingradient_units = IngredientUnit::get()->keyBy('id')->toArray();
            }
            $ingradient_units = self::$ingradient_units;

            $ingredients = $recipe->ingredients;
            $variable_ingredients = $recipe->variableIngredients;

            if ((!empty($variable_ingredients)) && (count($variable_ingredients))) {
                foreach ($variable_ingredients as $ingredient) {
                    if (isset(IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']])) {
                        $category = IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']];
                        $ingredient->type = $category['short'];
                    }
                    $ingredient->unit_data = $ingradient_units[$ingredient->unit_id];
                }
            }

            if ((!empty($ingredients)) && (count($ingredients))) {
                foreach ($ingredients as $ingredient) {
                    if (isset(IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']])) {
                        $category = IngredientCategory::MAIN_CATEGORIES[$ingredient->category->tree_information['main_category']];
                        $ingredient->type = $category['short'];
                    }
                    $ingredient->unit_data = $ingradient_units[$ingredient->unit_id];

                    if (isset($ingredient->pivot->amount)) {
                        $ingredient->amount = $ingredient->pivot->amount;
                    } else {
                        $ingredient->amount = 0;
                    }
                }//
            }

            $recipe_calculation_data = [
                'ingradients' => (!empty($ingredients)) ? $ingredients->toArray() : [],
                'ingradients_variable' => (!empty($variable_ingredients)) ? $variable_ingredients->toArray() : [],
            ];
            $calculated_recipe = self::calculateRecipe(
                $recipe_calculation_data,
                $nutriens['Kcal'],
                $nutriens['KH'],
                $nutriens['EW'],
                $nutriens['F']
            );
        }

        if (is_null($calculated_recipe)) {
            $calculated_recipe = [
                'errors' => true,
                'notices' => 'User diet data is empty!'
            ];
        }

        return $calculated_recipe;
    }

    //    public static function getRecipeIngredientsIds($recipe_id)
    //    {
    //        $recipe = Recipe::find($recipe_id);
    //        $ingredients =  $recipe->ingredients->toArray();
    //        $ingredients = array_merge($ingredients,$recipe->variableIngredients->toArray());
    //        return $ingredients;
    //    }

    //    public static function getCustomRecipeIngredientsIds($custom_recipe_id)
    //    {
    //        $recipe = CustomRecipe::find($custom_recipe_id);
    //        return $recipe->ingredients;
    //    }


    /**
     * parse Recipe Data
     *
     * @param $_recipe
     * @return array|null
     */
    public static function parseRecipeData($_recipe, string $userLocale = '')
    {
        # get recipe data
        $_parseData = [];
        if ((isset($_recipe->calc_recipe_data) && ($_recipeData = json_decode($_recipe->calc_recipe_data))) &&
            (!empty($_recipeData) && property_exists($_recipeData, 'ingredients'))) {
            $ingredientsIds = array_map(static fn($ingredient) => $ingredient->id, $_recipeData->ingredients);
            $usedIngredients = Ingredient::ofIds($ingredientsIds)->with(['category', 'hint', 'alternativeUnit'])->get(); // Units load eagerly
            foreach ($_recipeData->ingredients as $item) {
                $ingredient = $usedIngredients->find($item->id);
                if ($ingredient === null) {
                    continue;
                }
                $hint = $ingredient->hint?->translations->where('locale', $userLocale)->first();
                $hintContent = [];
                if ($hint !== null) {
                    $hintContent = [
                        'title' => $ingredient->name,
                        'content' => $hint->content,
                        'link_url' => $hint->link_url,
                        'link_text' => $hint->link_text,
                    ];
                }
                $prepareData = [
                    'ingredient_id' => $ingredient->id,
                    'ingredient_type' => $item->type,
                    'main_category' => $ingredient->category->tree_information['main_category'],
                    'ingredient_amount' => (int)$item->amount,
                    'ingredient_text' => $ingredient->unit->visibility ? "{$ingredient->unit->short_name} $ingredient->name" : $ingredient->name,
                    'ingredient_name' => $ingredient->name,
                    'ingredient_unit' => $ingredient->unit->visibility ? $ingredient->unit->short_name : '',
                    'hint' => $hintContent,
                    IngredientConversionService::KEY => app(IngredientConversionService::class)->generateData($ingredient, (int)$item->amount)
                ];

                if ($prepareData['main_category'] == IngredientCategoryEnum::SEASON->value) {
                    $prepareData['ingredient_text'] = $ingredient->name;
                }

                if (!empty($prepareData['main_category']) && isset(IngredientCategory::MAIN_CATEGORIES[$prepareData['main_category']])) {
                    $prepareData['allow_replacement'] = true;
                } else {
                    $prepareData['allow_replacement'] = false;
                }

                $_parseData[] = $prepareData;
            }
        }

        return $_parseData;
    }

    /**
     *
     * add Random Recipe to user for the first time
     *
     * @param       $_user
     * @param int $amountRecipes
     * @param array $seasons
     * @param false $setSeasonsAutomatically
     * @param array $options
     *
     * @return int
     */
    public static function recipeDistributionFirstTime(
        $_user,
        $amountRecipes = 90,
        $seasons = [],
        $setSeasonsAutomatically = false,
        &$options = []
    )
    {

        // resync excluded ingredients
        SyncUserExcludedIngredientsJob::dispatchSync($_user);

        if (empty($options['distribution_mode'])) {
            $options['distribution_mode'] = self::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_PREFERABLE;
        }


        $options['recipe_tags_prioritized'] = [
            // 0 level tag by answers in the formular
            // 1 level first time distribution if no tag from formular
            // 2+ any other tags
        ];


        if ($_user->isQuestionnaireExist()) {
            $latestQuestionnaireAnswers = $_user->latestQuestionnaireFullAnswers;
            $options['distributionType'] = 'ingestions';


            if (!empty($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY])) {
                switch ($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY]) {
                    case MealPerDayQuestionOptionsEnum::BREAKFAST_LUNCH->value:
                    case MealPerDayQuestionOptionsEnum::BREAKFAST_DINNER->value:
                        $options['breakfastSnack'] = $options['lunchDinner'] = intdiv($amountRecipes, 2);
                        break;
                    case MealPerDayQuestionOptionsEnum::LUNCH_DINNER->value:
                        $options['lunchDinner'] = $amountRecipes;
                        $options['breakfastSnack'] = 0;
                        break;
                    case MealPerDayQuestionOptionsEnum::STANDARD->value:
                    default:
                        if (empty($options['breakfastSnack'])) {
                            $options['breakfastSnack'] = ($amountRecipes > 25) ? 25 : intdiv($amountRecipes, 3);
                        }
                        if (empty($options['lunchDinner'])) {
                            $options['lunchDinner'] = $amountRecipes - $options['breakfastSnack'];
                        }
                        break;
                }
            }

            if (isset($options['strict_amounts'])) {
                if (isset($options['strict_amounts']['breakfastSnack'])) {
                    $options['breakfastSnack'] = $options['strict_amounts']['breakfastSnack'];
                }
                if (isset($options['strict_amounts']['lunchDinner'])) {
                    $options['lunchDinner'] = $options['strict_amounts']['lunchDinner'];
                }
            }

            $hasBreakfast = !(empty($_user->dietdata['ingestion']['breakfast']['percents']));
            $hasLunch = !(empty($_user->dietdata['ingestion']['lunch']['percents']));
            $hasDinner = !(empty($_user->dietdata['ingestion']['dinner']['percents']));

            /*
                Automation adds 90 randomize recipes, sometimes only 5 breakfast and 85 lunch/dinner.
                Please define, that the automation adds 25 breakfast and 65 lunch/dinner recipes

                If there are no 25 breakfast recipes: add more lunch/dinner recipes until 90 recipes have been added
                is user has only breakfast and dinner: add 25 breakfast and 65 dinner
                if user has only breakfast and lunch: add 25 breakfast and 65 lunch
            */

            if (!$hasLunch && !$hasDinner) {
                $options['breakfastSnack'] = $amountRecipes;
                $options['lunchDinner'] = 0;
            } elseif (!$hasBreakfast) {
                $options['breakfastSnack'] = 0;
                $options['lunchDinner'] = $amountRecipes;
            }

            if (!empty($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::RECIPE_PREFERENCES])) {
                $configRecipesTagsBasedOnAnswers = config('questionnaire.recipes_tag_based_on_answers.recipe_preferences');
                $routineValue = $latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::RECIPE_PREFERENCES];
                if (
                    !empty($configRecipesTagsBasedOnAnswers[$routineValue])
                ) {
                    // TODO:: refactor that to tags @NickMost
                    $recipeTagId = intval($configRecipesTagsBasedOnAnswers[$routineValue]);
                    $options['recipe_tags_prioritized'][] = [$recipeTagId];
                }
            }
        }

        /**
         * @notes: refactored from recipe categories to tags
         */
        $firstMealPlanRecipeTagId = config('adding-new-recipes.first_meal_plan_recipe_tag_id');
        if (!empty($firstMealPlanRecipeTagId)) {
            $options['recipe_tags_prioritized'][] = [$firstMealPlanRecipeTagId];
        }

        $rawOptions = $options;

        $count = self::_addRandomRecipe2user($_user, $amountRecipes, $seasons, $setSeasonsAutomatically, $options);

        //log only first time recipes distributions

        // clear logs only for production
        if (\App::environment('production')) {
            // removing details ingestions log
            if (!empty($options['ingestions_scope'])) {
                $tmp = $options['ingestions_scope'];
                foreach ($options['ingestions_scope'] as $index => $ingestionScopes) {
                    if (isset($tmp[$index]['recipes'])) {
                        unset($tmp[$index]['recipes']);
                    }
                }
                $rawOptions['ingestions_scope'] = $tmp;
            }
        }
        \Log::info(
            'Recipes distribution has been finished => ' . $_user->getKey() . ' results=' . var_export(
                $rawOptions,
                true
            )
        );

        return $count;
    }


    /**
     * Uses _addRandomRecipe2user for getting recipes in scope of $diff and $excluded_recipes_ids_by_user_exclusion by categories
     *
     * @param array $diff
     * @param array $excluded_recipes_ids_by_user_exclusion
     * @param array $recipesTags
     * @return array
     */
    private static function _internalGetRecipesFor_addRandomRecipe2user(
        $diff = [],
        $excluded_recipes_ids_by_user_exclusion = [],
        $recipesTags = []
    ): array
    {

        // TODO: @NickMost review, probably need additional conditions
        if (is_array($recipesTags)) {
            $recipesTags = array_map('intval', $recipesTags);
        } else {
            $recipesTags = [intval($recipesTags)];
        }

        // TODO:: check by tags
        return Recipe::query()
            // TODO:: replace to recipe tags
            ->whereHas(
                'tags',
                function (Builder $builder) use ($recipesTags) {
                    $builder->whereIn('recipe_tag_id', $recipesTags);
                }
            )
            ->whereNotIn('id', $excluded_recipes_ids_by_user_exclusion)
            ->whereIn('id', $diff)
            ->pluck('id')
            ->toArray();
    }

    /**
     * add Random Recipe to user
     * Distribution of recipes: When creating the weekly plans out of the users's recipes preferably use the seasonylity for the month. Example: creating the weekly / monthly recipe-plan in January: first use recipes with seasonality january - after the recipes of this season are used or if there are not enough recipes or no recipes at all use all other recipes.
     *
     * The script that distribute recipes into the meal plan should have the following additional conditions:
     *
     * - check, if the recipe is valid for the month it should be placed in (recipe can be valid only for this month but also for all months)
     * - do not distribute the same recipe more than 2 times per month
     * - if not enough recipes match the conditions above, use further recipes from "all recipes"
     * - if all recipes have been used 2 times per month, ignore this condition until all slots are filled
     *
     * @param       $_user
     * @param int $amountRecipes
     * @param array $seasons
     * @param false $setSeasonsAutomatically
     *
     * @return int
     */
    public static function _addRandomRecipe2user(
        $_user,
        $amountRecipes = 90,
        $seasons = [],
        $setSeasonsAutomatically = false,
        &$options = []
    )
    {
        if (is_null($setSeasonsAutomatically)) {
            $setSeasonsAutomatically = false;
        }

        if (!empty($setSeasonsAutomatically) && empty($seasons)) {
            $seasons = [];
        }

        $excluded_recipes_ids_by_user_exclusion = [];
        if (!empty($_user->excluded_recipes)) {
            $excluded_recipes_ids_by_user_exclusion = $_user->excluded_recipes->toArray();
        }

        $excluded_recipes_ids_by_seasons = [];
        $recipesIdsWithoutSeasons = [];

        // TODO:: @NickMost review seasons
        // TODO: amend here for new approach @NickMost

        $recipesIdBySeasons = [];

        $SEASONS_ANY_ID = 0;

        $recipesIdWithAnySeasons = DB::table('recipes_to_seasons')->select('recipe_id')->where('seasons_id', $SEASONS_ANY_ID)->pluck('recipe_id')->toArray();

        if (!empty($seasons)) {

            $recipesIdBySeasons = DB::table('recipes_to_seasons')->select('recipe_id')->whereIn(
                'seasons_id',
                $seasons
            )->pluck('recipe_id')->toArray();
        }

        // getting recipes which were attached by last month
        $monthAgoDate = \Carbon\Carbon::now()->subMonths(1);
        $lastMonthRecipes = $_user->recipes()
            ->where('meal_date', '>', $monthAgoDate)
            ->orderBy('recipe_id')
            ->pluck('recipe_id')
            ->toArray();
        $recipesUses = [];
        foreach ($lastMonthRecipes as $recipeId) {
            if (!isset($recipesUses[$recipeId])) {
                $recipesUses[$recipeId] = 0;
            }
            $recipesUses[$recipeId]++;
        }
        $recipeIdsUsesMoreThanTwice = array_keys(
            array_filter(
                $recipesUses,
                static fn($var) => $var > 1
            )
        );

        $allowAnyLangRecipes = true;
        // checking if user's lang is English
        if (isset($_user->lang) && $_user->lang == LanguagesEnum::EN) {
            // need to get only fully translated recipes
            $allowAnyLangRecipes = false;
        }

        # get exist recipe and calculations
        $existRecipeIds = $_user->allRecipes()
//                                ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
//                                ->where('user_recipe_calculated.user_id', $_user->getKey())
//            ->whereNotIn('recipes.id', $excluded_recipes_ids_by_user_exclusion)
            ->groupBy('recipes.id')
            ->pluck('recipes.id')
            ->toArray();

        # get all recipes Ids
        // TODO необходимо заменить на выборку с учетом "связанных рецептов"
        $allRecipeIds = Recipe::isActive()->whereNotIn(
            'id',
            $recipeIdsUsesMoreThanTwice
        )->whereNotIn(
            'id',
            $excluded_recipes_ids_by_user_exclusion
        );

        // TODO:: @NickMost one more time seasons relation
        if (!empty($seasons)) {
            # excluding recipes by ingredients restriction
            //            if (!empty($excluded_recipes_ids_by_seasons)) {
            $allRecipeIds = $allRecipeIds->whereIn('id', $recipesIdBySeasons);
            //            }

            // excluding recipes without any seasons
            //            if (!empty($recipesIdsWithoutSeasons)) {
            //                $allRecipeIds = $allRecipeIds->whereNotIn('id', $recipesIdsWithoutSeasons);
            //            }
        }

        if ($allowAnyLangRecipes === false) {
            $allRecipeIds = $allRecipeIds->where('translations_done', 1);
        }

        // TODO:: place for optimization, could be excluded not applicable recipes by ingredients, exclusions etc.
        $allRecipeIds = $allRecipeIds->pluck('id')->toArray();

        $allRecipeWithAnySeasonsIds = [];
        if (!empty($recipesIdWithAnySeasons)) {
            $allRecipeWithAnySeasonsIds = Recipe::isActive()
                ->whereIn('id', $recipesIdWithAnySeasons)
                ->whereNotIn('id', $excluded_recipes_ids_by_user_exclusion)
                ->whereNotIn('id', $existRecipeIds)
                ->pluck('id')
                ->toArray();
        }

        $diff = array_diff($allRecipeIds, $existRecipeIds);

        $preliminaryCalcDataRow = $_user->preliminaryCalc()->first();
        if (
            !empty($preliminaryCalcDataRow) &&
            !empty($preliminaryCalcDataRow->valid) &&
            is_array($preliminaryCalcDataRow->valid) &&
            ($_user->isQuestionnaireExist()) &&
            ($_user->questionnaire()->first()->updated_at < $preliminaryCalcDataRow->updated_at)
        ) {
            $diff = array_intersect($diff, $preliminaryCalcDataRow->valid);
            // all valid recipes for user
            $allRecipeWithAnySeasonsIds = array_intersect($allRecipeWithAnySeasonsIds, $preliminaryCalcDataRow->valid);
        }

        $options['ingestions_scope'] = [];

        $options['response_type'] = 'simple';
        if (!empty($options['distributionType']) && $options['distributionType'] == 'ingestions') {
            $options['response_type'] = 'full';

            $amountRecipes = 0;
            if (!empty($options['breakfastSnack'])) {
                $options['ingestions_scope'][] = [
                    // dissabled snack recipes distribution
//                    'ingestion_ids'     => [1, 4],
                    'ingestion_ids' => [1],
                    'ingestion_key' => 'breakfastSnack',
                    'count_requested' => intval($options['breakfastSnack']),
                    'count_distributed' => 0,
                    'recipes' => [],
                ];
                $amountRecipes += intval($options['breakfastSnack']);
            }

            if (!empty($options['lunchDinner'])) {
                $options['ingestions_scope'][] = [
                    'ingestion_ids' => [2, 3],
                    'ingestion_key' => 'lunchDinner',
                    'count_requested' => intval($options['lunchDinner']),
                    'count_distributed' => 0,
                    'recipes' => [],
                ];
                $amountRecipes += intval($options['lunchDinner']);
            }
        }

        $options['allow_any_lang_recipes'] = $allowAnyLangRecipes;

        // recipes without categories
        $rawDiff = $diff;


        if (empty($options['recipe_tags_prioritized'])) {
            $options['recipe_tags_prioritized'] = [];
        }

        // adding recipes_tag into scope of recipe_categories_prioritized
        if (!empty($options['recipes_tag'])) {
            $options['recipe_tags_prioritized'][] = $options['recipes_tag'];
        }


        if (empty($options['distribution_mode'])) {
            $options['distribution_mode'] = self::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_PREFERABLE;
        }

        $count = 0;
        // if mode is strict we are getting only recipes which 100% related to filters
        if ($options['distribution_mode'] == self::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_STRICT) {
            // strict mode, getting only recipes under conditions

            // distribution has priority of categories
            if (!empty($options['recipe_tags_prioritized']) && is_array($options['recipe_tags_prioritized'])) {
                //
                while (!empty($options['recipe_tags_prioritized']) && $count < $amountRecipes) {
                    // TODO refactor that to tags
                    $stepRecipeTags = array_shift($options['recipe_tags_prioritized']);

                    $diff = self::_internalGetRecipesFor_addRandomRecipe2user(
                        $rawDiff,
                        $excluded_recipes_ids_by_user_exclusion,
                        $stepRecipeTags
                    );


                    // TODO:: refactor it, remove duplications
                    $options['distribution_mode'] = 'level0_normal';
                    $count = self::internalDistributeAndCalculateRecipesForUser(
                        $diff,
                        $_user,
                        $amountRecipes,
                        $count,
                        $options
                    );

                    self::internalCheckDistributionTypeIngestions($options, $count, $amountRecipes);
                }
            } else {
                $options['distribution_mode'] = 'level0_normal';
                $count = self::internalDistributeAndCalculateRecipesForUser(
                    $diff,
                    $_user,
                    $amountRecipes,
                    $count,
                    $options
                );
            }
        } else {
            $spentRecipesIds = [];

            if (!empty($options['recipe_tags_prioritized']) && is_array($options['recipe_tags_prioritized'])) {
                //
                while (!empty($options['recipe_tags_prioritized']) && $count < $amountRecipes) {
                    $stepRecipeTags = array_shift($options['recipe_tags_prioritized']);

                    $diff = self::_internalGetRecipesFor_addRandomRecipe2user(
                        $rawDiff,
                        $excluded_recipes_ids_by_user_exclusion,
                        $stepRecipeTags
                    );


                    // TODO:: refactor it, remove duplications
                    $options['distribution_mode'] = 'level0_normal';
                    $count = self::internalDistributeAndCalculateRecipesForUser(
                        $diff,
                        $_user,
                        $amountRecipes,
                        $count,
                        $options
                    );

                    self::internalCheckDistributionTypeIngestions($options, $count, $amountRecipes);

                    $spentRecipesIds = array_merge($spentRecipesIds, $diff);
                }
                $diff = $rawDiff;
            }

            if ($count < $amountRecipes) {
                $diff = array_diff($diff, $spentRecipesIds);

                // recipes distribution first step without category
                $options['distribution_mode'] = 'level1_normal';
                $count = self::internalDistributeAndCalculateRecipesForUser(
                    $diff,
                    $_user,
                    $amountRecipes,
                    $count,
                    $options
                );

                //TODO:: refactor that
                self::internalCheckDistributionTypeIngestions($options, $count, $amountRecipes);

                $spentRecipesIds = array_merge($spentRecipesIds, $diff);
            }


            if ($count < $amountRecipes) {
                // recipes which are available for user and haven't any seasons
                $diffRecipeWithAnySeasons = array_diff($allRecipeWithAnySeasonsIds, $existRecipeIds);
                $diffRecipeWithAnySeasons = array_diff($diffRecipeWithAnySeasons, $allRecipeIds);
                $diffRecipeWithAnySeasons = array_diff($diffRecipeWithAnySeasons, $spentRecipesIds);

                if (!empty($diffRecipeWithAnySeasons)) {
                    $options['distribution_mode'] = 'level2_recipes_without_any_seasons';
                    $count = self::internalDistributeAndCalculateRecipesForUser(
                        $diffRecipeWithAnySeasons,
                        $_user,
                        $amountRecipes,
                        $count,
                        $options
                    );

                    $spentRecipesIds = array_merge($spentRecipesIds, $diffRecipeWithAnySeasons);
                }
            }

            // added recipes by seasons
            // added recipes without any seasons
            // and recipes aren't still enough


            //TODO:: refactor that
            self::internalCheckDistributionTypeIngestions($options, $count, $amountRecipes);


            // added recipes by seasons
            // added recipes without any seasons
            // and recipes aren't still enough
            // step last panic situation, adding any recipes......
            //
            if ($count < $amountRecipes) {
                $allRecipeIds = Recipe::isActive()
                    ->whereNotIn('id', $excluded_recipes_ids_by_user_exclusion)
                    ->pluck('id')->toArray();
                $options['distribution_mode'] = 'level3_all_recipes';
                $count = self::internalDistributeAndCalculateRecipesForUser(
                    $allRecipeIds,
                    $_user,
                    $amountRecipes,
                    $count,
                    $options
                );
            }


            // last chance panic step, check ingestions for 25/65 distribution, if there are not enough breakfast - add lunch....
            //            if ($count < $amountRecipes) {
            //                if (!empty($options['distributionType']) && $options['distributionType'] == 'ingestions') {
            ////                    $options['distributionType']        = 'general';
            ////                    $options['backup_ingestions_scope'] = $options['ingestions_scope'];
            ////                    // removing ingestions checking for recipes
            ////                    unset($options['ingestions_scope']);
            //
            //                    $allRecipeIds                 = Recipe::where('draft', 0)->whereNotIn(
            //                        'id',
            //                        $excluded_recipes_ids_by_user_exclusion
            //                    )->pluck('id')->toArray();
            //                    $options['distribution_mode'] = 'level5_all_recipes_without_ingestions';
            //                    $count                        = self::internalDistributeAndCalculateRecipesForUser(
            //                        $allRecipeIds,
            //                        $_user,
            //                        $amountRecipes,
            //                        $count,
            //                        $options
            //                    );
            //                }
            // not any other rules for proper recipes distribution
            //            }
        }

        // cache cleanup, cache has been cleanup in internalDistributeAndCalculateRecipesForUser::_calcRecipe2user

        return $count;
    }

    private static function internalDistributeAndCalculateRecipesForUser(
        $recipesIds,
        $user,
        $amountRecipes,
        $count = 0,
        &$options = []
    )
    {
        if (empty($count)) {
            $count = 0;
        }
        while ($count < $amountRecipes && count($recipesIds) > 0) {
            do {
                $randKey = array_rand($recipesIds);
                $recipeId = $recipesIds[$randKey];
                $result = self::_calcRecipe2user($user, [$recipeId], true, $options);
                if (!empty($result['options'])) {
                    $options = $result['options'];
                }
                unset($recipesIds[$randKey]);
            } while (!$result['success'] && count($recipesIds) > 0);

            if ($result['success']) {
                $count++;
            }

            //TODO:: refactor that
            self::internalCheckDistributionTypeIngestions($options, $count, $amountRecipes);
        }
        return $count;
    }

    /**
     * calculate Recipe to user
     *
     * @param User $_user
     * @param array $_recipeIds
     * @param bool $isFirstAdd
     * @param array $options
     * @return array
     */
    public static function _calcRecipe2user(User $_user, array $_recipeIds, bool $isFirstAdd = false, $options = [])
    {
        //        $options['distributionType']
        //        $options['breakfastSnack']
        //        $options['lunchDinner']
        //        $options['ingestions_scope']
        //        $options['skip_related_recipes']

        # error array
        $_errors = [
            'success' => true,
            'message' => null,
            'IDs' => null,
            'options' => $options,
        ];

        $originallyRequestedRecipes = $_recipeIds;

        // excluding | TODO: $_user->excluded_recipes make duplicated query, use once
        $excluded_recipes_ids_by_user_exclusion = [];
        if (!empty($_user->excluded_recipes)) {
            $excluded_recipes_ids_by_user_exclusion = $_user->excluded_recipes->toArray();
        }
        $_recipeIds = array_diff($_recipeIds, $excluded_recipes_ids_by_user_exclusion);

        if (empty($_recipeIds)) {
            $_errors['success'] = false;
            $_errors['message'] = trans('common.not_exists_available_recipe');
        }

        if (array_values($originallyRequestedRecipes) != array_values($_recipeIds)) {
            $_errors['message'] = 'Recipes were restricted by user\'s exclusions (' . implode(
                    ', ',
                    array_diff($originallyRequestedRecipes, $_recipeIds)
                ) . ')';
        }

        $recipesForDelete = [];
        # recipe from user create
        foreach ($_recipeIds as $itemId) {
            # clone recipe Id
            $recipeId = $itemId;

            # get exist recipe and calculations
            $existRecipeIds = Recipe::isActive()
                ->where('id', $recipeId)
                ->pluck('related_recipes', 'id')
                ->toArray();

            // getting whole pack of related recipes
            $fullPackOfRelatedRecipesIds = [$recipeId];
            foreach (array_values($existRecipeIds) as $item) {
                if (!empty($item) && is_array($item)) {
                    $fullPackOfRelatedRecipesIds = array_merge($fullPackOfRelatedRecipesIds, array_map('intval', $item));
                }
            }
            // checking if recipes are undrafted and production ready
            $fullPackOfRelatedRecipesIds = Recipe::isActive()
                ->whereIn('id', $fullPackOfRelatedRecipesIds)
                ->pluck('id')
                ->toArray();

            if (!empty($options['skip_related_recipes'])) {
                $_relRecipeIDs = [$itemId];
            } else {
                // TODO:: get only undrafted recipes
                $_relRecipeIDs = array_keys($existRecipeIds);
                foreach (array_values($existRecipeIds) as $item) {
                    if (!empty($item) && is_array($item)) {
                        $_relRecipeIDs = array_merge($_relRecipeIDs, array_map('intval', $item));
                    }
                }

                // checking if recipes are undrafted and production ready
                $_relRecipeIDs = Recipe::isActive()
                    ->whereIn('id', $_relRecipeIDs)
                    ->pluck('id')
                    ->toArray();
            }

            # get exist calculated for related group
            $relatedRecipeCalc = $_user->allRecipes()
                ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
                ->where('user_recipe_calculated.user_id', $_user->getKey())
                ->whereIn('recipes.id', $fullPackOfRelatedRecipesIds)
                ->where('user_recipe_calculated.invalid', '=', 0)
                ->groupBy('recipes.id')
                ->pluck('recipes.id')
                ->toArray();


            if (count($relatedRecipeCalc) !== 0 && $isFirstAdd) {
                $_errors['success'] = false;
                $_errors['message'] .= '<br>Recipe <b>#' . $itemId . '</b> not added! Exist valid related recipes <b>(' . implode(
                        '; ',
                        $relatedRecipeCalc
                    ) . ')</b>';
                continue;
            }

            $validResult = false;
            $recipeIsValid = false;
            do {
                # get related recipe Id
                $recipeId = array_shift($_relRecipeIDs);
                // TODO: why shift and not pop? it has to reindex all the keys, is it because first is original recipe? we can then reverse the array and pop it
                # get recipe by id
                $_recipe = Recipe::query()->find($recipeId);

                # check recipe & recipe ingestion
                if (is_null($_recipe) || $_recipe->ingestions->count() === 0) {
                    continue;
                }

                if (
                    isset($options['allow_any_lang_recipes'])
                    &&
                    $options['allow_any_lang_recipes'] === false
                    &&
                    !$_recipe->translations_done
                ) {
                    continue;
                }

                # ===========================
                # check Recipe valid for user
                # ===========================

                # check valid recipe data
                $validRecipeData = static::checkUserValidRecipe($recipeId, $_user);


                $recipeIsValid = !empty($validRecipeData['valid']);

                // replace only if recipe was exists for current user before
                if (!$recipeIsValid && !$isFirstAdd) {
                    # get recipe calc
                    $calcRecipes = UserRecipeCalculated::where('user_id', $_user->getKey())->whereNotNull(
                        'recipe_id'
                    )->where('recipe_id', $recipeId)->get();

                    foreach ($calcRecipes as $calcRecipe) {
                        $recipeData = $calcRecipe->recipe_data;

                        $recipeData['errors'] = true;
                        $recipeData['notices'] = [$validRecipeData['notices']];

                        $calcRecipe->invalid = true;
                        $calcRecipe->recipe_data = $recipeData;
                        $calcRecipe->save();

                        # update timestamps update_at
                        $calcRecipe->touch();
                    }

                    if (!empty($calcRecipes)) {
                        $_errors['options']['actions'][] = 'Recipe ID: ' . $recipeId . ' mark as invalid';
                    }
                    # replace recipe in user feed
                    $replaceResults = static::replaceRecipesInUserFeed($recipeId, $_user->id, $_user);
                    // adding logs
                    if (!empty($replaceResults['actions'])) {
                        foreach ($replaceResults['actions'] as $action) {
                            $_errors['options']['actions'][] = $action;
                        }
                    }
                    continue;
                } elseif (!$recipeIsValid && $isFirstAdd) {
                    $_errors['success'] = false;
                    $_errors['message'] .= '<br>Recipe <b>#' . $recipeId . '</b> — not valid for this user!';
                    continue;
                }


                # ==========================
                # check Recipe valid KCal/KH
                # ==========================

                $validResult = static::_validRecipeKcalKH($_recipe, $_user->dietdata);
                // TODO:: check if single ingestion is false!!!
                $ingestionInvalidValues = array_filter(
                    $validResult['ingestions'],
                    function ($item) {
                        return $item == true;
                    }
                );

                if (!empty($validResult['errors']) || !empty($ingestionInvalidValues)) {
                    $recipeIsValid = empty($validResult['errors']);

                    $ingestionIds = [];
                    foreach ($validResult['ingestions'] as $ingestionId => $ingestionInvalid) {
                        if ($ingestionInvalid) {
                            $ingestionIds[] = $ingestionId;
                        }
                    }

                    $calcRecipes = UserRecipeCalculated::where('user_id', $_user->getKey())
                        ->whereNotNull('recipe_id')
                        ->where('recipe_id', $recipeId)
                        ->whereIn('ingestion_id', $ingestionIds)
                        ->get();

                    foreach ($calcRecipes as $calcRecipe) {
                        $recipeData = $calcRecipe->recipe_data;

                        $recipeData['errors'] = true;
                        $recipeData['notices'] = [$validResult['message']];

                        $calcRecipe->invalid = true;
                        $calcRecipe->recipe_data = $recipeData;
                        $calcRecipe->save();

                        # update timestamps update_at
                        $calcRecipe->touch();
                    }
                    if (!empty($validResult['errors'])) {
                        # replace recipe in user feed
                        $replaceResults = static::replaceRecipesInUserFeed($recipeId, $_user->id, $_user);
                        // adding logs
                        if (!empty($replaceResults['actions'])) {
                            foreach ($replaceResults['actions'] as $action) {
                                $_errors['options']['actions'][] = $action;
                            }
                        }
                    }
                }
            } while (!$recipeIsValid && count($_relRecipeIDs));


            # ===============================
            # calculate recipe for meal times
            # ===============================

            # flag calc Error
            $calcError = true;

            if ($recipeIsValid && isset($validResult) && !$validResult['errors'] && (!empty($validResult['ingestions']))) {
                $allIngestions = Ingestion::get();
                $allInactiveIngestions = $allIngestions->filter(
                    function ($item) {
                        if (!$item->active) {
                            return $item;
                        }
                    }
                );

                $validIngestions = []; // TODO: not used

                foreach ($validResult['ingestions'] as $ingestionId => $ingestionError) {
                    // commented 20220505 by Mykola Mostovyi
                    //                    if ($ingestionError) continue;

                    $validIngestions[] = $ingestionId; // TODO: not used

                    # get ingestion
                    $ingestion = Ingestion::findOrFail($ingestionId);

                    $recipeData = static::calcRecipe($_recipe, $ingestion, $_user->dietdata);

                    if (!is_null($recipeData) && !$recipeData['errors']) {
                        # flag calc Error
                        $calcError = false;

                        # recipeData optimization
                        $recipeData = static::calcRecipeOptimization($_recipe, $recipeData, [], []);


                        if (!empty($recipeData['errors'])) {
                            // recipe is invalid
                            $recipeCalc = UserRecipeCalculated::where('user_id', $_user->getKey())
                                ->where('recipe_id', $recipeId)
                                ->where(
                                    'ingestion_id',
                                    $ingestion->id
                                )->first();

                            if ($recipeCalc) {
                                $recipeCalc->invalid = $recipeData['errors'];
                                $recipeCalc->recipe_data = $recipeData;
                                $recipeCalc->save();

                                $_errors['options']['actions'][] = 'Recipe ID: ' . $recipeId . ' mark as invalid';
                            }
                        } else {
                            //recipe is valid, we can add it
                            $recipeCalc = UserRecipeCalculated::updateOrCreate(
                                [
                                    'user_id' => $_user->getKey(),
                                    'recipe_id' => $recipeId,
                                    'ingestion_id' => $ingestion->id,
                                ],
                                [
                                    'invalid' => $recipeData['errors'],
                                    'recipe_data' => $recipeData
                                ]
                            );
                            $_errors['options']['actions'][] = 'Recipe ID: ' . $recipeId . ' mark as valid';
                        }

                        if ($recipeId != $itemId) {
                            # get Related Recipe Calculation
                            $oldRecipeCreatedAt = UserRecipeCalculated::where('user_id', $_user->getKey())
                                ->where('recipe_id', $itemId)
                                ->where('ingestion_id', $ingestion->id)
                                ->pluck('created_at')
                                ->first();

                            if (empty($oldRecipeCreatedAt)) {
                                continue;
                            }

                            $recipeCalc->created_at = $oldRecipeCreatedAt;
                            $recipeCalc->save(['timestamps' => false]);
                        }

                        # update timestamps update_at
                        $recipeCalc->touch();
                    } else {
                        // if recipe has invalid ingestion - need to save it into database
                        // fix for recipes which has only part of ingestion invalid
                        $recipeData = static::calcRecipeOptimization($_recipe, $recipeData, [], []);

                        $recipeCalc = UserRecipeCalculated::where('user_id', $_user->getKey())
                            ->where('recipe_id', $recipeId)
                            ->where('ingestion_id', $ingestionId)
                            ->update(
                                [
                                    'invalid' => $recipeData['errors'],
                                    'recipe_data' => json_encode($recipeData)
                                ]
                            );
                        //                       $recipeCalc->touch();

                    }
                }

                // remove invalid ingestions
                $ingestionsForRemove = [];
                foreach ($allInactiveIngestions as $ingestion) {
                    $ingestionsForRemove[] = $ingestion->id;
                }

                //                foreach ($allIngestions as $ingestion) {
                //                    if (!in_array($ingestion->id, $validIngestions)) {
                //                        $ingestionsForRemove[] = $ingestion->id;
                //                    }
                //                }

                if (!empty($ingestionsForRemove)) {
                    $ingestionsForRemove = array_unique($ingestionsForRemove);
                    UserRecipeCalculated::where('user_id', $_user->getKey())->where(
                        'recipe_id',
                        $recipeId
                    )->whereIn('ingestion_id', $ingestionsForRemove)->delete();
                }
                // remove invalid ingestions end

            }


            ////////////////////////////////////////////////////////////////
            $allowedByIngestionsRestictions = true;
            // checking is exists allowed ingestions scope for current recipe
            if (!empty($options['ingestions_scope'])) {
                $allowedByIngestionsRestictions = false;
                if ($recipeIsValid && isset($validResult) && !$validResult['errors'] && (!empty($validResult['ingestions']))) {
                    foreach ($validResult['ingestions'] as $ingestionId => $ingestionError) {
                        foreach ($options['ingestions_scope'] as $ingestionScopes) {
                            if (in_array(
                                    $ingestionId,
                                    $ingestionScopes['ingestion_ids']
                                ) && $ingestionScopes['count_distributed'] < $ingestionScopes['count_requested']) {
                                $allowedByIngestionsRestictions = true;
                            }
                        }
                    }
                }
            }

            if (!$allowedByIngestionsRestictions) {
                $_errors['success'] = false;
                $_errors['message'] .= '<br>Recipe <b>#' . $recipeId . '</b> — can not be addded by ingestions restrictions!';
                continue;
            }

            ////////////////////////////////////////////////////////////////

            # check calc Error and add recipe to user
            if (!$calcError) {
                # check exist recipe
                $existRecipe = $_user->allRecipes()->where('recipe_id', $recipeId)->count();

                if ($existRecipe == 0) {
                    // PROBABLY place of update created_at
                    $_user->allRecipes()->syncWithoutDetaching(['recipe_id' => $recipeId]);

                    $_errors['options']['actions'][] = 'User ID: ' . $_user->getKey() . ' Recipe ID: ' . $recipeId . ' added into user scope';

                    // need to check if user has related recipe before
                    // label "new" fix
                    $existRelatedRecipes = 0;
                    $recipeForAdding = Recipe::query()->find($recipeId);
                    if (!empty($recipeForAdding->related_recipes)) {
                        $existRelatedRecipes = $_user->allRecipes()->whereIn(
                            'recipe_id',
                            $recipeForAdding->related_recipes
                        )->count();
                    }


                    // update calculation date for recipe, when recipe assigning to the user
                    UserRecipeCalculated::where(
                        [
                            ['user_id', $_user->getKey()],
                            ['recipe_id', $recipeId],
                            ['invalid', 0]
                        ]
                    )->get()->each(
                        function ($item, $key) use ($existRelatedRecipes) {
                            if ($existRelatedRecipes != 0) {
                                // if related recipes were exists in all recipes list, we shift current date to -10 days, it will not show label "new" for related recipes
                                // because users saw the same cover before
                                $item->created_at = $item->updated_at = \Carbon\Carbon::now()->subDays(10);
                            } else {
                                $item->created_at = $item->updated_at = \Carbon\Carbon::now();
                            }
                            $item->save();
                        }
                    );

                    UserService::syncRelatedRecipesCreateDate($_user, [$recipeId]);
                    //$originalRequestedRecipesId

                    if ($itemId != $recipeId) {
                        $_errors['message'] .= '<br>Recipe <b>#' . $recipeId . '</b> as related to recipe #' . $itemId . ' added successfully!';
                    } else {
                        $_errors['message'] .= '<br>Recipe <b>#' . $recipeId . '</b> added successfully!';
                    }
                    $_errors['IDs'] = is_null($_errors['IDs']) ? $recipeId : $_errors['IDs'] . ',' . $recipeId;
                    $_errors['success'] = true;
                }

                if ($recipeId != $itemId) {
                    # replace recipe in user feed
                    //static::replaceRecipesInUserFeed($itemId, $_user->getKey(), $_user);

                    foreach ($validResult['ingestions'] as $ingestionId => $ingestionError) {
                        if ($ingestionError) {
                            continue;
                        }


                        \DB::table('recipes_to_users')->where(
                            [
                                ['user_id', $_user->getKey()],
                                ['recipe_id', $itemId],
                                ['ingestion_id', $ingestionId],
                            ]
                        )->update(
                            [
                                'recipe_id' => $recipeId,
                                'custom_recipe_id' => null,
                            ]
                        );

                        $_errors['options']['actions'][] = 'User ID: ' . $_user->getKey() . ' Recipe ID: ' . $itemId . ' replaced to recipe ID:' . $recipeId;
                    }

                    // PROBABLY place of update created_at
                    //updating all recipes scope
                    $_user->allRecipes()->syncWithoutDetaching(['recipe_id' => $recipeId]);

                    // add recipe into scope for removing....
                    # remove recipe
                    //					static::deleteRecipesByUser($itemId, $_user->getKey());
                    $recipesForDelete[] = $itemId;
                }

                // adding new recipes into ingestinos scope
                $ingestionFound = false;
                if (!empty($validResult['ingestions']) && is_array($validResult['ingestions'])) {
                    $validResult['ingestions'] = self::shuffle_assoc($validResult['ingestions']);
                }
                foreach ($validResult['ingestions'] as $ingestionId => $ingestionError) {
                    if ($ingestionError) {
                        continue;
                    }
                    if (!empty($options['ingestions_scope'])) {
                        foreach ($options['ingestions_scope'] as $index => $ingestionScopes) {
                            if (
                                !$ingestionFound
                                &&
                                in_array($ingestionId, $ingestionScopes['ingestion_ids'])
                                &&
                                $ingestionScopes['count_distributed'] < $ingestionScopes['count_requested']
                            ) {
                                $options['ingestions_scope'][$index]['count_distributed']++;

                                $options['ingestions_scope'][$index]['recipes'][] = [
                                    'ingestion_id' => $ingestionId,
                                    'recipe_id' => $recipeId,
                                    'distribution_mode' => $options['distribution_mode'],
                                ];
                                $ingestionFound = true;
                            }
                        }
                    }
                }
            } elseif ($calcError && !$isFirstAdd) {
                # TODO нужно добавить проверку на наличие и удаление рецептов из списка "связанных рецептов"

                # get recipe by id
                $_recipe = Recipe::find($itemId);

                # check valid recipe data
                $validRecipeData = static::checkUserValidRecipe($itemId, $_user);


                $allIngestions = Ingestion::get();
                $allInactiveIngestions = $allIngestions->filter(
                    function ($item) {
                        if (!$item->active) {
                            return $item;
                        }
                    }
                );


                $validIngestions = []; // TODO: not used
                // to add getting all recipe + general ingestions from database (+ inactive) remove all inactive from database
                foreach ($_recipe->ingestions as $ingestion) {
                    # check active meal time (ingestion)
                    if (empty($ingestion->active)) {
                        continue;
                    }

                    $validIngestions[] = $ingestion->id; // TODO: not used


                    # check valid by KCal/KH range
                    $validByKCalKHData = static::validRecipeKcalKH($_recipe, $ingestion, $_user->dietdata);

                    # calc recipe
                    $recipeData = static::calcRecipe($_recipe, $ingestion, $_user->dietdata);

                    # recipeData optimization
                    $recipeData = static::calcRecipeOptimization($_recipe, $recipeData, $validRecipeData, $validByKCalKHData);

                    if (!empty($recipeData['errors'])) {
                        // recipe is invalid
                        $recipeDbRecord = UserRecipeCalculated::where('user_id', $_user->getKey())
                            ->where('recipe_id', $itemId)
                            ->where(
                                'ingestion_id',
                                $ingestion->id
                            )->first();
                        if ($recipeDbRecord) {
                            $recipeDbRecord->invalid = $recipeData['errors'];
                            $recipeDbRecord->recipe_data = $recipeData;
                            $recipeDbRecord->save();
                        }
                    } else {
                        //recipe is valid, we can add it
                        UserRecipeCalculated::updateOrCreate(
                            [
                                'user_id' => $_user->getKey(),
                                'recipe_id' => $itemId,
                                'ingestion_id' => $ingestion->id,
                            ],
                            [
                                'invalid' => $recipeData['errors'],
                                'recipe_data' => $recipeData
                            ]
                        )->touch();
                    }
                }

                // remove invalid ingestions
                $ingestionsForRemove = [];
                foreach ($allInactiveIngestions as $ingestion) {
                    $ingestionsForRemove[] = $ingestion->id;
                }

                //                foreach ($allIngestions as $ingestion) {
                //                    if (!in_array($ingestion->id, $validIngestions)) {
                //                        $ingestionsForRemove[] = $ingestion->id;
                //                    }
                //                }

                if (!empty($ingestionsForRemove)) {
                    $ingestionsForRemove = array_unique($ingestionsForRemove);
                    UserRecipeCalculated::where('user_id', $_user->getKey())->where(
                        'recipe_id',
                        $recipeId
                    )->whereIn('ingestion_id', $ingestionsForRemove)->delete();
                }

                // remove invalid ingestions end

                # replace recipe in user feed
                static::replaceRecipesInUserFeed($itemId, $_user->getKey(), $_user);
            } elseif ($calcError && $isFirstAdd) {
                $_errors['success'] = false;
                $_errors['message'] .= '<br>Recipe <b>#' . $recipeId . '</b> — can not be correctly recalculate for the first time!';
                continue;
            }
        }

        $_errors['options'] = $options;


        // refresh related recipes date
        UserService::syncRelatedRecipesCreateDate($_user);

        if (!empty($recipesForDelete)) {
            static::deleteRecipesByUser($recipesForDelete, $_user->getKey());
        }

        // cache cleanup
        $cacheCleaner = new ClearUserRecipeCache();
        $cacheCleaner->setUserId(intval($_user->getKey()));
        $cacheCleaner->handle();
        unset($cacheCleaner);

        return $_errors;
    }

    /**
     * method for validate recipe
     *
     * @param $source_recipe_id
     * @param User|null $user
     * @return array
     */
    public static function checkUserValidRecipe($source_recipe_id, User $user = null): array
    {
        $result = [
            'recipe_id' => $source_recipe_id,
            'valid' => true,
            'notices' => '',
        ];

        $currentUser = is_null($user) ? \Auth::user() : $user;
        $user_id = $currentUser->getKey();

        $excluded_recipes_ids = \Cache::get(CacheKeys::userExcludedRecipesIds($user_id));
        if (is_null($excluded_recipes_ids)) {

            $recipesByIngredients = \Cache::get(CacheKeys::recipesByIngredients());
            if (is_null($recipesByIngredients)) {
                // TODO:: optimize it, remove DB::table and replace with models

                // takes much more memory
                //                $ingredientsRecipes = DB::table('recipe_ingredients')
                //                    ->join('recipe_variable_ingredients', function (JoinClause $clause) {
                //                        $clause->on('recipe_ingredients.ingredient_id', '=', 'recipe_variable_ingredients.ingredient_id');
                //                    })
                //                    ->get([
                //                        'recipe_ingredients.ingredient_id AS ingredient_id',
                //                        'recipe_ingredients.recipe_id AS recipe_id_1',
                //                        'recipe_variable_ingredients.recipe_id AS recipe_id_2'
                //                    ])->toArray();
                //
                //                $recipesByIngredients = [];
                //                foreach($ingredientsRecipes as $item){
                //                    if (!isset($recipesByIngredients[$item->ingredient_id])) $recipesByIngredients[$item->ingredient_id] = [];
                //                    if (isset($item->recipe_id_1)) $recipesByIngredients[$item->ingredient_id][] = $item->recipe_id_1;
                //                    if (isset($item->recipe_id_2)) $recipesByIngredients[$item->ingredient_id][] = $item->recipe_id_2;
                //                }
                //                $staticIngredients = null;
                //                unset($staticIngredients);
                //
                //                dd($recipesByIngredients);


                $staticIngredients = RecipeIngredient::get(['ingredient_id', 'recipe_id']);

                $recipesByIngredients = [];
                foreach ($staticIngredients as $item) {
                    if (!isset($recipesByIngredients[$item->ingredient_id])) {
                        $recipesByIngredients[$item->ingredient_id] = [];
                    }
                    $recipesByIngredients[$item->ingredient_id][] = $item->recipe_id;
                }
                $staticIngredients = null;
                unset($staticIngredients);

                $variableIngredients = RecipeVariableIngredient::get(['ingredient_id', 'recipe_id']);
                foreach ($variableIngredients as $item) {
                    if (!isset($recipesByIngredients[$item->ingredient_id])) {
                        $recipesByIngredients[$item->ingredient_id] = [];
                    }
                    $recipesByIngredients[$item->ingredient_id][] = $item->recipe_id;
                }
                $variableIngredients = null;
                unset($variableIngredients);

                \Cache::put(CacheKeys::recipesByIngredients(), $recipesByIngredients, config('cache.lifetime_1m'));
            }

            // TODO:: think if excluded array is empty
            $excludedIngredientsIds = $currentUser->prohibitedIngredients()->pluck('ingredients.id')->toArray();

            $excluded_recipes_ids = [];
            foreach ($excludedIngredientsIds as $ingredientId) {
                if (isset($recipesByIngredients[$ingredientId])) {
                    $excluded_recipes_ids = array_merge($excluded_recipes_ids, $recipesByIngredients[$ingredientId]);
                }
            }

            // getting user recipes exclusions
            $excludedRecipesIdsByUserExclusion = $currentUser->excludedRecipes()->pluck('recipe_id')->toArray();
            if (!empty($excludedRecipesIdsByUserExclusion)) {
                $excluded_recipes_ids = array_merge($excluded_recipes_ids, $excludedRecipesIdsByUserExclusion);
            }
            $excluded_recipes_ids = array_unique($excluded_recipes_ids);

            \Cache::put(CacheKeys::userExcludedRecipesIds($user_id), $excluded_recipes_ids, config('cache.lifetime_1m'));
        }
        // TODO:: remove it

        if ($result['valid']) {
            if (in_array($source_recipe_id, $excluded_recipes_ids)) {
                $result['notices'] .= 'excluded by ingredients; ';
                $result['valid'] = false;
            }
        }

        return $result;
    }

    /**
     * replace Recipes In User Feed
     * TODO: REFACTOR REQUIRED, ASAP!!!
     * @param array $recipeIds
     * @param null $userId
     * @param User $user
     * @param Carbon datetime $startDate, date from which to make replace
     */
    // TODO:: add ingestino id for replacement
    // TODO:: $user, $user_id... to much
    public static function replaceRecipesInUserFeed($recipeIds = [], $userId = null, $user = null, $startDate = null)
    {
        $results = [];
        if (is_null($userId)) {
            return;
        }

        // TODO:: cover case when recipe became invalid and was replaced!!!

        $_user = User::find($userId);

        $excluded_recipes_ids_by_user_exclusion = [];
        if (!empty($_user->excluded_recipes)) {
            $excluded_recipes_ids_by_user_exclusion = $_user->excluded_recipes->toArray();
        }

        # converting to an array
        $recipeIds = (is_array($recipeIds)) ? $recipeIds : [$recipeIds];


        // dates range for recipes replacement inside meal feed
        if (empty($startDate)) $startDate = \Carbon\Carbon::now()->startOfDay();

        $userRecipe = UserRecipe::where('user_id', $userId)
            ->whereIn('recipe_id', $recipeIds)
            ->where('meal_date', '>=', $startDate)
            ->where(function ($query) {
                $query->where('custom_recipe_id', null)
                    ->orWhereNull('custom_recipe_id');
            })
            ->get();

        // TODO:: get only non-draft recipes
        $userRecipesIdExpectedRequested = $_user->allRecipes()
            ->whereNotIn('recipe_id', $recipeIds)
            ->where('recipes.status', RecipeStatusEnum::ACTIVE->value)
            ->pluck('recipes.id')
            ->toArray();

        if (is_null($userRecipe) || $userRecipe->count() == 0) {
            return;
        }

        # get ingestions
        $ingestions = Ingestion::where('active', true)->get();

        // TODO:: try to find recipe from related FIRSTLY!!!!
        // TODO:: remove all related recipes if exists valid


        // when recipe become invalid...

        // if we are doing replacement -> first priority for related recipes to current,
        // if not exists in related scope check whole user recipes scope

        $recipeDate = $recipeDateBackUp = [];
        // adding recipes from general list ( which aren't 100% related )
        foreach ($ingestions as $ingestion) {
            $calcRecipeIds = UserRecipeCalculated::where('user_id', $userId)
                ->whereNotNull('recipe_id')
                ->whereIn('recipe_id', $userRecipesIdExpectedRequested)
                ->where('invalid', 0)
                ->where('ingestion_id', $ingestion->id)
                ->whereNotIn('recipe_id', $excluded_recipes_ids_by_user_exclusion)
                ->pluck('recipe_id')
                ->toArray();

            if (empty($calcRecipeIds)) {
                continue;
            }

            $recipeDateBackUp[$ingestion->id] = $recipeDate[$ingestion->id] = $calcRecipeIds;
        }


        $mappingRecipeReplacementTable = [];

        foreach ($userRecipe as $recipe) {
            if (!empty($mappingRecipeReplacementTable[$recipe['recipe_id']])) {
                // if recipe was replaced previously we skip that one
                continue;
            }

            $recipeDate[$recipe->ingestion_id] = (!empty($recipeDateBackUp[$recipe->ingestion_id])) ? $recipeDateBackUp[$recipe->ingestion_id] : [];

            // if exists other recipes - shuffle array
            if (!empty($recipeDate[$recipe->ingestion_id])) {
                shuffle($recipeDate[$recipe->ingestion_id]);
            }


            // if exists related recipes - adding them in the head of array
            $recipeObject = Recipe::find($recipe['recipe_id']);
            if (empty($recipeObject)) {
                continue;
            }

            // TODO:: get only undrafted recipes
            $_relatedRecipes = !empty($recipeObject->related_recipes) ? $recipeObject->related_recipes : [];

            if (!empty($_relatedRecipes)) {
                $_relatedRecipes = Recipe::isActive()
                    ->whereIn('id', $_relatedRecipes)
                    ->whereNotIn('id', $recipeIds)
                    ->pluck('id')
                    ->toArray();
            }

            foreach ($_relatedRecipes as $relatedRecipeId) {
                $relatedRecipeObject = Recipe::find($relatedRecipeId);
                if (empty($relatedRecipeObject)) {
                    continue;
                }
                $existsRecipeIngestion = $relatedRecipeObject->ingestions()->where('ingestions.id', $recipe->ingestion_id)->count();

                if ($existsRecipeIngestion > 0) {
                    if (empty($recipeDate[$recipe->ingestion_id])) {
                        $recipeDate[$recipe->ingestion_id] = [];
                    }
                    array_unshift($recipeDate[$recipe->ingestion_id], intval($relatedRecipeId));
                }
            }


            if (!key_exists($recipe->ingestion_id, $recipeDate) || empty($recipeDate[$recipe->ingestion_id])) {
                continue;
            }

            # randomize recipe Id by ingestion

            if (count($recipeDate[$recipe->ingestion_id]) == 0) {
                $recipeDate[$recipe->ingestion_id] = $recipeDateBackUp[$recipe->ingestion_id];
            }
            // current recipe validation, is current recipe valid for replace
            do {
                $recipeValidForReplace = false;
                $validResult = false;

                if (!empty($recipeDate[$recipe->ingestion_id])) {
                    $arrayKeys = array_keys($recipeDate[$recipe->ingestion_id]);
                    $randomRecipeIndex = reset($arrayKeys);

                    $randomRecipeId = $recipeDate[$recipe->ingestion_id][$randomRecipeIndex];
                    // unset recipe from array, preparation for next step
                    unset($recipeDate[$recipe->ingestion_id][$randomRecipeIndex]);


                    $validRecipeData = static::checkUserValidRecipe($randomRecipeId, $user);
                    if ((!empty($validRecipeData)) && $validRecipeData['valid']) {
                        $_recipe = Recipe::query()->find($randomRecipeId);
                        if (!is_null($_recipe) && (!empty($_recipe->ingestions)) && ($_recipe->ingestions->count() >= 0)) {
                            $validResult = static::_validRecipeKcalKH($_recipe, $user->dietdata);
                        } else {
                            $validResult = ['errors' => true];
                        }
                    }
                    if ($validRecipeData['valid'] && ((!empty($validResult)) && empty($validResult['errors']) && (isset($validResult['ingestions'][$recipe->ingestion_id])) && ($validResult['ingestions'][$recipe->ingestion_id] === false))) {
                        $recipeValidForReplace = true;
                    }
                }
            } while (!$recipeValidForReplace && count($recipeDate[$recipe->ingestion_id]));

            if ($recipeValidForReplace) {

                // TODO:: shopping list, replace in lists replacable recipe!!!!
                \DB::table('recipes_to_users')->where(
                    [
                        ['user_id', $userId],
                        ['recipe_id', $recipe->recipe_id],
                        ['meal_date', '>', $startDate]
                    ]
                )
                    ->update(
                        [
                            'recipe_id' => $randomRecipeId,
                            'custom_recipe_id' => null
                        ]
                    );


                $_user = User::findOrFail($userId);
                // PROBABLY place of update created_at
                $_user->allRecipes()->syncWithoutDetaching(['recipe_id' => $randomRecipeId]);


                //\Log::info('REPLACEMENT FOR USER '. $userId .' RECIPE '.$recipe->recipe_id.' REPLACED TO '.$randomRecipeId);

                $mappingRecipeReplacementTable[$recipe->recipe_id] = $randomRecipeId;

                $results['actions'][] = 'Recipe ID: ' . $recipe->recipe_id . ' replaced to ' . $randomRecipeId;
                // This one ir required in order to add new recipe to shopping list
                $results['new_recipe_data'] = \DB::table('recipes_to_users')->where(
                    [
                        ['user_id', $userId],
                        ['recipe_id', $randomRecipeId],
                        ['meal_date', '>', $startDate]
                    ]
                )->first();
                //updating all recipes scope
                /*

                $recipeObject = Recipe::find($recipe['recipe_id']);
                $_relatedRecipes = !empty($recipeObject->related_recipes) ? $recipeObject->related_recipes : [];
                if (!empty($_relatedRecipes)){
                    //////////////////////////////////////////////////////////////////////////////////////////////
                }
                */
            } else {
                $userRecipesMealPlanData = UserRecipe::where('user_id', $userId)
                    ->where('recipe_id', $recipe->recipe_id)
                    ->where('meal_date', '>=', $startDate)
                    ->where(function ($query) {
                        $query->where('custom_recipe_id', null)
                            ->orWhereNull('custom_recipe_id');
                    })
                    ->orderBy('meal_date')
                    ->get();
                $rResults = [];
                foreach ($userRecipesMealPlanData as $r) {
                    $rResults[] = \Carbon\Carbon::Parse($r->meal_date)->format('Y-m-d') . ' ' . $r->meal_time;
                }
                $rResultsString = implode(', ', $rResults);

                if (!empty($rResults)) {
                    $results['actions'][] = 'Recipe ID: ' . $recipe->recipe_id . ' is not possible to replace, please add manually recipe to ' . $rResultsString;
                }
            }
        }

        // TODO:: need to implement
        // whole pack of related recipes are invalid.... -> leave single recipe from scope
        // at least one recipe of related score is valid -> leave only valid recipe

        return $results;
    }

    /**
     * wrapper to function "validRecipeKcalKH"
     *
     * @param $recipe
     * @param null $dietData
     * @return array
     */
    public static function _validRecipeKcalKH($recipe, $dietData = null)
    {
        # set default result
        $result = [
            'ingestions' => [],
            'errors' => true,
            'message' => null
        ];

        foreach ($recipe->ingestions as $ingestion) {
            # check active meal time (ingestion)
            if (empty($ingestion->active)) {
                continue;
            }

            # set default result
            $result['ingestions'][$ingestion->id] = true;

            # check valid by KCal/KH range
            $validByKCalKHData = static::validRecipeKcalKH($recipe, $ingestion, $dietData);

            if (!empty($validByKCalKHData) && key_exists('errors', $validByKCalKHData) && $validByKCalKHData['errors']) {
                $result['message'] .= '<br>Recipe <b>#' . $recipe->id . ' (' . $ingestion->title . ')</b> — Range ERROR! => ' . $validByKCalKHData['notices'];
            } else {
                $result['errors'] = false;
                $result['ingestions'][$ingestion->id] = false;
            }
        }

        return $result;
    }

    //TODO:: refactor that

    /**
     * Validate recipe for ingestion KCal and KH
     *
     * @param      $recipe
     * @param      $ingestion
     * @param null $diet_data
     *
     * @return array
     */
    public static function validRecipeKcalKH($recipe, $ingestion, $diet_data = null)
    {
        $result = [
            'valid' => true,
            'notices' => '',
            'errors' => false,
        ];

        // check user dietData
        if (empty($diet_data)) {
            $user = \Auth::user();
            if (isset($user) && isset($user->dietdata)) {
                $diet_data = $user->dietdata;
            }
        }

        $ingestion_key = $ingestion->key;

        if (!is_null($ingestion_key) && !empty($diet_data['ingestion'][$ingestion_key])) {
            $nutriens = $diet_data['ingestion'][$ingestion_key];

            if ((!empty($recipe->min_kh)) || (!empty($recipe->max_kh))) {
                $KH = floatval($nutriens['KH']);
                if ((!empty($recipe->min_kh)) && ($KH < $recipe->min_kh)) {
                    $result['errors'] = true;
                    $result['valid'] = false;
                    $result['notices'] .= 'invalid by KH, min recipe KH = ' . $recipe->min_kh . ' need KH = ' . $KH . '; ';
                }

                // trick
                // recipe with KH 5-10, must be valid for 5.0 - 10.999999
                if ((!empty($recipe->max_kh)) && ($KH >= ($recipe->max_kh + 1))) {
                    $result['errors'] = true;
                    $result['valid'] = false;
                    $result['notices'] .= 'invalid by KH, max recipe KH = ' . $recipe->max_kh . ' need KH = ' . $KH . '; ';
                }
            }


            if ((!empty($recipe->min_kcal)) || (!empty($recipe->max_kcal))) {
                $KCal = floatval($nutriens['Kcal']);
                if ((!empty($recipe->min_kcal)) && ($KCal < $recipe->min_kcal)) {
                    $result['errors'] = true;
                    $result['valid'] = false;
                    $result['notices'] .= 'invalid by KCal, min recipe KCal = ' . $recipe->min_kcal . ' need KCal = ' . $KCal . '; ';
                }

                if ((!empty($recipe->max_kcal)) && ($KCal > $recipe->max_kcal)) {
                    $result['errors'] = true;
                    $result['valid'] = false;
                    $result['notices'] .= 'invalid by KCal, max recipe KCal = ' . $recipe->max_kcal . ' need KCal = ' . $KCal . '; ';
                }
            }
        }

        return $result;
    }

    /**
     * calc Recipe Optimization
     *
     * @param $_recipe
     * @param array $_recipeData
     * @param array $validRecipeData
     * @param array $validByKCalKHData
     * @return array
     */
    public static function calcRecipeOptimization($_recipe, $_recipeData = [], $validRecipeData = [], $validByKCalKHData = [])
    {
        # result variable
        $resultData = array();

        if (!empty($_recipeData)) {
            # ingredients Type array
            $_ingredientsType = ['ingradients' => 'fixed', 'ingradients_variable' => 'variable'];
            foreach ($_ingredientsType as $key => $type) {
                if (key_exists($key, $_recipeData)) {
                    foreach ($_recipeData[$key] as $item) {
                        $ingredient = Ingredient::findOrFail($item['id'])->load('unit');
                        $resultData['ingredients'][] = [
                            'id' => $ingredient->id,
                            'type' => $type,
                            'amount' => intval($item['amount'])
                        ];
                    }
                }
            }

            $keyIgnored = array(
                'ingradients',
                'ingradients_variable',
                'need_more_nutrients',
                'need_nutrients',
                'date',
                'ingestion'
            );
            foreach ($_recipeData as $key => $item) {
                if (in_array($key, $keyIgnored)) {
                    continue;
                }
                $resultData[$key] = $item;
            }
        } else {
            $_recipeData = $_recipe->load('ingredients', 'variableIngredients');

            # ingredients Type array
            $_ingredientsType = ['ingredients' => 'fixed', 'variableIngredients' => 'variable'];

            foreach ($_ingredientsType as $key => $type) {
                foreach ($_recipeData->{$key} as $item) {
                    $ingredient = $item->load('unit');
                    $resultData['ingredients'][] = [
                        'id' => $ingredient->id,
                        'type' => $type,
                        'amount' => $item->pivot->amount,
                    ];
                }
            }
        }

        $resultData['errors'] = false;
        $resultData['notices'] = [];

        if (!empty($validRecipeData) && key_exists('valid', $validRecipeData) && !$validRecipeData['valid']) {
            $resultData['errors'] = true;
            $resultData['notices'][] = $validRecipeData['notices'];
        }

        if (!empty($validByKCalKHData) && key_exists('valid', $validByKCalKHData) && !$validByKCalKHData['valid']) {
            $resultData['errors'] = true;
            $resultData['notices'][] = $validByKCalKHData['notices'];
        }

        if (!empty($_recipeData) && key_exists('errors', $_recipeData) && $_recipeData['errors']) {
            $resultData['errors'] = true;
            $resultData['notices'][] = $_recipeData['notices'];
        }

        return $resultData;
    }

    /**
     * delete Recipes by User
     *
     * @param array $recipeIds
     * @param null $userId
     */
    public static function deleteRecipesByUser($recipeIds = [], $userId = null)
    {
        if (!is_null($userId)) {
            # converting to an array
            $recipeIds = (is_array($recipeIds)) ? $recipeIds : [$recipeIds];

            foreach ($recipeIds as $recipeId) {
                # get recipe by id
                $recipe = Recipe::findOrFail($recipeId);

                # get ingestion Ids
                $ingestionIds = $recipe->ingestions->pluck('ingestions.id')->toArray();

                UserRecipeCalculated::where('user_id', $userId)
                    ->where('recipe_id', $recipeId)
                    ->whereIn('ingestion_id', $ingestionIds)
                    ->delete();

                \DB::table('user_recipe')->where(
                    [
                        ['user_id', $userId],
                        ['recipe_id', $recipeId],
                    ]
                )->delete();

                \DB::table('recipes_to_users')->where(
                    [
                        ['user_id', $userId],
                        ['recipe_id', $recipeId],
                    ]
                )
                    ->whereIn('ingestion_id', $ingestionIds)
                    ->delete();
            }
        }
    }

    private static function shuffle_assoc($my_array)
    {
        $keys = array_keys($my_array);

        shuffle($keys);

        foreach ($keys as $key) {
            $new[$key] = $my_array[$key];
        }

        $my_array = $new;

        return $my_array;
    }

    private static function internalCheckDistributionTypeIngestions($options, &$count, $amountRecipes)
    {
        // proper temporary key
        $allowedByIngestionsRestictions = false;
        if (!empty($options['distributionType']) && $options['distributionType'] == 'ingestions') {
            foreach ($options['ingestions_scope'] as $ingestionScopes) {
                if ($ingestionScopes['count_distributed'] < $ingestionScopes['count_requested']) {
                    $allowedByIngestionsRestictions = true;
                }
            }
            if (!$allowedByIngestionsRestictions) {
                $count = $amountRecipes;
            }
        }
    }

    /**
     * generate recipe to Subscribe
     *
     * @param $_user
     * @return array
     */
    public static function _generate2subscription($_user)
    {
        $excluded_recipes_ids_by_user_exclusion = [];
        if (!empty($_user->excluded_recipes)) {
            $excluded_recipes_ids_by_user_exclusion = $_user->excluded_recipes->toArray();
        }


        # get ingestions
        $ingestions = Ingestion::where('active', true)->get();

        # recipe Id collections
        $recipeDate = array();
        foreach ($ingestions as $ingestion) {
            /*$recipeIds = UserRecipeCalculated::where('user_id', $_user->id)
                ->whereNotNull('recipe_id')
                ->where('invalid', 0)
                ->where('ingestion_id', $ingestion->id)
                ->pluck('recipe_id')
                ->toArray();*/

            $recipeIds = $_user->allRecipes()
                ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
                ->leftJoin('ingestions', 'user_recipe_calculated.ingestion_id', '=', 'ingestions.id')
                ->select(
                    'recipes.*',
                    'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                    'user_recipe_calculated.recipe_data AS calc_recipe_data',
                    'user_recipe_calculated.invalid AS calc_invalid',
                    'user_recipe_calculated.created_at AS calc_created_at',
                    'ingestions.key AS meal_time'
                )
                ->where('user_recipe_calculated.user_id', $_user->id)
                ->where('user_recipe_calculated.ingestion_id', $ingestion->id)
                ->where('user_recipe_calculated.invalid', '=', 0)
                ->whereNotIn('recipes.id', $excluded_recipes_ids_by_user_exclusion)
                ->groupBy('recipes.id')
                ->pluck('recipes.id')
                ->toArray();

            if (empty($recipeIds)) {
                continue;
            }

            $recipeDate[$ingestion->id] = $recipeIds;
        }

        # check exist user recipes
        if (empty($recipeDate)) {
            return ['success' => false, 'message' => 'User has no recipes!'];
        }


        $subscriptionStartsAt = $_user->subscription?->created_at;

        if (empty($subscriptionStartsAt)) {
            $lastChargebeeSubscription = $_user->getLastChargebeeSubscriptionItem();
            if (!empty($lastChargebeeSubscription)) {
                $subscriptionStartsAt = $lastChargebeeSubscription?->started_at;
            }
        }

        $dateNow = Carbon::now();

        if (!empty($subscriptionStartsAt)) {
            $startDate = $subscriptionStartsAt;
        } else {
            $startDate = Carbon::now();
        }

        // shift meal plan start date not more 30 days before today (resources saving)
        if ($startDate->diffInDays($dateNow, false) >= 30) {
            $startDate = Carbon::now()->subMonth()->startOfDay();
        }

        $endDate = Carbon::now()->startOfMonth()->addMonth();//->addWeeks(2);

        if ($endDate->format('D') != 'Mon') {
            $endDate = $endDate->startOfMonth()->modify('first Monday');
        }
        $endDate->addWeeks(2);

        // TODO:: review that subscription end date uses for last date
//        if (!is_null($_user->subscription?->ends_at) && ($_user->subscription?->ends_at < $endDate)) {
//            $endDate = Carbon::parse($_user->subscription?->ends_at);
//        }

        # check different day
        if ($endDate->diffInDays($startDate, false) >= 0) {
            return ['success' => false, 'message' => 'Diff In Days error!'];
        }


        // removing all recipes from meal plan
        # delete recipes from current meal plan
        UserRecipe::where('user_id', $_user->id)->delete();
        // cleanup shopping list
        $_user->shoppingList()->delete();

        while ($startDate < $endDate) {
            # recipe from user create
            $preparedData = array();

            foreach ($ingestions as $ingestion) {
                if (!key_exists($ingestion->id, $recipeDate)) {
                    continue;
                }

                # randomize recipe Id by ingestion
                $randomRecipeId = $recipeDate[$ingestion->id][array_rand($recipeDate[$ingestion->id])];

                $preparedData[] = [
                    'user_id' => $_user->id,
                    'recipe_id' => $randomRecipeId,
                    'custom_recipe_id' => null,
                    'original_recipe_id' => $randomRecipeId,
                    'meal_date' => $startDate->format('Y-m-d 00:00:00'),
                    'meal_time' => $ingestion->key,
                    'ingestion_id' => $ingestion->id,
                ];
            }

            UserRecipe::insert($preparedData);

            $startDate->addDays(1);
        }

        return ['success' => true, 'message' => null];
    }

    /**
     *  Internal method
     * @param $_user
     * @param $_recipe
     * @param $ingestion
     * @param $recipeData
     * @return void
     */
    public static function _calcSuitableRecipe2users_internalUserRecipeCalculated($_user, $_recipe, $ingestion, $recipeData, $invalid = null)
    {
        if (is_null($invalid)) {
            $invalid = boolval($recipeData['errors']);
        }
        $recipeCalc = UserRecipeCalculated::updateOrCreate(
            [
                'user_id' => $_user->getKey(),
                'recipe_id' => $_recipe->id,
                'ingestion_id' => $ingestion->id,
            ],
            [
                'invalid' => $invalid,
                'recipe_data' => $recipeData
            ]
        );
        $recipeCalc->touch();
        $recipeCalc = null;
    }


    /**
     * Internal method
     * @param $_recipe
     * @param $_user
     * @param $allIngestions
     * @param $validRecipeData
     * @return void
     */
    public static function _calcSuitableRecipe2users_internalSaveRecipeData($_recipe, $_user, $allIngestions, $validRecipeData, $invalid = null)
    {
        // issue when recipes
        $ingestionIds = $_recipe->ingestions()->whereActive(true)->pluck('ingestions.id')->toArray();
        foreach ($ingestionIds as $ingestionId) {
            $ingestion = $allIngestions->find($ingestionId);
            $validByKCalKHData = static::validRecipeKcalKH($_recipe, $ingestion, $_user->dietdata);
            $recipeData = static::calcRecipe($_recipe, $ingestion, $_user->dietdata);
            $recipeData = static::calcRecipeOptimization(
                $_recipe,
                $recipeData,
                $validRecipeData,
                $validByKCalKHData
            );

            self::_calcSuitableRecipe2users_internalUserRecipeCalculated($_user, $_recipe, $ingestion, $recipeData, $invalid);
        }
    }

    /**
     * Calculate all suitable recipes to users
     * TODO: used only once and return array which is not used anyway? why?
     * @param User $_user
     * @param array $_recipeIds
     * @param null $preliminaryCalcData
     * @param string $relatedJobType
     * @param string $relatedJobHash
     * @param bool $couldBeInterrupted
     * @return array
     */
    public static function calcSuitableRecipe2users(
        User  $_user,
        array $_recipeIds,
              $preliminaryCalcData = null,
              $relatedJobType = null,
              $relatedJobHash = null,
              $couldBeInterrupted = true
    ): array
    {

        DB::disableQueryLog();
        # result array
        $result = array();

        $allInactiveIngestions = Ingestion::whereActive(false)->pluck('id')->toArray();
        $allIngestions = Ingestion::get();

        // fix WEB-130
        $existRecipeIds = $_user
            ->allRecipes()
//            ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
//            ->where('user_recipe_calculated.user_id', $_user->getKey())
            ->groupBy('recipes.id')
            ->pluck('related_recipes', 'recipes.id')
            ->toArray();
        $existRecipeIds = array_keys($existRecipeIds);
        // fix WEB-130


        $recipes = Recipe::isActive()->with(['ingredients', 'variableIngredients', 'ingestions'])->whereIntegerInRaw('id', $_recipeIds)->get();
        $lastRecipeCalculatedRecords = UserRecipeCalculated::where('user_id', $_user->getKey())
            ->whereIn('recipe_id', $_recipeIds)
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->pluck('updated_at', 'recipe_id');

        // fix for one day before recipes were calculated,
        // prevent problem with app/Jobs/ActionsAfterChangingFormular.php::56
        // where update_at set with "valid=null"

        $expectedPreliminaryDate = false;
        if (!empty($preliminaryCalcData) && !empty($preliminaryCalcData['updated_at'])) {
            $expectedPreliminaryDate = Carbon::parse($preliminaryCalcData['updated_at'])->subDays(1)->startOfDay();
        }
        $questionnaireUpdated_at = 0;
        $isQuestionnaireExist = $_user->isQuestionnaireExist();
        if ($isQuestionnaireExist) {
            $questionnaireUpdated_at = $_user->questionnaire()->first()->updated_at;
        }


        foreach ($recipes as $_recipe) {
            if ($couldBeInterrupted && self::staticVerifyOrFinishJob($relatedJobType, $_user->getKey(), $relatedJobHash) === false) {
                break;
            }

            # clone recipe Id
            $recipeId = intval($_recipe->id);

            # ===========================
            # check Recipe valid for user
            # ===========================

            if (!empty($lastRecipeCalculatedRecords[$recipeId])) {
                $lastRecipeCalculatedRecordUpdatedAt = $lastRecipeCalculatedRecords[$recipeId];
            } else {
                $lastRecipeCalculatedRecordUpdatedAt = false;
            }

            $recipeExistsInUserMealPlan = in_array($recipeId, $existRecipeIds);

            if (
                !empty($lastRecipeCalculatedRecordUpdatedAt)
                &&
                $isQuestionnaireExist
                &&
                $questionnaireUpdated_at < $lastRecipeCalculatedRecordUpdatedAt
                &&
                $_user->updated_at < $lastRecipeCalculatedRecordUpdatedAt
                &&
                $_recipe->updated_at < $lastRecipeCalculatedRecordUpdatedAt

            ) {
                array_push($result, $recipeId);
                continue;
            }
            // recipe not exists in calculation table, probably it's invalid

            // if preliminary calculation was before and recipe isset inside invalid area, skip invalid recipe from recalculation
            elseif (
                !$recipeExistsInUserMealPlan
                &&
                empty($lastRecipeCalculatedRecords[$recipeId])
                &&
                !empty($preliminaryCalcData)
                &&
                isset($preliminaryCalcData['updated_at'])
                &&
                !empty($preliminaryCalcData['invalid'])
                &&
                in_array($recipeId, $preliminaryCalcData['invalid'])
                &&
                $isQuestionnaireExist
                &&
                $questionnaireUpdated_at < $expectedPreliminaryDate
                &&
                $_user->updated_at < $expectedPreliminaryDate
                &&
                $_recipe->updated_at < $expectedPreliminaryDate
            ) {
                continue;
            }
            # check valid recipe data
            $validRecipeData = static::checkUserValidRecipe($recipeId, $_user);

            if (!$validRecipeData['valid']) {
                $calcRecipes = UserRecipeCalculated::where('user_id', $_user->getKey())->whereNotNull(
                    'recipe_id'
                )->where('recipe_id', $recipeId)->get();

                foreach ($calcRecipes as $calcRecipe) {
                    $recipeData = $calcRecipe->recipe_data;

                    $recipeData['errors'] = true;
                    $recipeData['notices'] = [$validRecipeData['notices']];

                    $calcRecipe->invalid = true;
                    $calcRecipe->recipe_data = $recipeData;
                    $calcRecipe->save();

                    # update timestamps update_at
                    $calcRecipe->touch();
                }

                // fix WEB-130
                if ($recipeExistsInUserMealPlan) {
                    // trick with attribute invalid
                    self::_calcSuitableRecipe2users_internalSaveRecipeData($_recipe, $_user, $allIngestions, $validRecipeData, true);
                }
                // fix WEB-130
                $calcRecipes = null;
                continue;
            }
            # ==========================
            # check Recipe valid KCal/KH
            # ==========================


            # check recipe ingestion
            if ($_recipe->ingestions->where('active', '1')->count() === 0) {
                continue;
            }

            $validResult = static::_validRecipeKcalKH($_recipe, $_user->dietdata);

            if ($validResult['errors']) {
                $invalidIngestionIds = [];
                foreach ($validResult['ingestions'] as $ingestionId => $ingestionInvalid) {
                    if ($ingestionInvalid) {
                        $invalidIngestionIds[] = $ingestionId;
                    }
                }

                $calcRecipes = UserRecipeCalculated::where('user_id', $_user->getKey())
                    ->whereNotNull('recipe_id')
                    ->where('recipe_id', $recipeId)
                    ->whereIn('ingestion_id', $invalidIngestionIds)
                    ->get();

                foreach ($calcRecipes as $calcRecipe) {
                    $recipeData = $calcRecipe->recipe_data;

                    $recipeData['errors'] = true;
                    $recipeData['notices'] = [$validResult['message']];

                    $calcRecipe->invalid = true;
                    $calcRecipe->recipe_data = $recipeData;
                    $calcRecipe->save();

                    # update timestamps update_at
                    $calcRecipe->touch();
                }

                // fix WEB-130
                if ($recipeExistsInUserMealPlan) {
                    self::_calcSuitableRecipe2users_internalSaveRecipeData($_recipe, $_user, $allIngestions, $validRecipeData);
                }
                continue;
            } else {
                // fix WEB-130
                if ($recipeExistsInUserMealPlan) {
                    self::_calcSuitableRecipe2users_internalSaveRecipeData($_recipe, $_user, $allIngestions, $validRecipeData);
                }
                // fix WEB-130
            }
            # ===============================
            # calculate recipe for meal times
            # ===============================


            $validIngestions = []; // TODO: not used

            # flag calc Error
            $calcError = true;

            foreach ($validResult['ingestions'] as $ingestionId => $ingestionError) {
                if ($ingestionError) {
                    continue;
                }
                // TODO:: optimize it
                # get ingestion
                $ingestion = $allIngestions->find($ingestionId);

                if (empty($ingestion->active)) {
                    continue;
                }

                $recipeData = static::calcRecipe($_recipe, $ingestion, $_user->dietdata);

                $validIngestions[] = $ingestion->id; // TODO: not used

                if (!is_null($recipeData) && !$recipeData['errors']) {
                    # flag calc Error
                    $calcError = false;

                    # recipeData optimization
                    $recipeData = static::calcRecipeOptimization($_recipe, $recipeData, [], []);

                    self::_calcSuitableRecipe2users_internalUserRecipeCalculated($_user, $_recipe, $ingestion, $recipeData);
                }
            }

            # get ingestions

            // remove invalid ingestions
            if (!empty($allInactiveIngestions)) {
                UserRecipeCalculated::where('user_id', $_user->getKey())->where('recipe_id', $recipeId)->whereIn(
                    'ingestion_id',
                    $allInactiveIngestions
                )->delete();
            }

            // remove invalid ingestions end

            # check calc Error and add recipe to user
            if (!$calcError && !in_array($recipeId, $result)) {
                array_push($result, $recipeId);
            }
        }

        // refresh related recipes date
        UserService::syncRelatedRecipesCreateDate($_user, $_recipeIds);

        // cache cleanup
        app(ClearUserRecipeCache::class, ['userId' => (int)$_user->getKey()])->handle();

        // TODO:: cleanup invalid recipes which are not exists in users mealplan, recipes scope

        return $result;
    }

    /**
     * get Related Recipe Groups
     *
     * @return array
     */
    public static function getRelatedRecipeGroups()
    {
        # get related recipe
        $relatedRecipeGroups = Recipe::isActive()->pluck('related_recipes', 'id')->toArray();

        $arrKeys = array_keys($relatedRecipeGroups);

        foreach ($relatedRecipeGroups as $recipeId => $group) {
            if (!in_array($recipeId, $arrKeys)) {
                continue;
            }

            if (empty($group)) {
                $relatedRecipeGroups[$recipeId] = array(strval($recipeId));
            } else {
                foreach ($group as $item) {
                    unset($relatedRecipeGroups[$item]);

                    if (($key = array_search($item, $arrKeys)) !== false) {
                        unset($arrKeys[$key]);
                    }
                }

                $relatedRecipeGroups[$recipeId][] = strval($recipeId);
            }
        }

        return $relatedRecipeGroups;
    }
}
