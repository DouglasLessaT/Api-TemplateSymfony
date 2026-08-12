<?php

use Doctum\Doctum;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src/Core');

return new Doctum($iterator, [
    'title'                => 'CORE - Oracle Cards API Documentation',
    'build_dir'            => __DIR__ . '/docs/api',
    'cache_dir'            => __DIR__ . '/var/cache/doctum',
    'default_opened_level' => 2,
]);
