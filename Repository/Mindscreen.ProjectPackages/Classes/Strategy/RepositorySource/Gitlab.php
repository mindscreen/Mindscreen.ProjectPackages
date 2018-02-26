<?php

namespace Mindscreen\ProjectPackages\Strategy\RepositorySource;


use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Gitlab\ResultPager;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Exception\FileNotFoundException;

class Gitlab extends AbstractRepositorySource
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @return array|Repository[]
     */
    public function getAllRepositories()
    {
        $pager = new ResultPager($this->getClient());
        $projects = $pager->fetchAll($this->getClient()->projects(), 'all');
        $result = [];
        foreach ($projects as $project) {
            $result[] = $this->createRepository($project);
        }
        return $result;
    }

    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = Client::create($this->options['url']);
            $this->client->authenticate($this->options['token']);
        }
        return $this->client;
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
        $repository->setNamespace($data['namespace']['name']);
        $repository->setWebUrl($data['web_url']);
        if (array_key_exists('default_branch', $data) && $data['default_branch'] !== null) {
            $repository->setDefaultBranch($data['default_branch']);
        }
        $repository->setCreated(new \DateTime($data['created_at']));
        $repository->setUpdated(new \DateTime($data['last_activity_at']));
        return $repository;
    }

    /**
     * @param string $repositoryId
     * @return bool
     */
    public function repositoryExists($repositoryId)
    {
        try {
            $this->getRepository($repositoryId);
            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * Tries to initialize a repository object from API data
     * @param $repositoryId
     * @return Repository
     */
    public function getRepository($repositoryId)
    {
        $repositoryInformation = $this->getClient()->projects()->show($repositoryId);
        return $this->createRepository($repositoryInformation);
    }

    /**
     * @param string $repositoryId
     * @param string $fileName
     * @param null $revision
     * @return bool
     */
    public function fileExists($repositoryId, $fileName, $revision = null)
    {
        try {
            $this->getFileContents($repositoryId, $fileName, $revision);
            return true;
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param string $repositoryId
     * @param string $fileName
     * @param null $revision
     * @return mixed|string
     * @throws FileNotFoundException
     */
    public function getFileContents($repositoryId, $fileName, $revision = null)
    {
        $ref = $revision === null ? 'master' : $revision;
        try {
            return $this->getClient()->repositoryFiles()->getRawFile($repositoryId, $fileName, $ref);
        } catch (RuntimeException $e) {
            throw new FileNotFoundException($e->getMessage(), 1519126673, $e);
        }
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
        $arguments = array();
        $arguments['recursive'] = $recursive;
        if ($revision) {
            $arguments['ref'] = $revision;
        }
        $client = $this->getClient();
        $pager = new ResultPager($client);
        $tree = $pager->fetchAll($client->repositories(), 'tree', [$repositoryId, $arguments]);
        $regex = '/' . $fileName . '/';
        if (!$caseSensitive) {
            $regex .= 'i';
        }
        $hits = array();
        foreach ($tree as $file) {
            if (preg_match($regex, $file['path'])) {
                $hits[] = $file['path'];
            }
        }
        return $hits;
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
        if (preg_match('/(git@|https?:\/\/)(.+?)(:|\/)([^\/]+)\/(.+?)(\.git|$)/', $repositoryUrl, $matches) === false) {
            return null;
        }
        $gitlabHost = $matches[2];
        if (strpos($this->options['url'], $gitlabHost) === false) {
            return null;
        }
        try {
            $data = $this->getClient()->projects()->show($matches[4] . '/' . $matches[5]);
            return $this->createRepository($data);
        } catch (\Exception $e) {
            return null;
        }
    }
}