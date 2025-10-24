<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Messenger\Middleware;

use Macpaw\SchemaContextBundle\Messenger\Stamp\BaggageSchemaStamp;
use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;

class BaggageSchemaMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SendersLocatorInterface $sendersLocator,
        private BaggageSchemaResolver $baggageSchemaResolver,
        private BaggageCodec $baggageCodec,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(BaggageSchemaStamp::class);

        if ($this->isWorker($envelope) && !$this->isSyncTransport($envelope)) {
            if ($stamp instanceof BaggageSchemaStamp) {
                $this->baggageSchemaResolver
                    ->setSchema($stamp->schema)
                    ->setBaggage($stamp->baggage === null ? null : $this->baggageCodec->decode($stamp->baggage));
            }

            $result = $stack->next()->handle($envelope, $stack);

            $this->baggageSchemaResolver->reset();

            return $result;
        }

        $schema = $this->baggageSchemaResolver->getSchema();
        $baggage = $this->baggageSchemaResolver->getBaggage() === null
            ? null
            : $this->baggageCodec->encode($this->baggageSchemaResolver->getBaggage());

        $envelope = $envelope->with(new BaggageSchemaStamp($schema, $baggage));

        return $stack->next()->handle($envelope, $stack);
    }

    private function isWorker(Envelope $envelope): bool
    {
        return (bool) $envelope->last(ReceivedStamp::class);
    }

    private function isSyncTransport(Envelope $envelope): bool
    {
        foreach ($this->sendersLocator->getSenders($envelope) as $sender) {
            if ($sender instanceof SyncTransport) {
                return true;
            }
        }

        return false;
    }
}
