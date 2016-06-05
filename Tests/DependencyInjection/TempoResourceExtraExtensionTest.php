<?php

/*
* This file is part of the Tempo-project package http://tempo-project.org/>.
*
* (c) Mlanawo Mbechezi  <mlanawo.mbechezi@ikimea.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tempo\Bundle\ResourceExtraBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tempo\Bundle\ResourceExtraBundle\DependencyInjection\TempoResourceExtraExtension;

class TempoResourceExtraExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var TempoResourceExtraExtension
     */
    private $extension;


    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('sylius.resources', array(
            'tempo.user' => array (
                'driver' => 'doctrine/orm',
                'classes' => array (
                      'model' => 'Tempo\Bundle\AppBundle\Model\User',
                      'repository' => 'Tempo\Bundle\AppBundle\Repository\UserRepository',
                      'factory' => 'Tempo\Bundle\AppBundle\Factory\UserFactory',
                      'controller' => 'Sylius\Bundle\ResourceBundle\Controller\ResourceController'
                )
            )
        ));
        $this->extension = new TempoResourceExtraExtension();
    }

    public function testCreateDomainManager()
    {
        $reflectorProperty = new \ReflectionProperty($this->extension, 'applicationName');
        $reflectorProperty->setAccessible(true);
        $reflectorProperty->setValue($this->extension, 'foo');

        $reflector = new \ReflectionMethod($this->extension, 'createDomainManager');
        $reflector->setAccessible(true);
        $reflector->invoke($this->extension, $this->container);

        $this->assertTrue($this->container->hasDefinition('foo.domain_manager'));
    }

    public function testCreateAdminServices()
    {
        $reflectorProperty = new \ReflectionProperty($this->extension, 'applicationName');
        $reflectorProperty->setAccessible(true);
        $reflectorProperty->setValue($this->extension, 'foo');

        $reflector = new \ReflectionMethod($this->extension, 'createAdminServices');
        $reflector->setAccessible(true);
        $reflector->invoke($this->extension, $this->container, array(
            'admin' => array(
                'user' => array(
                    'controller' => 'Tempo\Bundle\AppBundle\Controller\Admin\UserController'
                )
            )
        ));

        $this->assertTrue($this->container->hasDefinition('foo.admin.controller.user'));

        $reflector->invoke($this->extension, $this->container, array(
            'admin' => array(
                'project' => array(
                    'controller' => 'Tempo\Bundle\AppBundle\Controller\Admin\ProjectController'
                )
            )
        ));

        $this->assertEquals(
            $this->container->getDefinition('foo.admin.controller.project')->getClass(),
            'Tempo\Bundle\AppBundle\Controller\Admin\ProjectController'
        );
        $this->assertTrue($this->container->hasDefinition('foo.admin.controller.project'));
    }
}
