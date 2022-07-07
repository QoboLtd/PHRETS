<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ])
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'no_extra_blank_lines' => ['tokens' => [
            'extra',
            'parenthesis_brace_block',
            'square_brace_block',
            'throw',
            'use',
        ]],
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => false,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'yoda_style' => false
    ])
    ->setFinder($finder);
