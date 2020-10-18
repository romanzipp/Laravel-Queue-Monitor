<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['bootstrap', 'storage', 'vendor'])
    ->name('*.php')
    ->name('.php_cs')
    ->name('_ide_helper')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'array_indentation' => false,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => ['statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try']],
        'braces' => true, // https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4487
        'class_attributes_separation' => ['elements' => ['method']],
        'compact_nullable_typehint' => true,
        'concat_space' => false,
        'fully_qualified_strict_types' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline', 'keep_multiple_spaces_after_comma' => true],
        'no_blank_lines_after_class_opening' => false,
        'no_blank_lines_after_phpdoc' => false,
        'no_blank_lines_before_namespace' => false,
        'no_extra_blank_lines' => ['tokens' => []],
        'no_leading_import_slash' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_phpdoc_tags' => false,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'no_whitespace_in_blank_line' => true,
        'not_operator_with_space' => true,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => ['sortAlgorithm' => 'alpha'],
        'php_unit_fqcn_annotation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'single_blank_line_before_namespace' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline_array' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
    ])
    ->setFinder($finder);
