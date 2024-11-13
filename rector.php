<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__.'/**/vendor/*',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php83: true)
    // ->withTypeCoverageLevel(0)
    ->withPreparedSets(
        carbon: true,
        codeQuality: true,
        codingStyle: true,
        deadCode: true,
        earlyReturn: true,
        instanceOf: true,
        privatization: true,
        strictBooleans: true,
        typeDeclarations: true,
    )
    ->withImportNames();
