<?php

namespace Mindscreen\ProjectPackages\Strategy\RepositorySource;


use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Exception\MissingConfigurationException;
use Mindscreen\ProjectPackages\Service\GithubApi;

class Github extends AbstractRepositorySource
{

    /**
     * @var GithubApi
     */
    protected $api;

    public function __construct($identifier, array $options)
    {
        parent::__construct($identifier, $options);
        $configuration = [];
        foreach (['authorization'] as $configValue) {
            if (isset($options[$configValue])) {
                $configuration[$configValue] = $options[$configValue];
            }
        }
        $this->api = new GithubApi($configuration);
    }

    /**
     * @return Repository[]
     * @throws MissingConfigurationException
     */
    public function getAllRepositories()
    {
        $repositories = [];
        if (isset($this->options['user'])) {
            $userRepositoryData = $this->api->getRepositoriesForUser($this->options['user']);
            $repositories = array_merge($repositories,
                array_map(function ($repositoryData) {
                    return $this->createRepository($repositoryData);
                }, $userRepositoryData));
        }
        elseif (isset($this->options['org'])) {
            $orgRepositoryData = $this->api->getRepositoriesForUser($this->options['org']);
            $repositories = array_merge($repositories,
                array_map(function ($repositoryData) {
                    return $this->createRepository($repositoryData);
                }, $orgRepositoryData));
        }
        else {
            throw new MissingConfigurationException('Neither `user` nor `org` is configured for the github repository source', 1519120185);
        }
        return $repositories;
    }

    /**
     * @param array $data Data as returned from the API Client
     * @return Repository
     */
    protected function createRepository(array $data)
    {
        $repository = new Repository();
        $repository->setRepositorySource($this->getIdentifier());
        $repository->setId($data['id']);
        $repository->setName($data['name']);
        $repository->setNamespace($data['owner']['login']);
        $repository->setWebUrl($data['html_url']);
        if (array_key_exists('default_branch', $data) && $data['default_branch'] !== null) {
            $repository->setDefaultBranch($data['default_branch']);
        }
        $repository->setCreated(new \DateTime($data['created_at']));
        $repository->setUpdated(new \DateTime($data['updated_at']));
        return $repository;
    }

    /**
     * Returns whether a given repository exists in this source. Keep in mind that it might just be hidden for the
     * configured API user.
     * @param string $repositoryId
     * @return boolean
     */
    public function repositoryExists($repositoryId)
    {
        return $this->api->getRepository($repositoryId) === null;
    }

    /**
     * Tries to initialize a repository object from API data
     * @param $repositoryId
     * @return Repository
     */
    public function getRepository($repositoryId)
    {
        $repositoryData = $this->api->getRepository($repositoryId);
        if ($repositoryData === null) {
            return null;
        }
        return $this->createRepository($repositoryData);
    }

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
    public function findFile($repositoryId, $fileName, $caseSensitive = false, $revision = null, $path = '', $recursive = true)
    {
        $repository = $this->getRepository($repositoryId);
        $args = [
            'repo' => $repository->getFullName(),
            'filename' => $fileName,
        ];
        if (!empty($path)) {
            $args['path'] = $path;
        }
        $results = $this->api->search($args);
        return array_map(function($f) { return $f['path']; }, $results);
    }

    /**
     * Return whether a requested file exists in the given repository
     * @param string $repositoryId
     * @param string $fileName
     * @param string $revision
     * @return bool
     */
    public function fileExists($repositoryId, $fileName, $revision = null)
    {
        return $this->api->contentExists($repositoryId, $fileName, $revision);
    }

    /**
     * Return the contents of the requested file, if it exists. May throw an exception if the file doesn't exist.
     * @param string $repositoryId
     * @param string $fileName
     * @param null $revision
     * @return string
     * @throws \Mindscreen\ProjectPackages\Exception\FileNotFoundException
     * @throws \Mindscreen\ProjectPackages\Exception\PermissionDeniedException
     */
    public function getFileContents($repositoryId, $fileName, $revision = null)
    {
        return $this->api->getContent($repositoryId, $fileName, $revision);
    }

    /**
     * Return a Repository if it is accessible with the given URL or null,
     * if not available.
     * @param string $repositoryUrl
     * @return Repository|null
     */
    public function getRepositoryByUrl($repositoryUrl)
    {
        $matches = [];
        if (preg_match('/github\.com(:|\/)([^\/]+)\/(.+?)(\.git|$)/', $repositoryUrl, $matches) === false) {
            return null;
        }
        $response = $this->api->request('/repos/' . $matches[2] . '/' . $matches[3]);
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);
            if ($data !== null) {
                return $this->createRepository($data);
            }
        }
        return null;
    }
}