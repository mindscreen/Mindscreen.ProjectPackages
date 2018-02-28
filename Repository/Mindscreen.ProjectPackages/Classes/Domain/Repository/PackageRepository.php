<?php
namespace Mindscreen\ProjectPackages\Domain\Repository;


use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PackageRepository extends Repository
{
    public function findByProjectAndDepth($project, $depth = 0, $allowNull  = true)
    {
        $depthOptions = [$depth];
        if ($depth === 0 && $allowNull) {
            $depthOptions[] = null;
        }
        return $this->findBy(['project' => $project, 'depth' => $depthOptions], ['name'=>'ASC']);
    }
}