<?php

use PhpCsFixer\{Config, Finder};

define('PHP_CS_FIXER_IGNORE_ENV', true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'group_import' => true,
        'blank_line_after_namespace' => true,
        'single_line_after_imports' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'if', 'foreach', 'while', 'do', 'switch', 'try'],
        ],
    ])
    ->setFinder(Finder::create()->in(__DIR__));
