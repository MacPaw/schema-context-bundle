<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\EventListener;

use Macpaw\SchemaContextBundle\EventListener\SchemaRequestListener;
use Macpaw\SchemaContextBundle\Service\SchemaResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SchemaRequestListenerTest extends TestCase
{
    public function testSchemaFromHeaderIsSet(): void
    {
        $resolver = new SchemaResolver();
        $listener = new SchemaRequestListener(
            $resolver,
            'X-Schema',
            'default',
            'test-app',
            ['test-app'],
        );

        $request = new Request([], [], [], [], [], ['HTTP_X_SCHEMA' => 'tenant1']);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('tenant1', $resolver->getSchema());
    }

    public function testDefaultSchemaIsUsedIfHeaderMissing(): void
    {
        $resolver = new SchemaResolver();
        $listener = new SchemaRequestListener(
            $resolver,
            'X-Schema',
            'fallback',
            'test-app',
            ['test-app'],
        );

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        self::assertSame('fallback', $resolver->getSchema());
    }
}
