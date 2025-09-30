<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Monolog;

use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;

class BaggageProcessor
{
    public function __construct(
        private readonly BaggageSchemaResolver $baggageSchemaResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    public function __invoke(array $record): array
    {
        $baggage = $this->baggageSchemaResolver->getBaggage();

        if (is_array($baggage) && count($baggage) > 0) {
            if (!isset($record['extra']) || !is_array($record['extra'])) {
                $record['extra'] = [];
            }

            $record['extra']['baggage'] = $baggage;
        }

        return $record;
    }
}
