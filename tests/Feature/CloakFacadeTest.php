<?php

use DynamikDev\Cloak\Laravel\Facades\Cloak;

it('cloaks sensitive data via facade', function () {
    $text = 'Email me at test@example.com';

    $cloaked = Cloak::cloak($text);

    expect($cloaked)->not->toContain('test@example.com');
    expect($cloaked)->toMatch('/\{\{EMAIL_[A-Za-z0-9]+_\d+\}\}/');
});

it('uncloaks masked data via facade', function () {
    $text = 'Email me at test@example.com';

    $cloaked = Cloak::cloak($text);
    $uncloaked = Cloak::uncloak($cloaked);

    expect($uncloaked)->toBe($text);
});

it('handles text without sensitive data', function () {
    $text = 'Hello world, no sensitive data here.';

    $cloaked = Cloak::cloak($text);

    expect($cloaked)->toBe($text);
});

it('cloaks multiple email addresses', function () {
    $text = 'Contact john@example.com or jane@example.com';

    $cloaked = Cloak::cloak($text);
    $uncloaked = Cloak::uncloak($cloaked);

    expect($cloaked)->not->toContain('john@example.com');
    expect($cloaked)->not->toContain('jane@example.com');
    expect($uncloaked)->toBe($text);
});
