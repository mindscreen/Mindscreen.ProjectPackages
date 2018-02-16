<?php

namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;

interface ProjectStrategyInterface
{
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
    public static function isApplicable(RepositorySourceInterface $repositorySource, Repository $repository);

    /**
     * Higher priorities are sorted as first, priorities with negative values will be ignored.
     *
     * @return int
     */
    public static function getPriority();

    /**
     * @return void
     */
    public function evaluate();

    /**
     * Checks if any errors occurred when evaluating the strategy on it's given repository.
     *
     * @return boolean
     */
    public function hasErrors();

    /**
     * @return Message[]
     */
    public function getErrors();

    /**
     * @param RepositorySourceInterface $repositorySource
     * @return void
     */
    public function setRepositorySource(RepositorySourceInterface $repositorySource);

    /**
     * @param Repository $repository
     * @return void
     */
    public function setRepository(Repository $repository);
}