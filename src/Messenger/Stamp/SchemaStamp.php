<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class SchemaStamp implements StampInterface
{
    public function __construct(public string $schema)
    {
    }
}
