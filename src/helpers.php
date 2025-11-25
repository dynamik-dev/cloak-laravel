<?php

if (! function_exists('cloak')) {
    /**
     * Cloak sensitive data in text, replacing it with placeholder tokens.
     *
     * @param  string  $text  The text containing sensitive data
     * @param  array|null  $detectors  Optional array of detectors to use
     * @return string The text with sensitive data replaced by placeholders
     */
    function cloak(string $text, ?array $detectors = null): string
    {
        return app(\DynamikDev\Cloak\Cloak::class)->cloak($text, $detectors);
    }
}

if (! function_exists('uncloak')) {
    /**
     * Restore the original sensitive data from placeholder tokens.
     *
     * @param  string  $text  The text containing placeholder tokens
     * @return string The text with original sensitive data restored
     */
    function uncloak(string $text): string
    {
        return app(\DynamikDev\Cloak\Cloak::class)->uncloak($text);
    }
}
