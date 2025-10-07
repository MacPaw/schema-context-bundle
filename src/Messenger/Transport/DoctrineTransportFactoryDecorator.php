<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Messenger\Transport;

use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Decorator for DoctrineTransportFactory to support schema prefixes in table_name option.
 *
 * This decorator extends the default DoctrineTransportFactory to handle table names
 * that include schema prefixes using the BaggageSchemaResolver for dynamic schema detection.
 */
final class DoctrineTransportFactoryDecorator implements TransportFactoryInterface
{
    public function __construct(
        private readonly TransportFactoryInterface $decoratedFactory,
        private readonly BaggageSchemaResolver $baggageSchemaResolver,
        private readonly string $defaultSchema = 'public',
    ) {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        // Get current schema from BaggageSchemaResolver
        $currentSchema = $this->baggageSchemaResolver->getSchema();

        // If we have a schema and it's not the default 'public' schema, modify the table name
        if ($currentSchema !== null && $currentSchema !== $this->defaultSchema) {
            $originalTableName = sprintf(
                '"%s"."%s"',
                $currentSchema,
                $options['table_name'] ?? 'messenger_messages',
            );

            // Create transport with schema-prefixed table name
            $options['table_name'] = $originalTableName;
        }

        // Create transport with the original factory
        return $this->decoratedFactory->createTransport($dsn, $options, $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return $this->decoratedFactory->supports($dsn, $options);
    }
}
