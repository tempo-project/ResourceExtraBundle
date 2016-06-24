<?php

/*
* This file is part of the Tempo-project package http://tempo-project.org/>.
*
* (c) Mlanawo Mbechezi  <mlanawo.mbechezi@ikimea.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tempo\Bundle\ResourceExtraBundle\Tests\Manager;

use Tempo\Bundle\ResourceExtraBundle\Manager\DomainManager;
use Tempo\Bundle\ResourceExtraBundle\Tests\UserModel;

class DomainManagerTest extends \PHPUnit_Framework_TestCase
{
    private $domainManager;

    protected function setUp()
    {
        $this->domainManager = new DomainManager(
            'tempo', 
            $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
            array()
        );
    }

    public function testGetEventName()
    {
        $reflector = new \ReflectionMethod($this->domainManager, 'getEventName');
        $reflector->setAccessible(true);

        $eventName = $reflector->invoke(
            $this->domainManager,
            new UserModel(),
            'pre_create',
            'tempo'
        );

        $this->assertEquals('tempo.usermodel.pre_create', $eventName);
    }
}
