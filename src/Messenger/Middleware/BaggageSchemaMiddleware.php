<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Messenger\Middleware;

use Macpaw\SchemaContextBundle\Messenger\Stamp\BaggageSchemaStamp;
use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class BaggageSchemaMiddleware implements MiddlewareInterface
{
    public function __construct(
        private BaggageSchemaResolver $baggageSchemaResolver,
        private BaggageCodec $baggageCodec
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(BaggageSchemaStamp::class);

        if ($stamp instanceof BaggageSchemaStamp) {
            $this->baggageSchemaResolver
                ->setSchema($stamp->schema)
                ->setBaggage($this->baggageCodec->decode($stamp->baggage));

            return $stack->next()->handle($envelope, $stack);
        }

        $schema = $this->baggageSchemaResolver->getSchema();
        $baggage = $this->baggageCodec->encode($this->baggageSchemaResolver->getBaggage() ?? []);

        if ($schema !== null && $schema !== '') {
            $envelope = $envelope->with(new BaggageSchemaStamp($schema, $baggage));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
