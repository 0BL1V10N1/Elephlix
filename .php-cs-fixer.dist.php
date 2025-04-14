<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('bin')
    ->exclude('config')
    ->exclude('var')
    ->exclude('public/bundles')
    ->exclude('public/build')
    ->notPath('public/index.php')
    ->notPath('importmap.php');

return (new PhpCsFixer\Config())
    ->setParallelConfig(new PhpCsFixer\Runner\Parallel\ParallelConfig(4, 20))
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache');
