<?php

namespace Mindscreen\ProjectPackages\Service;

use Mindscreen\ProjectPackages\Exception\FileNotFoundException;
use Mindscreen\ProjectPackages\Exception\MissingConfigurationException;
use Mindscreen\ProjectPackages\Exception\PermissionDeniedException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Response;
use Neos\Flow\Http\Uri;
use Neos\Utility\ObjectAccess;

class GithubApi
{

    protected $baseUri = 'https://api.github.com';

    /**
     * @Flow\Inject
     * @var Browser
     */
    protected $browser;

    /**
     * @Flow\Inject
     * @var CurlEngine
     */
    protected $engine;

    /**
     * @var array
     */
    protected $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function search($args)
    {
        $queryString = implode('+',
            array_map(function($k, $v) { return $k . ':' . $v; }, array_keys($args), $args));
        $data = $this->getAll('/search/code', ['q' => $queryString]);
        return $data;
    }

    protected function initializeObject()
    {
        $this->browser->setRequestEngine($this->engine);
        if (ObjectAccess::getPropertyPath($this->configuration, 'authorization.type') !== null) {
            $this->addAuthorizationHeader(ObjectAccess::getPropertyPath($this->configuration, 'authorization.type'));
        }
    }

    protected function addAuthorizationHeader($type, $value = null)
    {
        $headerValue = '';
        switch ($type) {
            case 'value':
                if ($value !== null) {
                    $headerValue = $value;
                } elseif (ObjectAccess::getPropertyPath($this->configuration, 'authorization.header') !== null) {
                    $headerValue = ObjectAccess::getPropertyPath($this->configuration, 'authorization.header');
                } else {
                    throw new MissingConfigurationException('Directly setting a Authorization header requires the configuration value `authorization.header`.', 1519124294);
                }
                break;
            case 'basic':
                $user = ObjectAccess::getPropertyPath($this->configuration, 'authorization.user');
                $password = ObjectAccess::getPropertyPath($this->configuration, 'authorization.password');
                $token = ObjectAccess::getPropertyPath($this->configuration, 'authorization.token');
                if ($token !== null) {
                    $headerValue = 'basic ' . base64_encode($token . ':x-oauth-basic');
                }
                elseif ($user !== null) {
                    if ($token !== null) {
                        $headerValue = 'basic ' . base64_encode($user . ':' . $token);
                    }
                    elseif ($password !== null) {
                        $headerValue = 'basic ' . base64_encode($user . ':' . $password);
                    }
                }
                else {
                    throw new MissingConfigurationException('Authorization method `basic` requires the option `authorization.token` and/or `authorization.user`.', 1519129731);
                }
                break;
            case 'token':
                $token = ObjectAccess::getPropertyPath($this->configuration, 'authorization.token');
                if ($token === null) {
                    throw new MissingConfigurationException('Authorization method `token` requires the option `authorization.token`.', 1519124760);
                }
                $headerValue = 'token ' . $token;
                break;
        }
        $this->browser->addAutomaticRequestHeader('Authorization', $headerValue);
    }

    /**
     * @internal
     * @param string $endpoint
     * @param array $arguments
     * @param string $method
     * @param string $data
     * @return \Neos\Flow\Http\Response|null
     */
    public function request($endpoint, array $arguments = array(), $method = 'GET', $data = null)
    {
        $url = $this->baseUri . $endpoint;
        if ($method === 'GET') {
            $url .= '?' . http_build_query($arguments);
        }
        $url = str_replace(['%2B'], ['+'], $url);
        $uri = new Uri($url);
        try {
            return $this->browser->request($uri, $method, $arguments, [], [], $data);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getAll($endpoint, array $arguments = array(), $method = 'GET', $data = null)
    {
        $result = [];
        $args = $arguments;
        do {
            $response = $this->request($endpoint, $args, $method, $data);
            $hasNextPage = $this->hasNextPage($response);
            if ($hasNextPage !== false) {
                $args['page'] = $hasNextPage;
            }
            if ($response !== null) {
                $data = $this->getResponseJson($response);
                if (isset($data['total_count'])) {
                    $data = $data['items'];
                }
                if ($data !== null) {
                    $result = array_merge($result, $data);
                }
            }
        } while ($response !== null && $hasNextPage !== false);
        return $result;
    }

    /**
     * @param Response $response
     * @return int|false
     */
    protected function hasNextPage(Response $response)
    {
        if (!$response->hasHeader('Link')) {
            return false;
        }
        $linkHeader = $response->getHeader('Link');
        $links = explode(',', $linkHeader);
        foreach ($links as $link) {
            if (preg_match('/\?page=(\d+)>;\s+rel="next"$/', $link, $matches) !== false) {
                return $matches[1];
            }
        }
        return false;
    }

    protected function getResponseJson(Response $response)
    {
        if ($response === null) {
            return null;
        }
        if ($response->getStatusCode() != 200) {
            return null;
        }
        return json_decode($response->getContent(), true);
    }

    public function getRepository($repositoryId)
    {
        $result = $this->request('/repositories/' . $repositoryId);
        return $this->getResponseJson($result);
    }

    /**
     * Returns the repository data on all repositories by a given user
     * @param string $username
     * @return array
     */
    public function getRepositoriesForUser($username)
    {
        $result = $this->getAll('/users/' . $username . '/repos');
        return $result;
    }

    /**
     * Returns the repository data on all repositories by a given user
     * @param string $organisation
     * @return array
     */
    public function getRepositoriesForOrganisation($organisation)
    {
        $result = $this->getAll('/orgs/' . $organisation . '/repos');
        return $result;
    }

    /**
     * Check whether the requested contentPath exists in the repository and is a file
     * @param string $repositoryId
     * @param string $contentPath
     * @param null $ref
     * @return bool
     */
    public function contentExists($repositoryId, $contentPath, $ref = null) {
        $args = [];
        if ($ref !== null) {
            $args['ref'] = $ref;
        }
        $response = $this->request('/repositories/' . $repositoryId . '/contents/' . $contentPath, $args);
        $data = $this->getResponseJson($response);
        return $data !== null && isset($data['type']) && $data['type'] === 'file';
    }

    /**
     * Return the raw file contents of a file
     * @param string $repositoryId
     * @param string $contentPath
     * @param string $ref
     * @return null|string
     * @throws PermissionDeniedException
     * @throws FileNotFoundException
     */
    public function getContent($repositoryId, $contentPath, $ref = null) {
        $args = [];
        if ($ref !== null) {
            $args['ref'] = $ref;
        }
        $this->browser->addAutomaticRequestHeader('Accept', 'application/vnd.github.VERSION.raw');
        $response = $this->request('/repositories/' . $repositoryId . '/contents/' . $contentPath, $args);
        $this->browser->removeAutomaticRequestHeader('Accept');
        if ($response->getStatusCode() == 403) {
            throw new PermissionDeniedException(sprintf('Could not access `%s`.', $contentPath), 1519126873);
        }
        if ($response->getStatusCode() != 200) {
            throw new FileNotFoundException(sprintf('Could not find `%s`', $contentPath), 1519126919);
        }
        return $response->getContent();
    }
}
