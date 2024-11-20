<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__.'/app',
            __DIR__.'/bootstrap',
            __DIR__.'/config',
            __DIR__.'/lang',
            __DIR__.'/public',
            __DIR__.'/resources',
            __DIR__.'/routes',
        ]
    );

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets(
        [
            LevelSetList::UP_TO_PHP_81,
            SetList::DEAD_CODE,
            SetList::CODE_QUALITY,
            SetList::PRIVATIZATION,
            SetList::TYPE_DECLARATION,
            SetList::EARLY_RETURN
        ]
    );
};
