<?php

namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Domain\Repository\ProjectRepository;
use Mindscreen\ProjectPackages\Domain\Repository\RepositoryRepository;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\UnknownObjectException;
use Neos\Flow\Persistence\PersistenceManagerInterface;

abstract class AbstractProjectStrategy implements ProjectStrategyInterface
{
    /**
     * @var int
     */
    protected static $priority = -1;

    /**
     * @var RepositorySourceInterface
     */
    protected $repositorySource;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @Flow\Inject
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @Flow\Inject
     * @var RepositoryRepository
     */
    protected $repositoryRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @return int
     */
    public static function getPriority()
    {
        return static::$priority;
    }

    public function hasErrors()
    {
        return $this->project !== null
            && is_array($this->project->getMessages())
            && count($this->project->getMessages()) > 0;
    }

    /**
     * @return array|\Mindscreen\ProjectPackages\Domain\Model\Message[]
     */
    public function getErrors()
    {
        return $this->project !== null && is_array($this->project->getMessages())
            ? $this->project->getMessages()
            : [];
    }

    /**
     * @return int
     */
    public function getRepositoryId()
    {
        return $this->repository->getId();
    }

    /**
     * @param RepositorySourceInterface $repositorySource
     */
    public function setRepositorySource(RepositorySourceInterface $repositorySource): void
    {
        $this->repositorySource = $repositorySource;
    }

    /**
     * @param Repository $repository
     */
    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    protected function getBranch()
    {
        return $this->repository->getDefaultBranch();
    }

    protected function persist($force = false)
    {
        try {
            $this->repositoryRepository->update($this->repository);
        } catch (UnknownObjectException $e) {
            $this->repositoryRepository->add($this->repository);
        }
        $this->projectRepository->add($this->project);
        if ($force) {
            $this->persistenceManager->persistAll();
        }
    }

    protected function initializeProject()
    {
        $this->project = new Project();
        $this->project->setRepository($this->repository);
        $this->project->setName($this->repository->getFullName());
        $this->repository->addProject($this->project);
    }
}