// Main initializer for user and recipe selection. Sets up the user DataTable, filters, and recipe-related actions like selection and randomization
import $ from 'jquery';
import {initUserSelection} from './userSelection';
import {initFilters} from './filters';
import {addRecipes2selectUsers, submitAdding} from './recipeSelection';
import {addRandomizeRecipes2selectUsers} from './randomizeRecipes';

window.$SubmitAddRecipes = null;
if (window.FoodPunk?.pageInfo?.hideRecipesRandomizer) {
    window.FoodPunk.pageInfo.$SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
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
