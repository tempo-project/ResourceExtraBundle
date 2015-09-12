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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Mbechezi Mlanawo <mlanawo.mbechezi@ikimea.com>
 */

class ModelManager
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var DomainManager
     */
    protected $domainManager;

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var string
     */
    protected $class;

    /**
     * @param EntityManager $em
     * @param DomainManager $domainManager
     * @param $class
     */
    public function __construct(EntityManager $em, DomainManager $domainManager, $class)
    {
        $this->em = $em;
        $this->class = $class;
        $this->domainManager = $domainManager;
        $this->repository = $this->em->getRepository($this->class);
    }

    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function findOneBySlug($slug)
    {
        return $this->repository->findOneBySlug($slug);
    }

    /**
     * @return mixed
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * @param $user
     */
    public function findAllByUser($user)
    {
        return $this->repository->findAllByUser($user);
    }

    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Persist the given entity
     *
     * @param $entity  An entity instance
     * @param bool|true $doFlush Also flush  entity manager?
     * @return mixed
     */
    public function save($entity, $doFlush = true)
    {
        if (null === $entity->getId()) {
            $this->domainManager->create($entity, false);
        } else {
            $this->domainManager->update($entity, false);
        }

        if ($doFlush) {
            $this->domainManager->flush();
        }

        return $entity;
    }

    public function remove($entity, $doFlush = true)
    {
        $this->domainManager->delete($entity, false);

        if ($doFlush) {
            $this->domainManager->flush();
        }
    }
}
