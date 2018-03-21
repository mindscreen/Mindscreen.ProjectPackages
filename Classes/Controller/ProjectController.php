<?php

namespace Mindscreen\ProjectPackages\Controller;


use Mindscreen\ProjectPackages\Domain\Model\Package;
use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Domain\Repository\MessageRepository;
use Mindscreen\ProjectPackages\Domain\Repository\PackageRepository;
use Mindscreen\ProjectPackages\Domain\Repository\ProjectRepository;
use Mindscreen\ProjectPackages\Service\ProjectService;
use Mindscreen\ProjectPackages\Service\RepositoryEvaluationService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Mindscreen\ProjectPackages\Domain\Model\Repository;

class ProjectController extends ActionController
{
    /**
     * @var string
     */
    protected $viewFormatToObjectNameMap = array(
        'html' => \Neos\FluidAdaptor\View\TemplateView::class,
        'json' => \Neos\Flow\Mvc\View\JsonView::class
    );

    /**
     * @Flow\Inject
     * @var RepositoryEvaluationService
     */
    protected $repositoryEvaluationService;

    /**
     * @Flow\Inject
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @Flow\Inject
     * @var MessageRepository
     */
    protected $messageRepository;

    /**
     * @Flow\Inject
     * @var PackageRepository
     */
    protected $packageRepository;

    /**
     * @Flow\InjectConfiguration("allowCrossOriginRequests")
     * @var bool
     */
    protected $allowCrossOriginRequests;

    protected function initializeAction()
    {
        parent::initializeAction();
        if ($this->allowCrossOriginRequests) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
        }
    }

    public function listAction()
    {
        $queryResult = $this->projectRepository->findAll();
        $allProjects = $queryResult->toArray();
        $allProjects = array_map(function (Project $project) {
            return $project->toArray();
        }, $allProjects);
        $this->view->assign('value', $allProjects);
    }

    public function projectTypesAction()
    {
        $query = $this->projectRepository->createDqlQuery('
            SELECT DISTINCT p.type FROM ' . Project::class . ' p
        ');
        $result = $query->execute();
        $list = array_map(function ($x) {
            return $x['type'];
        }, $result);
        $this->view->assign('value', $list);
    }

    public function packageManagersAction()
    {
        $query = $this->projectRepository->createDqlQuery('
            SELECT DISTINCT p.packageManager FROM ' . Project::class . ' p
        ');
        $result = $query->execute();
        $list = array_map(function ($x) {
            return $x['packageManager'];
        }, $result);
        $this->view->assign('value', $list);
    }

    public function repositorySourcesAction()
    {
        $query = $this->projectRepository->createDqlQuery('
            SELECT DISTINCT p.repositorySource FROM ' . Repository::class . ' p
        ');
        $result = $query->execute();
        $list = array_map(function ($x) {
            return $x['repositorySource'];
        }, $result);
        $this->view->assign('value', $list);
    }

    /**
     * @param string $projectKey
     */
    public function packagesAction($projectKey)
    {
        $project = $this->projectRepository->findOneByKey($projectKey);
//        $packages = $this->packageRepository->findByProject($project)->toArray();
        $packages = $this->packageRepository->findByProjectAndDepth($project);
//        \Neos\Flow\var_dump($packages);
//        $packages = array_filter($packages, function(Package $package) { return $package->getDepth() === 0; });
        $packages = array_map(function (Package $package) {
            return $package->toArray();
        }, $packages);
        usort($packages, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        $this->view->assign('value', $packages);
    }

    /**
     * @param string $projectKey
     */
    public function messagesAction($projectKey)
    {
        $project = $this->projectRepository->findOneByKey($projectKey);
        $queryResult = $this->messageRepository->findByProject($project);
        $this->view->assign('value', $queryResult);
    }

    /**
     * @param string $projectKey
     */
    public function showAction($projectKey)
    {
        $queryResult = $this->projectRepository->findOneByKey($projectKey);
        if ($queryResult instanceof Project) {
            $value = $queryResult->toArray();
        } else {
            $value = null;
        }
        $this->view->assign('value', $value);
    }

}
