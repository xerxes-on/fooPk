<?php

declare(strict_types=1);

return [
    'success' => [
        'delete_list'                       => 'Delete list?',
        'list_was_created'                  => 'List created successfully',
        'list_was_deleted'                  => 'List was deleted',
        'list_was_not_deleted'              => 'List was not deleted',
        'generate_list_title'               => 'Do you want to generate a new shopping list?',
        'generate_list_text'                => 'Your previous shopping list will be overwritten.',
        'clear_list_question'               => 'Clear list?',
        'recipe_added_to_purchase_list'     => 'Recipe added to purchase list!',
        'recipe_removed_from_purchase_list' => 'Recipe removed from purchase list!',
        'purchase_list_is_empty'            => 'Purchase list is empty!',
        'recipe_not_found_in_purchase_list' => 'Recipe not found in purchase list!',
        'portions_changed'                  => 'Portions changed successfully!',
        'empty_recipes_list'                => 'You do not have any recipes in your purchase list.',
        'empty_ingredients_list'            => 'You do not have any ingredients in your purchase list.',
        'item_added'                        => 'Ingredient added!',
        'item_removed'                      => 'Ingredient removed!',
    ],
    'error' => [
        'same_amount'              => 'This recipe servings is already set to this amount.',
        'unknown_recipe_type'      => 'Unknown recipe type.',
        'replace_recipe'           => 'Recipe was not replaced in shopping list.',
        'not_found_while_deleting' => 'Previous recipe was not found in your shopping list.',
        'not_found'                => 'Recipe was not found in shopping list.',
        'recipe_servings'          => 'Unable to change amount of servings for this recipe. Please try again later.',
        'delete_recipe'            => 'Unable to delete this recipe. Please try again later.',
        'add_recipe'               => 'Unable to add this recipe. Please try again later.',
        'field_is_empty'           => 'Field is empty.',
        'item_removal'             => 'Ingredient was not removed.',
        'no_recipes'               => 'No available recipes found'
    ],
];
