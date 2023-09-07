<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRules([
        '@PER' => true,
        '@PER-CS1.0' => true,
        '@PHP54Migration' => true,
        '@PHP70Migration' => true,
        '@PHP71Migration' => true,
        '@PHP73Migration' => true,
        '@PHP74Migration' => true,
        '@PHP80Migration' => true,
        '@PHP81Migration' => true,
        '@PHP82Migration' => true,
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,
    ])
    ->setFinder(
        Finder::create()
            ->exclude('bootstrap')
            ->exclude('public')
            ->exclude('storage')
            ->exclude('vendor')
            ->in(__DIR__)
    );
