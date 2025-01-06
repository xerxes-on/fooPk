/**
 * Main initializer for recipe functionality.
 *
 * Cleans up localStorage, binds DOM events, registers recipe-related functions to window.FoodPunk.functions,
 * and initializes recipe DataTables
 */
import {selectedPopupRecipesStorage, selectedRecipesStorage} from './recipes/recipesConst';
import {renderCounterToolbarData} from './recipes/renderCounterToolbarData';
import {addRecipes} from './recipes/addRecipes';
import {deleteAllRecipes, deleteRecipe} from './recipes/deleteRecipes';
import {deleteSelectedRecipes, toggleSelect} from './recipes/deleteSelectedRecipes';
import {recalculateUserRecipes} from './recipes/recalculations';
import {addRandomizeRecipes} from './recipes/randomizeRecipes';
import {generateRecipe} from './recipes/generateRecipe';
import {submitAdding} from './recipes/submitAdding';
import {openInfoModal} from './recipes/openInfoModel';
import {initRecipesTables} from './recipes/initRecipesTables';

let $SubmitAddRecipes;

export function initRecipes() {
    if (window.FoodPunk.pageInfo.hideRecipesRandomizer) {
        $SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
    }

    localStorage.removeItem(selectedPopupRecipesStorage);
    localStorage.removeItem(selectedRecipesStorage);

    $(document)
        .bind('cbox_open', () => $('html').css({overflow: 'hidden'}))
        .bind('cbox_cleanup', () => $('html').css({overflow: 'auto'}));

    window.FoodPunk.functions.addRecipes = addRecipes;
    window.FoodPunk.functions.deleteRecipe = deleteRecipe;
    window.FoodPunk.functions.deleteAllRecipes = deleteAllRecipes;
    window.FoodPunk.functions.toggleSelect = toggleSelect;
    window.FoodPunk.functions.deleteSelectedRecipes = deleteSelectedRecipes;
    window.FoodPunk.functions.recalculateUserRecipes = recalculateUserRecipes;
    window.FoodPunk.functions.addRandomizeRecipes = addRandomizeRecipes;
    window.FoodPunk.functions.generateRecipe = generateRecipe;
    window.FoodPunk.functions.submitAdding = submitAdding;
    window.FoodPunk.functions.openInfoModal = openInfoModal;
    window.FoodPunk.functions.renderCounterToolbarData = renderCounterToolbarData;

    initRecipesTables();
}
