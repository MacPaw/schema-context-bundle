<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SchemaContextExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('schema_context.header_name', $config['header_name']);
        $container->setParameter('schema_context.default_schema', $config['default_schema']);
        $container->setParameter('schema_context.app_name', $config['app_name']);
        $container->setParameter('schema_context.allowed_app_names', $config['allowed_app_names']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.yaml');
    }
}
