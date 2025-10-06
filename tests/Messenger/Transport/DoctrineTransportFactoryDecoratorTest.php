<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\Messenger\Transport;

use Macpaw\SchemaContextBundle\DependencyInjection\SchemaContextCompilerPass;
use Macpaw\SchemaContextBundle\Messenger\Transport\DoctrineTransportFactoryDecorator;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;

final class DoctrineTransportFactoryDecoratorTest extends TestCase
{
    public function testCompilerPassNotRegisterDecoratorService(): void
    {
        $compilerPass = new SchemaContextCompilerPass();

        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects(self::once())
            ->method('hasDefinition')
            ->willReturn(false);

        $containerBuilder->expects(self::never())
            ->method('setDefinition');

        $compilerPass->process($containerBuilder);
    }

    public function testCompilerPassRegisterDecoratorService(): void
    {
        $compilerPass = new SchemaContextCompilerPass();

        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects(self::once())
            ->method('hasDefinition')
            ->willReturn(true);

        $containerBuilder->expects(self::once())
            ->method('setDefinition')
            ->with(
                self::equalTo(SchemaContextCompilerPass::DECORATOR_ID),
                self::callback(function (Definition $definition): bool {
                    // You can assert partial properties here
                    self::assertSame(
                        DoctrineTransportFactoryDecorator::class,
                        $definition->getClass(),
                    );

                    self::assertFalse($definition->isPublic());
                    self::assertTrue($definition->isAutowired());
                    self::assertTrue($definition->isAutoconfigured());
                    self::assertIsArray($definition->getDecoratedService());
                    self::assertArrayHasKey(0, $definition->getDecoratedService());
                    self::assertEquals(
                        SchemaContextCompilerPass::TARGET_ID,
                        $definition->getDecoratedService()[0],
                    );

                    // Optional: check arguments only if needed
                    $args = $definition->getArguments();
                    self::assertArrayHasKey('$decoratedFactory', $args);

                    return true;
                }),
            );

        $compilerPass->process($containerBuilder);
    }

    public function testSchemaIsOverride(): void
    {
        $doctrineTransportMock = $this->createMock(TransportFactoryInterface::class);
        $baggage = new BaggageSchemaResolver();
        $baggage->setSchema('test_schema');

        $decorator = new DoctrineTransportFactoryDecorator(
            $doctrineTransportMock,
            $baggage,
        );

        $doctrineTransportMock->expects(self::once())
            ->method('createTransport')
            ->with(
                self::equalTo(''),
                self::callback(function (array $options): bool {
                    self::assertArrayHasKey('table_name', $options);
                    self::assertEquals('"test_schema"."messenger_messages"', $options['table_name']);

                    return true;
                },
                ),
            );

        $decorator->createTransport('', [], $this->createMock(SerializerInterface::class));
    }

    public function testSchemaIsDefault(): void
    {
        $doctrineTransportMock = $this->createMock(TransportFactoryInterface::class);
        $baggage = new BaggageSchemaResolver();
        $baggage->setSchema('default');

        $decorator = new DoctrineTransportFactoryDecorator(
            $doctrineTransportMock,
            $baggage,
            'default',
        );

        $doctrineTransportMock->expects(self::once())
            ->method('createTransport')
            ->with(
                self::equalTo(''),
                self::callback(function (array $options): bool {
                    self::assertArrayHasKey('table_name', $options);
                    self::assertEquals('messenger_messages', $options['table_name']);

                    return true;
                }),
            );

        $decorator->createTransport(
            '',
            ['table_name' => 'messenger_messages'],
            $this->createMock(SerializerInterface::class),
        );
    }
}
