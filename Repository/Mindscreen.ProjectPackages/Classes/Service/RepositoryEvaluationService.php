<?php
namespace Mindscreen\ProjectPackages\Service;

use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Domain\Repository\RepositoryRepository;
use Mindscreen\ProjectPackages\Exception\MissingConfigurationException;
use Mindscreen\ProjectPackages\Strategy\Project\ProjectStrategyInterface;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Reflection\ReflectionService;

class RepositoryEvaluationService
{

    /**
     * @Flow\Inject()
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject
     * @var RepositoryRepository
     */
    protected $repositoryRepository;

    /**
     * @throws MissingConfigurationException
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function evaluateRepositorySources()
    {
        $clients = $this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'Mindscreen.ProjectPackages.clients');
        if ($clients === null) {
            throw new MissingConfigurationException('No repository sources defined as `Mindscreen.ProjectPackages.clients`.', 1518537759);
        }
        foreach ($clients as $identifier => $configuration) {
            $this->evaluateRepositorySource($identifier, $configuration);
        }
    }

    /**
     * @param $identifier
     * @param array|null $configuration
     * @throws MissingConfigurationException
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function evaluateRepositorySource($identifier, array $configuration = null)
    {
        if ($configuration === null) {
            $configuration = $this->configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'Mindscreen.ProjectPackages.clients.' . $identifier);
        }
        if ($configuration === null || !isset($configuration['type'])) {
            throw new MissingConfigurationException(
                sprintf('No repository-source strategy defined with identifier `%s`.', $identifier), 1518509852);
        }
        if (!isset($configuration['options']) || !is_array($configuration['options'])) {
            $options = [];
        } else {
            $options = $configuration['options'];
        }
        $repositorySourceClassName = $configuration['type'];
        if (!$this->reflectionService->isClassImplementationOf($repositorySourceClassName, RepositorySourceInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('The repository-source `%s` configures `%s` as strategy which does not implement the interface `%s`',
                    $identifier, $repositorySourceClassName, RepositorySourceInterface::class), 1518510283);
        }
        /** @var RepositorySourceInterface $repositorySource */
        $repositorySource = new $repositorySourceClassName($identifier, $options);
        foreach ($repositorySource->getAllRepositories() as $repositoryId) {
            $this->evaluateRepository($repositorySource, $repositoryId);
        }
    }

    /**
     * Evaluate strategies for a single repository of a given repository source
     * @param RepositorySourceInterface $repositorySource
     * @param string $repositoryId
     */
    public function evaluateRepositoryById(RepositorySourceInterface $repositorySource, $repositoryId) {
        $this->repositoryRepository->removeBySourceAndId($repositorySource->getIdentifier(), $repositoryId);
        $repository = $repositorySource->getRepository($repositoryId);
        $this->evaluateRepository($repositorySource, $repository);
    }

    protected function evaluateRepository(RepositorySourceInterface $repositorySource, Repository $repository)
    {
        $this->repositoryRepository->removeBySourceAndId($repositorySource->getIdentifier(), $repository->getId());
        $strategyClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface(ProjectStrategyInterface::class);
        $strategyClassNames = array_filter($strategyClassNames, function($strategy) { return $strategy::getPriority() >= 0; });
        usort($strategyClassNames, function($a, $b) { return $a::getPriority() < $b::getPriority();});
        $evaluatedStrategies = [];
        foreach ($strategyClassNames as $strategyClassName) {
            $strategySubClasses = $this->reflectionService->getAllSubClassNamesForClass($strategyClassName);
            $subclassEvaluated = count(array_intersect($evaluatedStrategies, $strategySubClasses)) > 0;
            if ((empty($evaluatedStrategies) || !$subclassEvaluated)
                && $strategyClassName::isApplicable($repositorySource, $repository)) {
                /** @var ProjectStrategyInterface $strategy */
                $strategy = new $strategyClassName();
                $strategy->setRepositorySource($repositorySource);
                $strategy->setRepository($repository);
                $strategy->evaluate();
                $evaluatedStrategies[] = $strategyClassName;
            }
        }
    }
}