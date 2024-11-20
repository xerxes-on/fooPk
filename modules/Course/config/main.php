<?php

declare(strict_types=1);

return [
    'special_costs'                 => json_decode(env('CHALLENGES_SPECIAL_PRICES_CONFIG_JSON', ''), true),
    'bootcamp_and_fitness_discount' => 100,
    'sugar_detox_discount'          => 0,
];
