<?php
namespace Mindscreen\ProjectPackages\Strategy\RepositorySource;


use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Exception\FileNotFoundException;
use Mindscreen\ProjectPackages\Exception\MissingConfigurationException;
use Mindscreen\ProjectPackages\Exception\PermissionDeniedException;
use Mindscreen\ProjectPackages\Exception\RepositoryNotFoundException;

interface RepositorySourceInterface
{
    /**
     * Returns the identifier in the configuration
     * @return string
     */
    public function getIdentifier();

    /**
     * @return Repository[]
     * @throws MissingConfigurationException
     */
    public function getAllRepositories();

    /**
     * Returns whether a given repository exists in this source. Keep in mind that it might just be hidden for the
     * configured API user.
     * @param string $repositoryId
     * @return boolean
     */
    public function repositoryExists($repositoryId);

    /**
     * Tries to initialize a repository object from API data
     * @param $repositoryId
     * @return Repository
     * @throws RepositoryNotFoundException
     * @throws PermissionDeniedException
     */
    public function getRepository($repositoryId);

    /**
     * Return whether a requested file exists in the given repository
     * @param string $repositoryId
     * @param string $fileName
     * @param bool $caseSensitive
     * @param string $revision
     * @param string $path
     * @param bool $recursive
     * @return string[] file paths
     */
    public function findFile($repositoryId, $fileName, $caseSensitive = false, $revision = null, $path = '', $recursive = true);

    /**
     * Return whether a requested file exists in the given repository
     * @param string $repositoryId
     * @param string $fileName
     * @param string $revision
     * @return bool
     */
    public function fileExists($repositoryId, $fileName, $revision = null);

    /**
     * Return the contents of the requested file, if it exists. May throw an exception if the file doesn't exist.
     * @param string $repositoryId
     * @param string $fileName
     * @param null $revision
     * @return string
     * @throws FileNotFoundException
     * @throws PermissionDeniedException
     */
    public function getFileContents($repositoryId, $fileName, $revision = null);
}