<?php

namespace Mindscreen\ProjectPackages\Domain\Repository;


use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Neos\Flow\Persistence\Doctrine\Repository as DoctrineRepository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ProjectRepository extends DoctrineRepository
{
    public function removeByRepositoryAndPackageManager(Repository $repository, $packageManager)
    {
        $query = $this->createDqlQuery('DELETE FROM ' . Project::class . " p
            WHERE p.repository=:repository AND p.packageManager=:packageManager")
            ->setParameter('repository', $repository)
            ->setParameter('packageManager', $packageManager);
        $query->execute();
    }
}