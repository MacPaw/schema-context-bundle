<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Messenger\Middleware;

use Macpaw\SchemaContextBundle\Messenger\Stamp\SchemaStamp;
use Macpaw\SchemaContextBundle\Service\SchemaResolver;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class SchemaMiddleware implements MiddlewareInterface
{
    public function __construct(private SchemaResolver $schemaResolver)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(SchemaStamp::class);

        if ($stamp instanceof SchemaStamp) {
            $this->schemaResolver->setSchema($stamp->schema);

            return $stack->next()->handle($envelope, $stack);
        }

        $schema = $this->schemaResolver->getSchema();

        if ($schema !== null && $schema !== '') {
            $envelope = $envelope->with(new SchemaStamp($schema));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
