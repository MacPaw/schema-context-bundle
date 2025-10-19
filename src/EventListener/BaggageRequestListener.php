<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\EventListener;

use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BaggageRequestListener implements EventSubscriberInterface
{
    public function __construct(
        private BaggageSchemaResolver $baggageSchemaResolver,
        private BaggageCodec $baggageCodec,
        private string $schemaRequestHeader,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => [['onKernelRequest', 100]]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $headerBaggage = $request->headers->get('baggage');

        $baggage = null;
        $schema = null;

        if ($headerBaggage) {
            $baggage = $this->baggageCodec->decode($headerBaggage);
            $schema = $baggage[$this->schemaRequestHeader] ?? null;
        }

        $this->baggageSchemaResolver->setBaggage($baggage);
        $this->baggageSchemaResolver->setSchema($schema);
    }
}
