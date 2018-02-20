<?php

namespace Mindscreen\ProjectPackages\Factory;

use Mindscreen\ProjectPackages\Exception\MissingConfigurationException;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;

class RepositorySourceFactory
{
    /**
     * @Flow\InjectConfiguration("clients")
     * @var array
     */
    protected $repositorySourceConfiguration;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @param $identifier
     * @param array|null $configuration
     * @return RepositorySourceInterface
     * @throws MissingConfigurationException
     */
    public function create($identifier, array $configuration = null)
    {
        if (!array_key_exists($identifier, $this->repositorySourceConfiguration)) {
            throw new MissingConfigurationException(sprintf('No RepositorySource with identfier `%s` is configured', $identifier), 1519133296);
        }
        if ($configuration === null) {
            $configuration = $this->repositorySourceConfiguration[$identifier];
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
        $repositorySource = new $repositorySourceClassName($identifier, $options);
        return $repositorySource;
    }
}