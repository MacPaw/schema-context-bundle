# Schema Context Bundle

The **SchemaContextBundle** provides a robust way to manage dynamic schema context across your Symfony application, especially useful for multi-tenant setups. It extracts schema information from W3C standard baggage headers (or Symfony Messenger Stamps), validates schema changes based on environment configuration, and propagates schema context throughout your application, including HTTP clients and Symfony Messenger queues.

---

## Features

- Extracts tenant schema param from W3C standard `baggage` request header.
- Stores schema and baggage context in a global `BaggageSchemaResolver`.
- Validates schema changes based on environment configuration to prevent accidental schema mismatches.
- Injects schema and baggage info into Messenger messages via a middleware.
- Rehydrates schema and baggage on message consumption via a middleware.
- Provide decorator for Http clients to propagate baggage header.
- Optional: Adds baggage context to Monolog log records via a processor.

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
    environment_name: '%env(APP_ENV)%' # Current environment name (example: 'develop')
    header_name: 'X-Tenant' # Key name in baggage header to extract schema name
    environment_schema: '%env(ENVIRONMENT_SCHEMA)%' # The schema for this environment (example: 'public')
    overridable_environments: ['develop', 'staging', 'test'] # Environments where schema can be overridden via baggage header or Symfony Messenger stamp
```

**Configuration parameters:**
- `environment_name`: The name of the current environment. Best practice is to use `'%env(APP_ENV)%'` to match Symfony's environment.
- `environment_schema`: The schema for this environment.
- `header_name`: The key name in the baggage header used to extract the schema value.
- `overridable_environments`: List of environment names where schema can be overridden via baggage header or Symfony Messenger stamp.

```env
APP_ENV=develop
ENVIRONMENT_SCHEMA=public
```

### 3. Schema Override Protection
The bundle includes protection against accidental schema changes in production environments:
- In **non-overridable environments** (e.g., `production`): The schema is always fixed to `environment_schema`. Any attempt to override it via baggage header will throw `EnvironmentSchemaMismatchException`.
- In **overridable environments** (e.g., `develop`, `staging`): The schema can be dynamically changed via baggage header for testing and development purposes.

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

### Baggage Header Format

The bundle uses W3C standard `baggage` header format. Example request:

```http
GET /api/endpoint HTTP/1.1
Host: example.com
baggage: X-Tenant=tenant_a,user-id=12345,trace-id=abc123
```

The bundle will extract the schema value from the baggage header using the key specified in `header_name` configuration.

## Exception Handling

### EnvironmentSchemaMismatchException

The bundle throws `EnvironmentSchemaMismatchException` when:
- The environment is **not** in the `overridable_environments` list
- A request tries to set a schema via baggage header that differs from `environment_schema`

This exception prevents accidental schema changes in production/staging/etc. environments. Example error message:

```
Schema mismatch in "production" environment: expected "public", got "tenant_a". Allowed override environments: [develop, staging, test].
```

**How to handle:**
- In production/staging/etc.: ensure clients don't send schema baggage headers, or send the correct environment schema
- In development: add your environment to `overridable_environments` list if you need to test different schemas

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
