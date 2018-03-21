<?php

namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;

class FallbackStrategy extends AbstractProjectStrategy
{

    protected static $priority = 1;

    public static function isApplicable(RepositorySourceInterface $repositorySource, Repository $repository)
    {
        return true;
    }

    /**
     * @return void
     */
    public function evaluate()
    {
        $this->initializeProject();
        $this->project->setPackageManager(Project::PACKAGEMGR_UNKNOWN);
        $this->persist();
    }
}