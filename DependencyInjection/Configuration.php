<?php

/*
* This file is part of the Tempo-project package http://tempo-project.org/>.
*
* (c) Mlanawo Mbechezi  <mlanawo.mbechezi@ikimea.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tempo\Bundle\ResourceExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('tempo')
            ->children()
                ->scalarNode('driver')->defaultValue('doctrine/orm')->end()
                ->scalarNode('app_name')->defaultValue('tempo')->end()
                ->arrayNode('admin')
                    ->prototype('variable')->end()
                    ->defaultValue(array())
                ->end()
                ->scalarNode('model_manager')->defaultValue('Tempo\Bundle\AppBundle\Manager\%sManager')->end()
                ->scalarNode('controller_admin')->defaultValue('Tempo\Bundle\AppBundle\Controller\Admin\AdminController')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
