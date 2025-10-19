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
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class BaggageSchemaMiddlewareTest extends TestCase
{
    public function testSchemaIsSetFromStamp(): void
    {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $schema = 'tenant1';
        $rawBaggage = 'X-Schema=tenant1';
        $baggage = [
            'X-Schema' => 'tenant1',
        ];

        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $baggageCodec = new BaggageCodec();
        $middleware = new BaggageSchemaMiddleware($resolver, $baggageCodec);
        $stamp = new BaggageSchemaStamp($schema, $rawBaggage);
        $envelope = (new Envelope(new \stdClass()))->with($stamp);
        $envelope = $envelope->with(new ReceivedStamp('async'));
        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = new class implements MiddlewareInterface {
            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                /** @var BaggageSchemaStamp|null $stamp */
                $stamp = $envelope->last(BaggageSchemaStamp::class);

                return new Envelope((object) [
                    'schema' => $stamp?->schema,
                    'baggage' => $stamp?->baggage,
                ]);
            }
        };

        $stack->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware);

        $envelope = $middleware->handle($envelope, $stack);

        $result = (array) $envelope->getMessage();

        $this->assertSame($schema, $result['schema']);
        $this->assertSame($baggage, $baggageCodec->decode($result['baggage']));
        $this->assertSame($environmentSchema, $resolver->getSchema());
        $this->assertNull($resolver->getBaggage());
    }

    public function testSchemaStampIsInjectedIfMissing(): void
    {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $schema = 'tenant1';
        $rawBaggage = 'X-Schema=tenant1';
        $baggage = [
            'X-Schema' => 'tenant1',
        ];
        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $resolver
            ->setSchema($schema)
            ->setBaggage($baggage);
        $baggageCodec = new BaggageCodec();
        $middleware = new BaggageSchemaMiddleware($resolver, $baggageCodec);
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
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];

        $resolver = new BaggageSchemaResolver($environmentSchema, $environmentName, $schemaOverridableEnvironments);
        $baggageCodec = new BaggageCodec();
        $middleware = new BaggageSchemaMiddleware($resolver, $baggageCodec);
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
        $this->assertSame($environmentSchema, $stamp->schema);
        $this->assertNull($stamp->baggage);
    }
}
