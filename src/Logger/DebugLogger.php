<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DebugLogger
{
    private readonly LoggerInterface $logger;

    public function __construct(
        LoggerInterface|null $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /** @param array<string, string|null>|null $baggage */
    public function log(string $message, ?array $baggage = null, ?string $schema = null): void
    {
        $this->logger->info(
            'schema_context_' . $message,
            [
                'schema' => $schema,
                'baggage' => $baggage,
            ]
        );
    }

    /** @param array<string, string|null>|null $baggage */
    public function logInfoFromRequest(array|null $baggage, string|null $schema): void
    {
        $this->log('info_from_request', $baggage, $schema);
    }

    /** @param array<string, string|null> $baggage */
    public function logHttpRequest(array $baggage): void
    {
        $this->log('http_request', $baggage);
    }

    /** @param array<string, string|null>|null $baggage */
    public function logSetBaggage(array|null $baggage): void
    {
        $this->log('set_baggage', $baggage);
    }

    public function logSetSchema(string|null $schema): void
    {
        $this->log('set_schema', null, $schema);
    }

    /** @param array<string, string|null>|null $baggage */
    public function logInfoFromStamp(array|null $baggage, string|null $schema): void
    {
        $this->log('info_from_stamp', $baggage, $schema);
    }

    public function logResetWorkerAfterWorker(): void
    {
        $this->log('reset_worker_after_worker');
    }

    /** @param array<string, string|null>|null $baggage */
    public function logCreateMessage(array|null $baggage, string|null $schema): void
    {
        $this->log('create_message', $baggage, $schema);
    }

    public function logApplySearchPath(string $schema, bool $isNewConnection, bool $isInTransaction): void
    {
        $this->logger->info('schema_context_apply_search_path', [
            'schema' => $schema,
            'isNewConnection' => $isNewConnection,
            'inInTransaction' => $isInTransaction,
        ]);
    }

    public function logSkipSearchPath(string $schema, bool $isInTransaction): void
    {
        $this->logger->info('schema_context_skip_search_path', [
            'schema' => $schema,
            'inInTransaction' => $isInTransaction,
        ]);
    }

    public function logActualSearchPath(string|null $actualScheme): void
    {
        $this->logger->info('schema_context_actual_search_path', [
            'actualScheme' => $actualScheme,
        ]);
    }

    public function logSchemaResetByTransactionRollback(string $schema): void
    {
        $this->logger->info('schema_context_schema_reset_by_transaction_rollback', [
            'schema' => $schema,
        ]);
    }

    public function logSchemaResetByConnectionClose(string $schema): void
    {
        $this->logger->info('schema_context_schema_reset_by_connection_close', [
            'schema' => $schema,
        ]);
    }
}
