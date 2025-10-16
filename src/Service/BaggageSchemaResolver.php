<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Service;

class BaggageSchemaResolver
{
    /**
     * @var array<string,string|null>|null
     */
    private ?array $baggage = null;
    private ?string $schema = null;

    /**
     * @return array<string,string|null>|null
     */
    public function getBaggage(): ?array
    {
        return $this->baggage;
    }

    /**
     * @param array<string,string|null> $baggage
     */
    public function setBaggage(array $baggage): self
    {
        $this->baggage = $baggage;

        return $this;
    }

    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public function reset(): void
    {
        $this->baggage = null;
        $this->schema = null;
    }
}
