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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Tempo\Bundle\ResourceExtraBundle\Util\ClassUtils;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TempoResourceExtraExtension extends AbstractResourceExtension
{
    /**
     * @var string
     */
    protected $applicationName = 'tempo';

    /**
     * @var string
     */
    protected $configDirectory = '/../Resources/config';

    /**
     * @var array
     */
    protected $configFiles = array();

    /**
     * {@inheritDoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $this->registerResources($this->applicationName, $config['driver'], array(), $container);

        if (isset($config['app_name'])) {
            $this->applicationName = $config['app_name'];
        }

        $this->createDomainManager($container);
        $this->createManagerServices($container,$config);
        $this->createAdminServices($container,$config);

        return $config;
    }


    protected function createDomainManager($container)
    {
        $class = 'Tempo\Bundle\ResourceExtraBundle\Manager\DomainManager';
        $container
            ->register(sprintf('%s.domain_manager', $this->applicationName), $class)
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument(new Reference('sylius.controller.parameters_parser'));
    }

    protected function createManagerServices(ContainerBuilder $container, $config)
    {
        $classes = $container->getParameter('sylius.resources');

        foreach ($classes as $class) {
            $model = $class['classes']['model'];
            $className = ClassUtils::getShortName($model, false);
            $manager = sprintf($config['model_manager'], ucfirst($className));

            if (!class_exists($manager)) {
                $manager = 'Tempo\Bundle\ResourceExtraBundle\Manager\ModelManager';
            }

            $container
                ->register(sprintf('%s.model_manager.%s', $this->applicationName, ClassUtils::uncamel($className)), $manager)
                ->addArgument(new Reference('doctrine.orm.entity_manager'))
                ->addArgument(new Reference($this->applicationName.'.domain_manager'))
                ->addArgument($model);
        }
    }

    protected function createAdminServices(ContainerBuilder $container, $config)
    {
        foreach ($config['admin'] as $resourceName => $conf) {
            if (!isset($conf['controller'])) {
                $conf['controller'] = $config['controller_admin'];
            }

            $container->setDefinition(
                sprintf('%s.admin.controller.%s',$this->applicationName,$resourceName),
                $this->getControllerDefinition($conf['controller'], $resourceName)
            );
        }
    }

    protected function getControllerDefinition($class, $resourceName)
    {
        $definition = new Definition($class);
        $definition
            ->setArguments(array($this->getConfigurationDefinition($resourceName)))
            ->addMethodCall('setContainer', array(new Reference('service_container')))
        ;

        return $definition;
    }

    protected function getConfigurationDefinition($resourceName)
    {
        $definition = new Definition('Sylius\Bundle\ResourceBundle\Controller\Configuration');
        $definition
            ->setFactory(array(
                new Reference('sylius.controller.configuration_factory'),
                'createConfiguration'
            ))
            ->setArguments(array($this->applicationName, $resourceName, ''))
            ->setPublic(false)
        ;

        return $definition;
    }
}
