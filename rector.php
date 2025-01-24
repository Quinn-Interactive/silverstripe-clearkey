<?php

declare(strict_types=1);

use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeLevelSetList;
use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeSetList;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php83: true)
    ->withSkip([
        ChangeSwitchToMatchRector::class,
    ])
    ->withImportNames(importShortClasses: false)
    ->withSets([
        SilverstripeLevelSetList::UP_TO_SILVERSTRIPE_413,
        SilverstripeSetList::CODE_QUALITY,
    ])
;
