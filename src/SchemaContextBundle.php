<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle;

use Macpaw\SchemaContextBundle\DependencyInjection\SchemaContextCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SchemaContextBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new SchemaContextCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            10
        );
    }
}
