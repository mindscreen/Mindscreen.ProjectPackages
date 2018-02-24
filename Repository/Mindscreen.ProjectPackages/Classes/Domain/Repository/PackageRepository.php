<?php
namespace Mindscreen\ProjectPackages\Domain\Repository;


use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PackageRepository extends Repository
{
    public function findByProjectAndDepth($project, $depth = 0)
    {
        return $this->findBy(['project' => $project, 'depth' => $depth], ['name'=>'ASC']);
    }
}