<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class BaggageStamp implements StampInterface
{
    public function __construct(public string $schema, public string $baggage)
    {
    }
}
