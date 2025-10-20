<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\EventListener;

use Macpaw\SchemaContextBundle\EventListener\BaggageRequestListener;
use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BaggageRequestListenerTest extends TestCase
{
    public function testBaggageFromHeaderIsSet(): void
    {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
        );

        $request = new Request([], [], [], [], [], ['HTTP_BAGGAGE' => 'X-Schema=tenant1']);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('tenant1', $resolver->getSchema());
        self::assertSame([
            'X-Schema' => 'tenant1',
        ], $resolver->getBaggage());
    }

    public function testBaggageFromHeaderIsSetWithMultiplyParameters(): void
    {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
        );

        $request = new Request([], [], [], [], [], ['HTTP_BAGGAGE' => 'X-Schema= tenant1 ,test , foo=bar']);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('tenant1', $resolver->getSchema());
        self::assertSame([
            'X-Schema' => 'tenant1',
            'test' => null,
            'foo' => 'bar',
        ], $resolver->getBaggage());
    }

    public function testDefaultSchemaIsUsedIfHeaderMissing(): void
    {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('default', $resolver->getSchema());
        self::assertNull($resolver->getBaggage());
    }

    // TODO!!
    public function testFail(): void
    {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
        );

        $request = new Request([], [], [], [], [], ['HTTP_BAGGAGE' => 'X-Schema=tenant1']);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('tenant1', $resolver->getSchema());
        self::assertSame([
            'X-Schema' => 'tenant1',
        ], $resolver->getBaggage());
    }
}
