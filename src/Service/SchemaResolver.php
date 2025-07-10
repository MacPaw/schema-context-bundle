<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Service;

class SchemaResolver
{
    private ?string $schema = null;

    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }
}
