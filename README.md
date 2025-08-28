# Schema Context Bundle

The **SchemaContextBundle** provides a lightweight way to manage dynamic schema context across your Symfony application, especially useful for multi-tenant setups. It allows schema resolution based on request headers and propagates schema information through Symfony Messenger.

---

## Features

- Extracts tenant schema param from baggage request header.
- Stores schema and baggage context in a global `BaggageSchemaResolver`.
- Injects schema and baggage info into Messenger messages via a middleware.
- Rehydrates schema and baggage on message consumption via a middleware.
- Provide decorator for Http clients to propagate baggage header

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
  app_name: '%env(APP_NAME)%' # Application name
  header_name: 'X-Tenant' # Request header to extract schema name
  default_schema: 'public' # Default schema to fallback to
  allowed_app_names: ['develop', 'staging', 'test'] # App names where schema context is allowed to change
```
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
        - '@Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver'
        - '@Macpaw\SchemaContextBundle\Service\BaggageCodec'
```

## Messenger Integration
The bundle provides a middleware that automatically:

* Adds a BaggageStamp to dispatched messages

* Restores the schema and baggage context on message handling

Enable the middleware in your `messenger.yaml`:

```yaml 
framework:
  messenger:
    buses:
      messenger.bus.default:
        middleware:
        - Macpaw\SchemaContextBundle\Messenger\Middleware\BaggageMiddleware
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
