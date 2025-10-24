<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('schema_context');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('header_name')->defaultValue('X-Schema')->end()
                ->scalarNode('environment_name')->defaultValue('public')->end()
                ->scalarNode('environment_schema')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('overridable_environments')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
