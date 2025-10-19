<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Service;

use Macpaw\SchemaContextBundle\Exception\EnvironmentSchemaMismatchException;

class BaggageSchemaResolver
{
    /**
     * @var array<string,string|null>|null
     */
    private ?array $baggage = null;
    private ?string $schema = null;
    private bool $isSchemaOverridableEnvironment;

    /**
     * @param string[] $schemaOverridableEnvironments
     */
    public function __construct(
        private readonly string $environmentSchema,
        private readonly string $environmentName,
        private readonly array $schemaOverridableEnvironments,
    ) {
        $this->isSchemaOverridableEnvironment = in_array(
            $this->environmentName,
            $this->schemaOverridableEnvironments,
            true
        );
    }

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
    public function setBaggage(?array $baggage): self
    {
        if (is_array($baggage) && count($baggage) <= 0) {
            $baggage = null;
        }

        $this->baggage = $baggage;

        return $this;
    }

    public function setSchema(?string $schema): self
    {
        if (is_string($schema)) {
            $schema = trim($schema);

            $schema = $schema !== '' ? $schema : null;
        }

        // check that the schema hasn't changed in a non-schema-overridable environment
        if (
            $this->isSchemaOverridableEnvironment === false
            && $schema !== null
            && $schema !== $this->environmentSchema
        ) {
            throw new EnvironmentSchemaMismatchException(
                $schema,
                $this->environmentSchema,
                $this->environmentName,
                $this->schemaOverridableEnvironments,
            );
        }

        $this->schema = $schema;

        return $this;
    }

    public function getSchema(): string
    {
        if ($this->isSchemaOverridableEnvironment === true && $this->schema !== null && $this->schema !== '') {
            return $this->schema;
        }

        return $this->environmentSchema;
    }

    public function isSchemaOverridableEnvironment(): bool
    {
        return $this->isSchemaOverridableEnvironment;
    }

    public function getEnvironmentSchema(): string
    {
        return $this->environmentSchema;
    }

    public function getProvidedSchema(): ?string
    {
        return $this->schema;
    }

    public function reset(): void
    {
        $this->baggage = null;
        $this->schema = null;
    }
}
