<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\Messenger\Middleware;

use Macpaw\SchemaContextBundle\Messenger\Middleware\BaggageSchemaMiddleware;
use Macpaw\SchemaContextBundle\Messenger\Stamp\BaggageSchemaStamp;
use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class BaggageSchemaMiddlewareTest extends TestCase
{
    public function testSchemaIsSetFromStamp(): void
    {
        $schema = 'tenant1';
        $rawBaggage = 'X-Schema=tenant1';
        $baggage = [
            'X-Schema' => 'tenant1',
        ];

        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $middleware = new BaggageSchemaMiddleware($resolver, $baggageCodec, 'public');
        $stamp = new BaggageSchemaStamp($schema, $rawBaggage);
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

        $this->assertSame($schema, $resolver->getSchema());
        $this->assertSame($baggage, $resolver->getBaggage());
    }

    public function testSchemaStampIsInjectedIfMissing(): void
    {
        $schema = 'tenant1';
        $rawBaggage = 'X-Schema=tenant1';
        $baggage = [
            'X-Schema' => 'tenant1',
        ];
        $resolver = new BaggageSchemaResolver();
        $resolver
            ->setSchema($schema)
            ->setBaggage($baggage);
        $baggageCodec = new BaggageCodec();
        $middleware = new BaggageSchemaMiddleware($resolver, $baggageCodec, 'public');
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

        $stamp = $resultEnvelope->last(BaggageSchemaStamp::class);

        $this->assertInstanceOf(BaggageSchemaStamp::class, $stamp);
        $this->assertSame($schema, $stamp->schema);
        $this->assertSame($rawBaggage, $stamp->baggage);
    }

    public function testSchemaStampIsDefaultSchema(): void
    {
        $resolver = new BaggageSchemaResolver();
        $baggageCodec = new BaggageCodec();
        $middleware = new BaggageSchemaMiddleware($resolver, $baggageCodec, 'public');
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

        $stamp = $resultEnvelope->last(BaggageSchemaStamp::class);

        $this->assertInstanceOf(BaggageSchemaStamp::class, $stamp);
        $this->assertSame('public', $stamp->schema);
        $this->assertSame('', $stamp->baggage);
    }
}
