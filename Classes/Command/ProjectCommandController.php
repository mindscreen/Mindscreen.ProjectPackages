<?php

namespace Mindscreen\ProjectPackages\Command;


use Mindscreen\ProjectPackages\Domain\Repository\RepositoryRepository;
use Mindscreen\ProjectPackages\Exception\MissingConfigurationException;
use Mindscreen\ProjectPackages\Factory\RepositorySourceFactory;
use Mindscreen\ProjectPackages\Service\RepositoryEvaluationService;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;
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
     * @Flow\Inject
     * @var RepositorySourceFactory
     */
    protected $repositorySourceFactory;

    /**
     * @var RepositorySourceInterface[]
     */
    protected $repositorySourceInstances = [];

    /**
     * Update all projects from all VCS sources
     */
    public function updateAllCommand()
    {
        $this->repositoryEvaluationService->evaluateRepositorySources();
    }

    /**
     * Given a list of repository URLs, separated by `,`, the given repositories are
     * updated. See `./flow help project:updateRepositoryByUrl` for more information.
     *
     * @param array $repositories
     */
    public function updateRepositoriesCommand($repositories)
    {
        \Neos\Flow\var_dump($repositories);
        return;
        foreach ($repositories as $repositoryUrl) {
            try {
                $this->updateRepositoryByUrlCommand($repositoryUrl);
            } catch (MissingConfigurationException $e) {
                $this->outputLine(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }
    }

    /**
     * Update a repository by it's URL like `https://git.example.com/vendor/project`.
     *
     * @param string $repositoryUrl
     * @throws \Mindscreen\ProjectPackages\Exception\MissingConfigurationException
     */
    public function updateRepositoryByUrlCommand($repositoryUrl)
    {
        foreach ($this->repositorySourceConfigurations as $sourceIdentifier => $configuration) {
            if (!array_key_exists('type', $configuration)) {
                throw new MissingConfigurationException(sprintf('No class could be resolved for repository source `%s`.', $sourceIdentifier), 1519662233);
            }
            if (!array_key_exists($sourceIdentifier, $this->repositorySourceInstances)) {
                $source = $this->repositorySourceFactory->create($sourceIdentifier, $configuration);
                $this->repositorySourceInstances[$sourceIdentifier] = $source;
            } else {
                $source = $this->repositorySourceInstances[$sourceIdentifier];
            }
            $repositoryFromUrl = $source->getRepositoryByUrl($repositoryUrl);
            if ($repositoryFromUrl === null) {
                continue;
            }
            $this->repositoryEvaluationService->evaluateRepository($source, $repositoryFromUrl);
            $this->outputLine(sprintf('Updated repository `%s` from source `%s`.', $repositoryFromUrl->getFullName(), $sourceIdentifier));
            return;
        }
        $this->outputLine(sprintf('No matching repository source found for `%s`.', $repositoryUrl));
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