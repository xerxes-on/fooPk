import $ from 'jquery';

import {setupValidations} from './validations.js';
import {initCalculationStatusCheck} from './jobStatus.js';
import {initChallenges} from './challenges.js';
import {initSubscriptions} from './subscriptions.js';
import {initRecipes} from './recipes.js';
import {initDeposit} from "./deposit";
import initFormular from "./formular";

if (window.FoodPunk.pageInfo.hideRecipesRandomizer) {
    window.$SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
}

$(document).ready(function () {
    setupValidations();
    initCalculationStatusCheck();
    initChallenges();
    initSubscriptions();
    initRecipes();
    initDeposit();
    initFormular();

    window.FoodPunk.functions = window.FoodPunk.functions || {};
});
