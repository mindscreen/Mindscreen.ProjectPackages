<?php

namespace Mindscreen\ProjectPackages\Domain\Repository;


use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository as DoctrineRepository;

/**
 * @Flow\Scope("singleton")
 */
class RepositoryRepository extends DoctrineRepository
{

    /**
     * @param Repository $repository
     * @return bool
     */
    public function exists(Repository $repository)
    {
        $queryResult = $this->findOneBySourceAndId($repository->getRepositorySource(), $repository->getId());
        return $queryResult !== null && !empty($queryResult);
    }

    /**
     * Remove all previous repositories with this id and source
     * @param string $source
     * @param string $id
     */
    public function removeBySourceAndId($source, $id)
    {
        $query = $this->createDqlQuery('DELETE FROM ' . Repository::class . " r
            WHERE r.repositorySource=:source AND r.id=:id")
            ->setParameter('source', $source)
            ->setParameter('id', $id);
        $query->execute();
    }

    /**
     * Remove all repositories originating from the given source
     * @param string $source
     */
    public function removeBySource($source)
    {
        $query = $this->createDqlQuery('DELETE FROM ' . Repository::class . " r
            WHERE r.repositorySource=:source")
            ->setParameter('source', $source);
        $query->execute();
    }

    /**
     * @param string $source
     * @param string $id
     * @return mixed
     */
    public function findOneBySourceAndId($source, $id)
    {
        $query = $this->createDqlQuery('SELECT r FROM ' . Repository::class . " r
            WHERE r.repositorySource=:source AND r.id=:id")
            ->setParameter('source', $source)
            ->setParameter('id', $id);
        return $query->execute();
        /*$queryBuilder = $this->createQueryBuilder('r')
            ->where("r.repositorySource=':source'")
            ->andWhere("r.id=':id'")
            ->setParameter('source', $source)
            ->setParameter('id', $id);
        $query = $queryBuilder->getQuery();
        return $query->execute();*/
    }
}