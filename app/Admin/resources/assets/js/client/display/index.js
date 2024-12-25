import $ from 'jquery';
import {initUserSelection} from './userSelection.js';
import {initFilters} from './filters.js';
import {addRecipes2selectUsers, submitAdding} from './recipeSelection.js';
import {addRandomizeRecipes2selectUsers} from './randomizeRecipes.js';

window.$SubmitAddRecipes = null;
if (window.FoodPunk?.static?.hideRecipesRandomizer) {
    window.$SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
}

$(document).ready(function () {
    const userDataTable = initUserSelection();

    initFilters(userDataTable);

    window.FoodPunk = window.FoodPunk || {};
    window.FoodPunk.functions = {
        addRecipes2selectUsers,
        submitAdding,
        addRandomizeRecipes2selectUsers,
    };
});
