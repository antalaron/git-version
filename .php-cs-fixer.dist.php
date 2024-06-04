<?php

/*
 * This file is part of GitVersion.
 *
 * (c) Antal Áron <antalaron@antalaron.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<'EOF'
This file is part of GitVersion.

(c) Antal Áron <antalaron@antalaron.hu>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@Symfony:risky' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_before_statement' => ['statements' => ['break', 'case', 'continue', 'declare', 'default', 'exit', 'goto', 'phpdoc', 'return', 'throw', 'yield', 'yield_from']],
        'header_comment' => ['header' => $header],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'modernize_strpos' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'no_extra_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'ordered_class_elements' => ['order' => ['use_trait', 'constant_public', 'constant_protected', 'constant_private', 'property_public', 'property_protected', 'property_private', 'construct', 'destruct']],
        'ordered_imports' => true,
        'ordered_interfaces' => true,
        'phpdoc_to_comment' => ['ignored_tags' => ['var']],
        'psr_autoloading' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => false, 'elements' => ['arguments', 'arrays', 'match', 'parameters']],
        'yoda_style' => ['equal' => true, 'identical' => true, 'less_and_greater' => true, 'always_move_variable' => true],
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__)
            ->append([
                __FILE__,
                'bin/check-version',
                'bin/assets-mtime',
                'bin/console',
                'bin/phpunit',
                'config/bootstrap.php',
                'config/bundles.php',
                'public/index.php',
            ])
            ->exclude([
                'assets',
                'bin',
                'config',
                'node_modules',
                'public',
                'templates',
                'translations',
                'var',
                'vendor',
            ]),
    )
;
