<?php

return [
    // TODO:: review @NickMost, probably deprecated
    # adding new recipes for Challenges
    'challenge' => [
        'numberNewRecipes' => 5, // number of recipes to add
        'ranges'           => [
            [2258, 2261],
            [2282, 2302],
        ],// recipe ranges
        'force'        => false, // continue searching for recipes from the entire database
        'challenge_id' => 15,
    ],

    # monthly addition of new recipes
    'monthly' => [
        // number of recipes to add
        'numberNewRecipes' => 10,
        // recipe ranges
        'ranges' => [
            [
                6203,
                6204,
                6205,
                6206,
                6207,
                6208,
                6209,
                6210,
                6211,
                6212,
                6213,
                6214,
                6215,
                6216,
                6217,
                6218,
                6219,
                6220,
                6221,
                6222,
                6223,
                6224,
                6225,
                6226,
                6227,
                6228,
                6229,
                6230,
                6231,
                6232,
                6233,
                6234,
                6235,
                6236,
                6237,
                6238,
                6239,
                6240,
                6241,
                6242,
                6243,
                6244,
                6245,
                6246,
                6247,
                6248,
                6249,
                6250,
                6251,
                6252,
                6253,
                6254,
                6255,
                6256,
                6257,
                6258,
                6259,
                6260,
                6261,
                6262,
                //                6281,
                //                6282,
                //                6283,
                //                6284,
                //                6285,
                //                6286,
                //                6287,
                //                6288,
                //                6289,
                //                6290,
                //                6291,
                //                6292
            ],
        ],
        'force' => true, // continue searching for recipes from the entire database
    ],

    // recipes tags from which will be taken first time recipes distribution
    /**
     * @notes: refactored from recipe categories to tags
     */
    'first_meal_plan_recipe_tag_id' => env('FIRST_MEAL_PLAN_RECIPE_TAG_ID', false),

    // Url for purchasing recipes
//    'purchase_url' => 'https://foodpunk.de/shop/app/foodpunkte.html'
    'purchase_url' => env('RECIPES_PURCHASE_URL', 'https://shop.foodpunk.de/collections/gutschein'),
];
