<?php

namespace Mindscreen\ProjectPackages\Controller;

use Mindscreen\ProjectPackages\Domain\Model\Package;
use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Domain\Repository\PackageRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class PackageController extends ActionController
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

    /**
     * @param bool $grouped Packages will be grouped by name, e.g. [[{'name': 'foo', 'version': 'v1'}, {'name': 'foo', 'version: 'v2'}]]
     */
    public function listAction($grouped = false)
    {
        $query = $this->packageRepository->createDqlQuery('
        SELECT p.name, p.version, p.packageManager, count(p) as usages FROM ' . Package::class . ' p
        JOIN p.project r
        GROUP BY p.name, p.version, p.packageManager
        ORDER BY p.name ASC, usages DESC
        ');
        /** @var array $queryResult */
        $queryResult = $query->execute();
        if ($grouped && count($queryResult) > 0) {
            $result = array();
            $currentName = $queryResult[0]['name'];
            $currentPackage = array();
            foreach ($queryResult as $packageResult) {
                if ($packageResult['name'] != $currentName) {
                    $result[] = $currentPackage;
                    $currentName = $packageResult['name'];
                    $currentPackage = array();
                }
                $currentPackage[] = $packageResult;
            }
            $queryResult = $result;
        }
        $this->view->assign('value', $queryResult);
    }

    public function packageManagersAction()
    {
        $query = $this->packageRepository->createDqlQuery('
            SELECT DISTINCT p.packageManager FROM ' . Package::class . ' p
        ');
        $result = $query->execute();
        $list = array_map(function ($x) {
            return $x['packageManager'];
        }, $result);
        $this->view->assign('value', $list);
    }


    /**
     * @param array $packages
     */
    public function projectsAction(array $packages)
    {
        $queriedPackages = array();
        $packageNames = array();
        $versionedPackageNames = array();
        foreach ($packages as $package) {
            $parts = explode(':', $package, 2);
            if (count($parts) === 1 || $parts[1] === 'ANY') {
                $packageNames[] = $parts[0];
            } else {
                $versionedPackageNames[] = $package;
            }
            $queriedPackages[$parts[0]] = true;
        }
        $query = $this->packageRepository->createDqlQuery('
        SELECT project FROM ' . Project::class . ' project
        JOIN project.packages package
        WHERE (package.name IN (:packageNames) OR CONCAT(package.name, \':\', package.version) IN (:versionedPackageNames))
        GROUP BY project
        HAVING count(project)=:packageCount
        ')
            ->setParameter('packageNames', $packageNames)
            ->setParameter('versionedPackageNames', $versionedPackageNames)
            ->setParameter('packageCount', count($queriedPackages));
        $result = $query->execute();
        $result = array_map(function (Project $project) {
            return $project->toArray();
        }, $result);
        $this->view->assign('value', $result);
    }
}
