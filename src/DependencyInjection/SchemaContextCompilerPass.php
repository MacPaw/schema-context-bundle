<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SchemaContextCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
    }
}
