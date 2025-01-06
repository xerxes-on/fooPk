import $ from 'jquery';

import {initCalculationStatusCheck} from './jobStatus';
import {initDeposit} from "./deposit";
import initQuestionnaire from "./questionnaire";
import {initNutrientsCalculations} from './nutrientsCalculation';
import {initRecipes} from './recipes';
import {initSubscriptions} from './subscriptions';
import {initCourses} from './courses';

if (window.FoodPunk.pageInfo.hideRecipesRandomizer) {
    window.$SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
}

$(document).ready(function () {
    window.FoodPunk.functions = window.FoodPunk.functions || {};

    initCalculationStatusCheck();
    initDeposit();
    initQuestionnaire();
    initNutrientsCalculations();
    initRecipes();
    initSubscriptions();
    initCourses();
});
