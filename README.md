# Cloak for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dynamikdev/cloak-laravel.svg?style=flat-square)](https://packagist.org/packages/dynamikdev/cloak-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dynamik-dev/cloak-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dynamik-dev/cloak-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dynamikdev/cloak-laravel.svg?style=flat-square)](https://packagist.org/packages/dynamikdev/cloak-laravel)

**Keep PII out of LLMs.** Cloak masks sensitive data before sending text to AI APIs like ChatGPT or Claude, then restores the original data in responses.

## Quick Start

```php
// Mask PII before sending to an LLM
$safe = cloak('Contact john@example.com or call 555-123-4567');
// "Contact {{EMAIL_x7k2m9_1}} or call {{PHONE_x7k2m9_1}}"

// Restore the original data
$original = uncloak($safe);
// "Contact john@example.com or call 555-123-4567"
```

## Why Cloak?

When building AI-powered features, user messages often contain sensitive information—emails, phone numbers, SSNs, credit cards. Sending this data to third-party LLM APIs creates privacy and compliance risks.

Cloak solves this by:
1. **Detecting** PII using built-in or custom detectors
2. **Replacing** sensitive data with placeholder tokens
3. **Storing** the mapping temporarily (in-memory or cache)
4. **Restoring** original data when you receive the LLM's response

The LLM never sees the actual PII, but your users get personalized responses.

## Installation

```bash
composer require dynamikdev/cloak-laravel
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="cloak-config"
```

## Configuration

```php
// config/cloak.php
return [
    // false (default): In-memory storage, auto-cleared after request
    // true: Cache storage, persists across requests
    'persist' => env('CLOAK_PERSIST', false),

    // Storage driver class when persist is true
    'storage_driver' => DynamikDev\Cloak\Laravel\CacheStorage::class,

    // Cache store to use (null = default cache)
    'cache_store' => env('CLOAK_CACHE_STORE'),

    // TTL for cached mappings in seconds
    'default_ttl' => env('CLOAK_DEFAULT_TTL', 3600),
];
```

### Encryption at Rest

All sensitive data is encrypted using Laravel's `Crypt` facade before being stored, regardless of which storage driver is used. This provides defense in depth—even if memory or cache is compromised, the original PII remains protected.

### Persist Mode

- **`persist: false`** (default) - Encrypted in-memory storage. Perfect for single-request flows where you cloak → call LLM → uncloak in one request. Data is automatically garbage collected.

- **`persist: true`** - Encrypted Laravel cache. Use this when you need to uncloak in a different request (e.g., webhook responses, queued jobs).

## Usage

### Helper Functions

The simplest way to use Cloak:

```php
$masked = cloak($text);
$restored = uncloak($masked);
```

### Facade

```php
use DynamikDev\Cloak\Laravel\Facades\Cloak;

$masked = Cloak::cloak($text);
$restored = Cloak::uncloak($masked);
```

### Dependency Injection

```php
use DynamikDev\Cloak\Cloak;

class ChatController extends Controller
{
    public function send(Request $request, Cloak $cloak)
    {
        $safe = $cloak->cloak($request->input('message'));
        // ...
    }
}
```

## Real-World Example: OpenAI Integration

```php
use OpenAI\Laravel\Facades\OpenAI;

public function chat(Request $request)
{
    $userMessage = $request->input('message');
    // "Help me email john.doe@acme.com about invoice #1234.
    //  My number is 555-867-5309 if they need to call back."

    // 1. Cloak PII before sending to OpenAI
    $safeMessage = cloak($userMessage);
    // "Help me email {{EMAIL_a1b2c3_1}} about invoice #1234.
    //  My number is {{PHONE_a1b2c3_1}} if they need to call back."

    // 2. Send to OpenAI - no PII exposed to the API
    $response = OpenAI::chat()->create([
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $safeMessage],
        ],
    ]);

    // 3. Get the response (LLM uses placeholders naturally)
    $aiResponse = $response->choices[0]->message->content;
    // "Here's a draft email for {{EMAIL_a1b2c3_1}}:
    //  Subject: Regarding Invoice #1234..."

    // 4. Restore PII for the user
    $finalResponse = uncloak($aiResponse);
    // "Here's a draft email for john.doe@acme.com:
    //  Subject: Regarding Invoice #1234..."

    return response()->json(['response' => $finalResponse]);
}
```

## Built-in Detectors

Cloak automatically detects:

| Type | Example | Placeholder |
|------|---------|-------------|
| Email | `john@example.com` | `{{EMAIL_x1y2z3_1}}` |
| Phone | `555-123-4567` | `{{PHONE_x1y2z3_1}}` |
| SSN | `123-45-6789` | `{{SSN_x1y2z3_1}}` |
| Credit Card | `4111-1111-1111-1111` | `{{CREDIT_CARD_x1y2z3_1}}` |

By default, all detectors run. To use specific detectors:

```php
use DynamikDev\Cloak\Detector;

// Only detect emails and phones
$masked = cloak($text, [
    Detector::email(),
    Detector::phone(),
]);

// Phone detection with region hint (improves accuracy)
$masked = cloak($text, [
    Detector::phone('US'),
]);
```

## Custom Detectors

### Pattern Detector (Regex)

```php
use DynamikDev\Cloak\Detector;

// Detect database connection strings
$masked = cloak($text, [
    Detector::pattern(
        '/mysql:\/\/[^:]+:[^@]+@[^\s]+/',
        'DB_CONNECTION'
    ),
]);

// Detect API keys
$masked = cloak($text, [
    Detector::pattern(
        '/sk-[a-zA-Z0-9]{32,}/',
        'API_KEY'
    ),
]);
```

### Word Detector

```php
use DynamikDev\Cloak\Detector;

// Mask specific names or terms (case-insensitive)
$masked = cloak($text, [
    Detector::words(['John Doe', 'Jane Smith', 'Acme Corp'], 'NAME'),
]);
```

### Callback Detector

For complex detection logic:

```php
use DynamikDev\Cloak\Detector;

$masked = cloak($text, [
    Detector::using(function (string $text) {
        // Return array of ['match' => '...', 'type' => '...']
        $matches = [];

        // Example: Find Laravel env variables
        if (preg_match_all('/\bDB_PASSWORD=\S+/', $text, $found)) {
            foreach ($found[0] as $match) {
                $matches[] = ['match' => $match, 'type' => 'ENV_VAR'];
            }
        }

        return $matches;
    }),
]);
```

## Middleware Example

Auto-cloak sensitive data in request logs:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSafeRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Log request with PII masked
        Log::info('API Request', [
            'path' => $request->path(),
            'body' => cloak(json_encode($request->all())),
        ]);

        return $next($request);
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [DynamikDev](https://github.com/dynamik-dev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
