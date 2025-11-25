<?php

it('cloak helper function masks sensitive data', function () {
    $text = 'Email me at test@example.com';

    $cloaked = cloak($text);

    expect($cloaked)->not->toContain('test@example.com');
    expect($cloaked)->toMatch('/\{\{EMAIL_[A-Za-z0-9]+_\d+\}\}/');
});

it('uncloak helper function restores original data', function () {
    $text = 'Email me at test@example.com';

    $cloaked = cloak($text);
    $uncloaked = uncloak($cloaked);

    expect($uncloaked)->toBe($text);
});

it('helper functions work together for full round trip', function () {
    $original = 'Contact john@example.com or jane@company.org for help.';

    $masked = cloak($original);
    $restored = uncloak($masked);

    expect($masked)->not->toContain('john@example.com');
    expect($masked)->not->toContain('jane@company.org');
    expect($restored)->toBe($original);
});
