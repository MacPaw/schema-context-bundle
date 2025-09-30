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
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'default',
            'test-app',
            ['test-app'],
            [],
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
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'default',
            'test-app',
            ['test-app'],
            [],
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
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'fallback',
            'test-app',
            ['test-app'],
            []
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('fallback', $resolver->getSchema());
        self::assertNull($resolver->getBaggage());
    }

    public function testAppNameIsAllowed(): void
    {
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'fallback',
            'test-app',
            ['test-app'],
            [],
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        $reflection = new \ReflectionClass(BaggageRequestListener::class);
        $reflectionMethod = $reflection->getMethod('isAllowedAppName');
        $reflectionMethod->setAccessible(true);

        self::assertTrue($reflectionMethod->invoke($listener));
        self::assertSame('fallback', $resolver->getSchema());
        self::assertNull($resolver->getBaggage());
    }

    public function testAppNameIsNotAllowed(): void
    {
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'fallback',
            'staging',
            ['test-app'],
            [],
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        $reflection = new \ReflectionClass(BaggageRequestListener::class);
        $reflectionMethod = $reflection->getMethod('isAllowedAppName');
        $reflectionMethod->setAccessible(true);

        self::assertFalse($reflectionMethod->invoke($listener));
        self::assertNull($resolver->getSchema());
        self::assertNull($resolver->getBaggage());
    }

    public function testAppNameIsNotAllowedByRegex(): void
    {
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'fallback',
            'staging',
            ['test-app'],
            ['/^prod$/'],
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        $reflection = new \ReflectionClass(BaggageRequestListener::class);
        $reflectionMethod = $reflection->getMethod('isAllowedAppName');
        $reflectionMethod->setAccessible(true);

        self::assertFalse($reflectionMethod->invoke($listener));
        self::assertNull($resolver->getSchema());
        self::assertNull($resolver->getBaggage());
    }

    public function testAppNameIsAllowedByRegex(): void
    {
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $listener = new BaggageRequestListener(
            $resolver,
            $baggageCodec,
            'X-Schema',
            'fallback',
            'pr-100',
            ['test-app'],
            ['/^pr-\d+$/'],
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        $reflection = new \ReflectionClass(BaggageRequestListener::class);
        $reflectionMethod = $reflection->getMethod('isAllowedAppName');
        $reflectionMethod->setAccessible(true);

        self::assertTrue($reflectionMethod->invoke($listener));
        self::assertEquals('fallback', $resolver->getSchema());
        self::assertNull($resolver->getBaggage());
    }
}
