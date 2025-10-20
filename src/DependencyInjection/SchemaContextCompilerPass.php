<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\DependencyInjection;

use Macpaw\SchemaContextBundle\Messenger\Transport\DoctrineTransportFactoryDecorator;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class SchemaContextCompilerPass implements CompilerPassInterface
{
    public const TARGET_ID = 'messenger.transport.doctrine.factory';
    public const DECORATOR_ID = 'messenger.doctrine_transport_factory.decorator';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(self::TARGET_ID) === false) {
            return;
        }

        $def = new Definition(DoctrineTransportFactoryDecorator::class);
        $def->setAutowired(true);      // avoid pulling the chain or adding tags
        $def->setAutoconfigured(true);
        $def->setPublic(false);

        // Decorate the *target* id; explicit inner id is "<decorator>.inner"
        $def->setDecoratedService(self::TARGET_ID, self::DECORATOR_ID . '.inner');

        // Inject the inner/original factory + your resolver
        $def->setArgument('$decoratedFactory', new Reference(self::DECORATOR_ID . '.inner'));
        $def->setArgument('$baggageSchemaResolver', new Reference(BaggageSchemaResolver::class));

        $container->setDefinition(self::DECORATOR_ID, $def);
    }
}
