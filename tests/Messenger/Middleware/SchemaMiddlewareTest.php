<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\Messenger\Middleware;

use Macpaw\SchemaContextBundle\Messenger\Middleware\SchemaMiddleware;
use Macpaw\SchemaContextBundle\Messenger\Stamp\SchemaStamp;
use Macpaw\SchemaContextBundle\Service\SchemaResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class SchemaMiddlewareTest extends TestCase
{
    public function testSchemaIsSetFromStamp(): void
    {
        $resolver = new SchemaResolver();
        $middleware = new SchemaMiddleware($resolver);
        $stamp = new SchemaStamp('tenant1');
        $envelope = (new Envelope(new \stdClass()))->with($stamp);
        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = new class implements MiddlewareInterface {
            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                return $envelope;
            }
        };

        $stack->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware);

        $middleware->handle($envelope, $stack);

        $this->assertSame('tenant1', $resolver->getSchema());
    }

    public function testSchemaStampIsInjectedIfMissing(): void
    {
        $schema = 'tenant1';
        $resolver = new SchemaResolver();
        $resolver->setSchema($schema);
        $middleware = new SchemaMiddleware($resolver);
        $originalEnvelope = new Envelope(new \stdClass());
        $stack = $this->createMock(StackInterface::class);

        $stack->expects($this->once())
            ->method('next')
            ->willReturnCallback(function () {
                return new class implements MiddlewareInterface {
                    public function handle(Envelope $envelope, StackInterface $stack): Envelope
                    {
                        return $envelope;
                    }
                };
            });

        $resultEnvelope = $middleware->handle($originalEnvelope, $stack);

        $stamp = $resultEnvelope->last(SchemaStamp::class);

        $this->assertInstanceOf(SchemaStamp::class, $stamp);
        $this->assertSame($schema, $stamp->schema);
    }
}
