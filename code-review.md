# Code Review: `zobay/laravel-sslcommerz`

**Date:** 2026-05-09  
**Reviewer:** Expert Review  
**Scope:** Security · Clean Code · Clean Architecture · Performance · Best Practices

---

## Table of Contents

1. [Security](#1-security)
2. [Clean Code](#2-clean-code)
3. [Clean Architecture](#3-clean-architecture)
4. [Performance](#4-performance)
5. [Best Practices](#5-best-practices)
6. [Priority Order](#6-priority-order)

---

## 1. Security

---

### [Critical] Store credentials sent in GET query string

**File:** `src/LaravelSslCommerz.php:35–43`

```php
->get(config('sslcommerz.paths.validation'), array_merge(
    [
        'store_id'     => $this->storeId,
        'store_passwd' => $this->storePassword,
        ...
    ],
    $validationRequest->toArray(),
))
```

`store_passwd` appears in the URL query string. It is captured in web server access logs, proxy logs, browser history, and `Referer` headers sent to third parties. Change to POST.

---

### [Critical] Financial tamper-check tolerance allows under-payment

**File:** `src/LaravelSslCommerz.php:52–62`

```php
abs($validationRequest->amount - $result->amount) >= 1
```

A tolerance of `1` means paying BDT 99.01 on a BDT 100.00 order passes validation. The attacker saves up to `0.999...` in any currency. Change to strict equality (`!=`) or at most `>= 0.01`.

---

### [High] No HTTP timeouts — workers can be held indefinitely

**File:** `src/LaravelSslCommerzServiceProvider.php:24–28`

```php
return Http::baseUrl($baseUrl)->asForm();
```

No `->timeout()` or `->connectTimeout()` is configured. A slow or stalled SSLCommerz endpoint will hold a PHP-FPM worker until `max_execution_time` kills the process. Add `->timeout(30)->connectTimeout(10)` at minimum.

---

### [High] `fromPostData` and `fromApiResponse` crash on missing or invalid input

**Files:** `src/DTOs/IpnData.php:30`, `src/DTOs/ValidationResponseData.php:37`

```php
verifySign: $data['verify_sign'],            // undefined index if absent
status:     PaymentStatus::from($data['status']),   // ValueError on unknown status
```

IPN and API responses are external input. Direct array access without key existence checks produces fatal errors or unhandled `ValueError` exceptions. Validate the shape of the incoming array and throw a descriptive typed exception before constructing the DTO.

---

### [High] `verifyIpnHash` uses MD5 (protocol-imposed but undocumented risk)

**File:** `src/LaravelSslCommerz.php:65–79`

```php
$data['store_passwd'] = md5($this->storePassword);
...
return hash_equals(md5($hashString), $payload->verifySign);
```

`hash_equals` is correctly used (timing-safe). However, MD5 is cryptographically broken, imposed by the SSLCommerz protocol. The README must explicitly warn consumers to defend in depth: IP-allowlist the IPN endpoint, implement idempotency, and not treat `verifyIpnHash` as the sole security gate.

---

### [Medium] `Http::macro` registers a global name that can silently conflict

**File:** `src/LaravelSslCommerzServiceProvider.php:22`

```php
Http::macro('sslcommerz', function () { ... });
```

Any other package or application code registering the same macro name will silently overwrite this one. Encapsulate the HTTP client setup as a private method inside `LaravelSslCommerz` instead of polluting the global `Http` facade.

---

### [Medium] No guidance or protection for the IPN endpoint

There is no middleware, rate-limiting, or IP-restriction guidance for the IPN callback route. `verifyIpnHash` is the only guard. The README must document that this route should be IP-restricted to SSLCommerz server ranges and rate-limited.

---

## 2. Clean Code

---

### [High] Facade PHPDoc signatures are wrong

**File:** `src/Facades/SslCommerz.php:11–14`

```php
* @method static ValidationResponseData validateOrder(string $valId, string $tranId, float $amount, string $currency = 'BDT')
* @method static bool verifyIpnHash(array $postData)
```

**Actual signatures:**

- `validateOrder(ValidationRequestData $validationRequest)`
- `verifyIpnHash(IpnData $payload)`

IDEs and static analyzers generate calls with the wrong argument types. These PHPDoc entries are actively harmful. Fix them immediately.

---

### [High] Placeholder command is production-dead code

**File:** `src/Commands/LaravelSslCommerzCommand.php`

```php
public $signature   = 'laravel-sslcommerz';
public $description = 'My command';
public function handle(): int { $this->comment('All done'); ... }
```

Boilerplate never removed from the Spatie skeleton. Either give it real purpose (e.g., `sslcommerz:ping` to verify credentials against the sandbox) or delete it and unregister it from the service provider.

---

### [Medium] `PaymentResponseData` success is a fragile heuristic

**File:** `src/DTOs/PaymentResponseData.php:20`

```php
$success = ! empty($data['GatewayPageURL']);
```

SSLCommerz returns an explicit `status` field in its response. Inferring success from URL presence is implicit, brittle, and will silently break if the API changes. Map the `status` field directly.

---

### [Medium] `IpnData::getRaw()` is an encapsulation escape hatch

**Files:** `src/DTOs/IpnData.php:57`, `src/LaravelSslCommerz.php:67`

```php
$data = array_intersect_key($payload->getRaw(), array_flip($keys));
```

`getRaw()` exists solely so `verifyIpnHash` can do key extraction on the untyped original array. This defeats the purpose of a typed DTO. Remove `private array $raw` and `getRaw()`. `verifyIpnHash` should reconstruct the hash from the typed public properties on `IpnData`.

---

### [Medium] Commented-out dead code in two files

**Files:** `tests/TestCase.php:17–21`, `database/factories/ModelFactory.php`

Both contain commented-out code blocks serving no purpose. Uncomment and implement, or delete.

---

### [Low] `PaymentRequestData::toArray()` instantiates objects as a side effect

**File:** `src/DTOs/PaymentRequestData.php:47`

```php
($this->shipment ?? new ShipmentData())->toArray(),
($this->emi      ?? new EmiData())->toArray(),
```

Object construction inside `toArray()` is a hidden side effect. The null defaults also mean every payload always includes shipping and EMI fields (even if empty after `array_filter`). Return an empty array when these are absent.

---

## 3. Clean Architecture

---

### [High] No interface/contract for the main class

**Files:** `src/LaravelSslCommerz.php`, `src/Facades/SslCommerz.php`

The facade resolves to a concrete class. There is no `Contracts\SslCommerzInterface`. Consequences:

- Cannot mock without partial mocking the concrete class
- Cannot decorate with a logging or metrics layer
- Cannot substitute a different implementation

Define `Contracts\SslCommerzInterface`, bind it in the container, and resolve it from the facade.

---

### [High] Tamper-detection is application-layer business logic embedded in the package

**File:** `src/LaravelSslCommerz.php:46–63`

```php
if ($validationRequest->currency === 'BDT') {
    if ($validationRequest->tranId !== $result->tranId || abs(...) >= 1) {
        throw new OrderValidationException('Data has been tampered');
    }
}
```

Whether a discrepancy is "tampering," and what to do about it, are decisions that belong in the consuming application. The package should return structured data and expose comparison helpers (`isAmountConsistentWith(float $expected): bool`). Throwing here forces all consumers to adopt this one error-handling strategy.

---

### [High] Config values resolved in constructor — stale in singleton context

**File:** `src/LaravelSslCommerz.php:17–20`

```php
public function __construct()
{
    $this->storeId       = config('sslcommerz.credentials.store_id');
    $this->storePassword = config('sslcommerz.credentials.store_passwd');
}
```

The class is bound as a singleton. Config is read once at first resolution and frozen. Any test that calls `config()->set('sslcommerz.credentials.store_id', '...')` after the singleton resolves will operate against stale credentials silently.

**Fix:** Resolve config at call time, or accept credentials as explicit constructor parameters and bind them in the service provider.

---

### [Medium] Config `version` and `paths` are decoupled but dependent

**File:** `config/sslcommerz.php`

```php
'version' => 'v4',
'paths'   => [
    'init' => '/gwprocess/v4/api.php',   // v4 hardcoded here too
],
```

Changing `version` to `v5` does not update the path. Either derive the path from the version key or remove the redundant `version` key entirely.

---

### [Medium] Migration stub exists with no backing model or purpose

**File:** `database/migrations/create_sslcommerz_table.php.stub`

A migration is published, the README instructs users to run it, but there is no Eloquent model, no factory, and no documentation of the table's schema or purpose. Ship the complete feature (migration + model + documentation) or remove the stub entirely.

---

### [Low] `Http::macro` as architecture — breaks encapsulation

**File:** `src/LaravelSslCommerzServiceProvider.php:22–29`

Registering a macro on the global `Http` facade as the HTTP client strategy is a package-level anti-pattern. It leaks internal implementation concerns into a shared global namespace. Prefer a private `httpClient(): PendingRequest` method on `LaravelSslCommerz`.

---

## 4. Performance

---

### [High] No HTTP timeout (also a security issue)

Worker exhaustion under a slow or hung gateway endpoint. Add `->timeout(30)->connectTimeout(10)` to the HTTP client macro.

---

### [Medium] No retry strategy for transient failures

Payment gateway HTTP calls are idempotent when `tran_id` is stable. Laravel's HTTP client supports `->retry(3, 100)`. Without it, any single transient network error surfaces as a failed payment attempt to the user.

---

### [Low] Repeated null-stripping on every `toArray()` call

**File:** `src/DTOs/PaymentRequestData.php:51`

```php
return array_filter($merged, fn ($v) => $v !== null);
```

Minor, but building a dense array of nulls then filtering on every payment initiation is wasteful. Build only non-null entries at construction time.

---

## 5. Best Practices

---

### [High] Test suite is effectively empty

`ExampleTest.php` tests `true === true`. `ArchTest.php` checks only for debug functions. There are zero tests for:

- `initiatePayment` success and failure paths
- `validateOrder` tamper detection for BDT and foreign currencies
- `verifyIpnHash` with a real fixture (known password, known payload, known hash)
- `fromApiResponse` / `fromPostData` mapping and error handling

For a payment library, missing tests on financial validation logic is the highest-impact gap in the project.

---

### [High] README is unmodified Spatie skeleton template

It references `echoPhrase('Hello, Zobay!')` (a non-existent method), links to Spatie's own support pages, and contains no usage documentation for the actual API surface. A payment package with no integration documentation is dangerous to ship.

---

### [Medium] `declare(strict_types=1)` missing from command file

**File:** `src/Commands/LaravelSslCommerzCommand.php:1`

All other source files in `src/` declare strict types. The command file is inconsistent.

---

### [Medium] `minimum-stability: dev` in `composer.json`

```json
"minimum-stability": "dev",
```

Allows unstable transitive dependencies in consumer projects. Change to `stable`. If a dev-only dependency requires it, use `@dev` version constraints explicitly for that package only.

---

### [Low] `phpunit.xml.dist` references an outdated PHPUnit schema

```xml
xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.3/phpunit.xsd"
```

The project installs Pest 4.x which ships with PHPUnit 11/12. The schema URL is for PHPUnit 10.3 and will trigger validation warnings. Update the schema URL to match the installed version.

---

## 6. Priority Order

| # | Issue | Severity | File |
|---|-------|----------|------|
| 1 | Store password in GET URL | Critical | `src/LaravelSslCommerz.php:35` |
| 2 | Tamper-check tolerance `>= 1` | Critical | `src/LaravelSslCommerz.php:52` |
| 3 | No HTTP timeouts | High | `src/LaravelSslCommerzServiceProvider.php:24` |
| 4 | Crash on missing keys in DTOs | High | `src/DTOs/IpnData.php:30`, `src/DTOs/ValidationResponseData.php:37` |
| 5 | Wrong Facade PHPDoc signatures | High | `src/Facades/SslCommerz.php:11` |
| 6 | No interface/contract | High | `src/LaravelSslCommerz.php` |
| 7 | Business logic in package layer | High | `src/LaravelSslCommerz.php:46` |
| 8 | Config resolved in singleton constructor | High | `src/LaravelSslCommerz.php:17` |
| 9 | Empty test suite | High | `tests/` |
| 10 | Unmodified README | High | `README.md` |
| 11 | Placeholder command | High | `src/Commands/LaravelSslCommerzCommand.php` |
| 12 | `Http::macro` global conflict risk | Medium | `src/LaravelSslCommerzServiceProvider.php:22` |
| 13 | No retry strategy | Medium | `src/LaravelSslCommerz.php` |
| 14 | `PaymentResponseData` success heuristic | Medium | `src/DTOs/PaymentResponseData.php:20` |
| 15 | Config version/path coupling | Medium | `config/sslcommerz.php` |
| 16 | `IpnData::getRaw()` escape hatch | Medium | `src/DTOs/IpnData.php:57` |
| 17 | Dead commented-out code | Medium | `tests/TestCase.php`, `database/factories/` |
| 18 | `minimum-stability: dev` | Medium | `composer.json` |
| 19 | Missing `strict_types` in command | Medium | `src/Commands/LaravelSslCommerzCommand.php` |
| 20 | Orphaned migration stub | Medium | `database/migrations/` |
| 21 | Objects instantiated in `toArray()` | Low | `src/DTOs/PaymentRequestData.php:47` |
| 22 | Null-strip on every serialization | Low | `src/DTOs/PaymentRequestData.php:51` |
| 23 | Outdated PHPUnit schema URL | Low | `phpunit.xml.dist` |
