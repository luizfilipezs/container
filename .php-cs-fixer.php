<?php

declare(strict_types=1);

use PhpCsFixer\{Config, Finder};

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // https://cs.symfony.com/doc/rules/index.html
        '@PSR12' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_import_per_statement' => false,
        'group_import' => true,
        'no_unused_imports' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'throw',
                'if',
                'for',
                'foreach',
                'while',
                'do',
                'switch',
                'try',
            ],
        ],
        'whitespace_after_comma_in_array' => true,
        'no_trailing_comma_in_singleline' => [
            'elements' => ['arguments', 'array', 'array_destructuring', 'group_import'],
        ],
        'array_push' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_spaces_around_offset' => ['positions' => ['inside', 'outside']],
        'trim_array_spaces' => true,
        'method_chaining_indentation' => true,
        'type_declaration_spaces' => ['elements' => ['constant', 'function', 'property']],
        'types_spaces' => ['space' => 'none', 'space_multiple_catch' => null],
        'single_quote' => true,
        'explicit_string_variable' => true,
        'attribute_empty_parentheses' => ['use_parentheses' => false],
        'single_line_empty_body' => true,
        'class_reference_name_casing' => true,
        'cast_spaces' => ['space' => 'single'],
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'single_line_comment_spacing' => true,
        'single_line_comment_style' => ['comment_types' => ['asterisk', 'hash']],
        'empty_loop_body' => ['style' => 'semicolon'],
        'no_singleline_whitespace_before_semicolons' => true,
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'simplified_if_return' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'assign_null_coalescing_to_coalesce_equal' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'new_with_braces' => ['anonymous_class' => false, 'named_class' => true],
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => false,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_indent' => true,
        'phpdoc_order' => ['order' => ['template', 'param', 'throws', 'return']],
        'phpdoc_param_order' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_types_order' => ['sort_algorithm' => 'none', 'null_adjustment' => 'always_last'],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        'no_empty_statement' => true,
        'space_after_semicolon' => true,
        'no_extra_blank_lines' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'no_space_around_double_colon' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude(['vendor'])
            ->name('*.php'),
    );
