<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\DependencyInjection;

use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SchemaContextExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $headerName = $config['header_name'];
        $environmentSchema = $config['environment_schema'];
        $environmentName = $config['environment_name'];
        $overridableEnvironments = $config['overridable_environments'];

        if (!is_string($headerName)) {
            throw new LogicException('Configuration "header_name" must be a string');
        }

        if (!is_string($environmentSchema)) {
            throw new LogicException('Configuration "environment_schema" must be a string');
        }

        if (!is_string($environmentName)) {
            throw new LogicException('Configuration "environment_name" must be a string');
        }

        if (!is_array($overridableEnvironments)) {
            throw new LogicException('Configuration "overridable_environments" must be an array');
        }

        $container->setParameter('schema_context.header_name', $headerName);
        $container->setParameter('schema_context.environment_schema', $environmentSchema);
        $container->setParameter('schema_context.environment_name', $environmentName);
        $container->setParameter('schema_context.overridable_environments', $overridableEnvironments);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.yaml');
    }
}
