<?php

namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;

class VagrantProjectStrategy extends FallbackStrategy
{

    protected static $priority = 10;

    /**
     * Checks whether a Strategy is applicable on a given repository.
     * This might be based on project files like composer.json, package.json etc.
     * to determine the repository type and how to handle it.
     * All available strategies will be tested for applicability based on their priority
     * (@see ProjectStrategyInterface::getPriority()).
     *
     * @param RepositorySourceInterface $repositorySource
     * @param Repository $repository
     * @return boolean
     */
    public static function isApplicable(RepositorySourceInterface $repositorySource, Repository $repository)
    {
        return $repositorySource->fileExists($repository->getId(), 'Vagrantfile', $repository->getDefaultBranch());
    }

    /**
     * @return void
     */
    public function evaluate()
    {
        $this->initializeProject();
        $readMeFiles = $this->repositorySource->findFile($this->getRepositoryId(), 'readme\.(rst|md)', false, null, '', false);
        $found = false;
        foreach ($readMeFiles as $file) {
            if (strpos($file, '/') === false) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->project->addMessage(new Message('No readme file found', null, Message::SEVERITY_NOTICE));
        }
        // find ip
        $vagrantFile = $this->repositorySource->getFileContents($this->repository->getId(), 'Vagrantfile', $this->repository->getDefaultBranch());
        if (preg_match('/config\.vm\.network.+?,\s+ip:\s+"(.*?)"/', $vagrantFile, $match) !== false) {
            $this->project->setDescription('IP: ' . $match[1]);
        }
        $this->project->setPackageManager('vagrant');
        $this->persist();
    }
}