<?php

/*
* This file is part of the Tempo-project package http://tempo-project.org/>.
*
* (c) Mlanawo Mbechezi  <mlanawo.mbechezi@ikimea.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tempo\Bundle\ResourceExtraBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Domain Manager
 *
 * @author Mbechezi Mlanawo <mlanawo.mbechezi@ikimea.com>
 * @author Jérémy Leherpeur <jeremy@leherpeur.net>
 */

class DomainManager
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct($prefix, ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->prefix    = $prefix;
        $this->objectManager    = $objectManager;
        $this->eventDispatcher  = $eventDispatcher;
    }

    /**
     * @param $resource
     * @param $name
     * @return Event
     */
    protected function dispatchEvent($resource, $name)
    {
        return $this->eventDispatcher->dispatch(
            $this->getEventName($resource, $name, $this->prefix),
            new GenericEvent($resource)
        );
    }

    /**
     * @param $resource
     * @param $name
     * @param $prefix
     * @return string
     */
    protected function getEventName($resource, $name, $prefix)
    {
        $resourceName = (new \ReflectionClass($resource))->getShortName();

        return sprintf('%s.%s.%s', $prefix, strtolower($resourceName), $name);
    }

    public function flush()
    {
        $this->objectManager->flush();
    }

    /**
     * @param $resource
     * @param bool $flush
     * @return Event
     */
    public function create($resource, $flush = true)
    {
        $event = $this->dispatchEvent($resource, 'pre_create');

        if ($event->isPropagationStopped()) {
            return $event;
        }

        $this->objectManager->persist($resource);
        if ($flush) {
            $this->objectManager->flush();
        }

        $this->dispatchEvent($resource, 'post_create');
    }

    /**
     * @param $resource
     * @param bool $flush
     * @return Event
     */
    public function update($resource, $flush = true)
    {
        $event = $this->dispatchEvent($resource, 'pre_update');

        if ($event->isPropagationStopped()) {
            return  $event;
        }

        $this->objectManager->persist($resource);
        if ($flush) {
            $this->objectManager->flush();
        }

        $this->dispatchEvent($resource, 'post_update');
    }

    /**
     * @param $resource
     * @param bool $flush
     * @return Event
     */
    public function delete($resource, $flush = true)
    {
        $event = $this->dispatchEvent($resource, 'pre_delete');

        if ($event->isPropagationStopped()) {
            return  $event;
        }

        $this->objectManager->remove($resource);
        if ($flush) {
            $this->objectManager->flush();
        }

        $this->dispatchEvent($resource, 'post_delete');
    }
}
