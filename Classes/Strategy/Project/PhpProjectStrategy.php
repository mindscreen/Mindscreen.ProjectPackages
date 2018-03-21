<?php

namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Exception\FileNotFoundException;
use Mindscreen\ProjectPackages\Exception\PermissionDeniedException;
use Mindscreen\ProjectPackages\Service\ComposerService;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;
use Neos\Flow\Annotations as Flow;

class PhpProjectStrategy extends FallbackStrategy
{
    protected static $priority = 10;

    /**
     * @Flow\Inject
     * @var ComposerService
     */
    protected $composerService;

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
        return $repositorySource->fileExists($repository->getId(), 'composer.json', $repository->getDefaultBranch());
    }


    public function evaluate()
    {
        $this->initializeProject();
        $composerJsonContent = $this->repositorySource->getFileContents($this->getRepositoryId(), 'composer.json', $this->getBranch());
        $composerJson = json_decode($composerJsonContent);
        $this->project = $this->composerService->evaluateComposerJson($composerJson, $this->project);
        $this->project->setPackageManager(Project::PACKAGEMGR_COMPOSER);
        try {
            $composerLockContent = $this->repositorySource->getFileContents($this->getRepositoryId(), 'composer.lock', $this->getBranch());
            $composerLock = json_decode($composerLockContent);
            $this->project = $this->composerService->evaluateComposerLock($composerJson, $composerLock, $this->project);
        } catch (FileNotFoundException $e) {
            $this->project->addMessage(new Message('No composer.lock file committed', $e->getCode()));
        } catch (PermissionDeniedException $e) {
            $this->project->addMessage(new Message('Could not read composer.lock file', $e->getCode()));
        }
        $this->persist();
    }
}