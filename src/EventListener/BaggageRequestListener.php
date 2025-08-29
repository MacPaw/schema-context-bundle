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
        private string $defaultSchema,
        private string $appName,
        /** @var string[] */
        private array $allowedAppNames,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => [['onKernelRequest', 100]]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->isAllowedAppName()) {
            return;
        }

        $request = $event->getRequest();
        $baggage = $request->headers->get('baggage');

        $schema = null;
        if ($baggage) {
            $baggage = $this->baggageCodec->decode($baggage);
            $this->baggageSchemaResolver->setBaggage($baggage);

            $schema = $baggage[$this->schemaRequestHeader] ?? null;
        }

        if ($schema !== null && $schema !== '') {
            $this->baggageSchemaResolver->setSchema($schema);
        } else {
            $this->baggageSchemaResolver->setSchema($this->defaultSchema);
        }
    }

    private function isAllowedAppName(): bool
    {
        return in_array($this->appName, $this->allowedAppNames, true);
    }
}
