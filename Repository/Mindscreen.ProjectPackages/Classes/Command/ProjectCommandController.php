<?php

namespace Mindscreen\ProjectPackages\Command;


use Mindscreen\ProjectPackages\Domain\Repository\RepositoryRepository;
use Mindscreen\ProjectPackages\Service\RepositoryEvaluationService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class ProjectCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var RepositoryEvaluationService
     */
    protected $repositoryEvaluationService;

    /**
     * @Flow\Inject
     * @var RepositoryRepository
     */
    protected $repositoryRepository;

    /**
     * @Flow\InjectConfiguration("clients")
     * @var array
     */
    protected $repositorySourceConfigurations;

    /**
     * Update all projects from all VCS sources
     */
    public function updateAllCommand()
    {
        $this->repositoryEvaluationService->evaluateRepositorySources();
    }

    /**
     * @param string $sourceIdentifier
     */
    public function deleteBySourceCommand($sourceIdentifier)
    {
        $this->repositoryRepository->removeBySource($sourceIdentifier);
    }

    /**
     * @param string $sourceIdentifier
     * @throws \Mindscreen\ProjectPackages\Exception\MissingConfigurationException
     */
    public function updateFromSourceCommand($sourceIdentifier)
    {
        $this->repositoryEvaluationService->evaluateRepositorySource($sourceIdentifier);
    }

    /**
     * List all configured repository sources
     */
    public function listSourcesCommand()
    {
        foreach ($this->repositorySourceConfigurations as $client => $configuration) {
            $this->outputLine($client);
        }
    }
}