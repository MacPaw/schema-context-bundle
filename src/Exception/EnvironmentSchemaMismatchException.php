<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentSchemaMismatchException extends Exception
{
    /**
     * @param string[] $schemaOverridableEnvironments
     */
    public function __construct(
        string $actualSchema,
        string $environmentSchema,
        string $environmentName,
        array $schemaOverridableEnvironments,
    ) {
        parent::__construct(
            sprintf(
                'Schema mismatch in "%s" environment: expected "%s", got "%s". Allowed override environments: [%s].',
                $environmentName,
                $environmentSchema,
                $actualSchema,
                implode(', ', $schemaOverridableEnvironments)
            ),
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
