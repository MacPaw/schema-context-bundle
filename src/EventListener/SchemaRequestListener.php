<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\EventListener;

use Macpaw\SchemaContextBundle\Service\SchemaResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SchemaRequestListener implements EventSubscriberInterface
{
    public function __construct(
        private SchemaResolver $schemaResolver,
        private string $schemaRequestHeader,
        private string $defaultSchema,
        private string $appName,
        /** @var string[] */
        private array $allowedAppNames,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->isAllowedAppName()) {
            return;
        }

        $request = $event->getRequest();
        $baggage = $request->headers->get('baggage');

        if ($baggage) {
            foreach (explode(',', $baggage) as $part) {
                [$key, $value] = array_map(
                    static fn(?string $v): ?string => $v !== null ? trim($v) : null,
                    explode('=', $part, 2) + [null, null]
                );

                if ($key === $this->schemaRequestHeader && $value !== null) {
                    $schema = $value;
                    break;
                }
            }
        }

        $schema ??= $this->defaultSchema;

        if ($schema !== null && $schema !== '') {
            $this->schemaResolver->setSchema($schema);
        }
    }

    private function isAllowedAppName(): bool
    {
        return in_array($this->appName, $this->allowedAppNames, true);
    }
}
