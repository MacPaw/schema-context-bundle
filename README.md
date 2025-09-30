# Schema Context Bundle

The **SchemaContextBundle** provides a lightweight way to manage dynamic schema context across your Symfony application, especially useful for multi-tenant setups. It allows schema resolution based on request headers and propagates schema information through Symfony Messenger.

---

## Features

- Extracts tenant schema param from baggage request header.
- Stores schema and baggage context in a global `BaggageSchemaResolver`.
- Injects schema and baggage info into Messenger messages via a middleware.
- Rehydrates schema and baggage on message consumption via a middleware.
- Provide decorator for Http clients to propagate baggage header
- Optional: Adds baggage context to Monolog log records via a processor

---

## Installation

```bash
composer require macpaw/schema-context-bundle
```

If you are not using Symfony Flex, register the bundle manually:

```php
// config/bundles.php
return [
    Macpaw\SchemaContextBundle\SchemaContextBundle::class => ['all' => true],
];
```
## Configuration
### 1. Bundle Configuration
Add this config to `config/packages/schema_context.yaml`:

```yaml
schema_context:
    # Name of your application (required). Used to decide whether incoming
    # requests are allowed to override the schema from baggage.
    app_name: '%env(APP_NAME)%'

    # The KEY inside the RFC 8941 "baggage" HTTP header that holds the schema name.
    # This is NOT an HTTP header name itself. Default: 'X-Schema'
    header_name: 'X-Schema'

    # Fallback schema used when baggage doesn't contain the key or value is empty
    default_schema: 'public'

    # Explicit app names that ARE allowed to take schema from baggage
    allowed_app_names: ['develop', 'staging', 'test']

    # Additionally allow app names by regex patterns (evaluated with preg_match)
    # Example: allow PR preview apps like "pr-123"
    allowed_app_names_regex: ['/^pr-\d+$/']
```

Notes:
- The bundle reads the standard "baggage" HTTP header and expects a comma-separated list of key=value pairs.
- It looks up the schema by the configured header_name key inside that baggage.

Example incoming HTTP headers:
```
GET / HTTP/1.1
Host: example.test
baggage: X-Schema=tenant_42,traceId=abc123
```

With the config above, the bundle will resolve schema "tenant_42" and store the entire baggage map.

### 2. Set Environment Parameters
If you're using .env, define the app name:

```env
APP_NAME=develop
```

## Usage

```php
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;

public function index(BaggageSchemaResolver $schemaResolver)
{
    $schema = $schemaResolver->getSchema();
    $baggage = $schemaResolver->getBaggage();
    // Use schema in logic
}
```

## Baggage-Aware HTTP Client
Decorate your http client in your service configuration:
```yaml
services:
    baggage_aware_payment_http_client:
        class: Macpaw\SchemaContextBundle\HttpClient\BaggageAwareHttpClient
        decorates: payment_http_client #http client to decorate
        arguments:
            - '@baggage_aware_payment_http_client.inner'
```

### A Note on Testing

If you are replacing or mocking HTTP clients in your test environment, for example, using a library like [`macpaw/extended-mock-http-client`](https://github.com/MacPaw/extended_mock_http_client), you need to disable the `BaggageAwareHttpClient` decoration.

```yaml
when@test:
    services:
        baggage_aware_payment_http_client:
            class: Macpaw\SchemaContextBundle\HttpClient\BaggageAwareHttpClient
```

## Messenger Integration
The bundle provides a middleware that automatically:

* Adds a BaggageSchemaStamp to dispatched messages
* Restores the schema and baggage context on message handling

Enable the middleware in your `messenger.yaml`:

```yaml
framework:
    messenger:
        buses:
            messenger.bus.default:
                middleware:
                    - Macpaw\SchemaContextBundle\Messenger\Middleware\BaggageSchemaMiddleware
```

## Optional: Monolog Integration
The bundle provides an optional processor that automatically adds baggage context to your log records:

* Adds baggage information to the `extra` field of log records

To enable the processor, add it to your service configuration:

```yaml
services:
    Macpaw\SchemaContextBundle\Monolog\BaggageProcessor:
        tags:
            - { name: monolog.processor }
```

## Testing
To run tests:
```bash
vendor/bin/phpunit
```

## Contributing
Feel free to open issues and submit pull requests.

## License
This bundle is released under the MIT license.
