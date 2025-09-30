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
                ->scalarNode('default_schema')->defaultValue('public')->end()
                ->scalarNode('app_name')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('allowed_app_names')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('allowed_app_names_regex')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
