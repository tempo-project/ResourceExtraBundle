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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Tempo\Bundle\ResourceExtraBundle\Util\ClassUtils;
use Tempo\Bundle\ResourceExtraBundle\Manager\DomainManager;

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
        $this->createRepositoriesServices($container,$config);
        $this->createAdminServices($container,$config);

        return $config;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createDomainManager(ContainerBuilder $container)
    {
        $container
            ->register(sprintf('%s.domain_manager', $this->applicationName), DomainManager::class)
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('event_dispatcher'));
    }

    /**
     * @param ContainerBuilder $container
     * @param $config
     */
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

    /**
     * @param ContainerBuilder $container
     * @param $config
     */
    protected function createAdminServices(ContainerBuilder $container, $config)
    {
        foreach ($config['admin'] as $resourceName => $conf) {
            if (!isset($conf['controller'])) {
                $conf['controller'] = $config['controller_admin'];
            }

            $container->setDefinition(
                sprintf('%s.admin.controller.%s', $this->applicationName, $resourceName),
                $this->getControllerDefinition($conf['controller'], $resourceName)
            );
        }
    }

    /**
     * @param $class
     * @param $resourceName
     * @return Definition
     */
    protected function getControllerDefinition($class, $resourceName)
    {
        $definition = new Definition($class);
        $definition
            ->setArguments(array(
                $this->getConfigurationDefinition($resourceName),
                new Reference('sylius.resource_controller.request_configuration_factory'),
                new Reference('sylius.resource_controller.view_handler'),
                new Reference(sprintf('%s.repository.%s', $this->applicationName, $resourceName)),
                new Reference(sprintf('%s.factory.%s', $this->applicationName, $resourceName)),
                new Reference('sylius.resource_controller.new_resource_factory'),
                new Reference('doctrine.orm.default_entity_manager'),
                new Reference('sylius.resource_controller.single_resource_provider'),
                new Reference('sylius.resource_controller.resources_collection_provider'),
                new Reference('sylius.resource_controller.form_factory'),
                new Reference('sylius.resource_controller.redirect_handler'),
                new Reference('sylius.resource_controller.flash_helper'),
                new Reference('sylius.resource_controller.authorization_checker.disabled'),
                new Reference('sylius.resource_controller.event_dispatcher'),
            ))
            ->addMethodCall('setContainer', array(new Reference('service_container')));

        return $definition;
    }

    /**
     * @param $resourceName
     * @return Definition
     */
    protected function getConfigurationDefinition($resourceName)
    {
        $definition = new Definition('Sylius\Component\Resource\Metadata\Metadata');
        $definition
            ->setFactory(array(
                new Reference('sylius.resource_registry'),
                'get'
            ))
            ->setArguments(array($this->applicationName. '.'.$resourceName));

        return $definition;
    }

    /**
     * @param $container
     */
    protected function createRepositoriesServices(Container $container)
    {
        $classes = $container->getParameter('sylius.resources');

        foreach ($classes as $key => $class) {
            if (!isset($class['classes']['repository']) ) {
                continue;
            }

            $resourceName = str_replace($this->applicationName,'', $key);
            $definition = new Definition($class['classes']['repository'], array(
                new Reference('doctrine'),
                new Reference('doctrine.orm.default_metadata_cache')
            ));

            $container->setDefinition(
                sprintf('%s.repository.group',$this->applicationName,$resourceName),
                $definition
            );
        }
    }
}
