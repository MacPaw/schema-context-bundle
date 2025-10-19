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
    ) {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $currentSchema = $this->baggageSchemaResolver->getSchema();

        $originalTableName = sprintf(
            '"%s"."%s"',
            $currentSchema,
            $options['table_name'] ?? 'messenger_messages',
        );

        // Create transport with schema-prefixed table name
        $options['table_name'] = $originalTableName;

        // Create transport with the original factory
        return $this->decoratedFactory->createTransport($dsn, $options, $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return $this->decoratedFactory->supports($dsn, $options);
    }
}
