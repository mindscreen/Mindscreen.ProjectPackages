<?php

namespace Mindscreen\ProjectPackages\Service;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Package;
use Mindscreen\ProjectPackages\Domain\Model\Project;
use Mindscreen\ProjectPackages\Exception\PackageNotFoundException;

class ComposerService
{
    /**
     * @param \stdClass $composerData
     * @param \stdClass $lockData
     * @param Project $project
     * @return Project
     */
    public function evaluateComposerLock($composerData, $lockData, Project $project)
    {
        $rootDependencies = [];
        foreach (['require', 'require-dev'] as $dependencyType) {
            if (isset($composerData->$dependencyType)) {
                $rootDependencies = array_merge($rootDependencies, (array)$composerData->$dependencyType);
            }
        }
        try {
            $packages = $this->buildDependencyTree($lockData, array_keys($rootDependencies));
            foreach ($packages as $package) {
                $project->addPackage($package);
            }
        } catch (PackageNotFoundException $e) {
            $project->addMessage(new Message($e->getMessage(), $e->getCode(), Message::SEVERITY_ERROR));
        }
        return $project;
    }

    /**
     * @param \stdClass $lockData           The result of json_decode of the lock file
     * @param string[] $rootPackageNames    The names of the packages required in the composer.json
     * @return Package[]
     * @throws PackageNotFoundException
     */
    protected function buildDependencyTree($lockData, $rootPackageNames)
    {
        $packageMap = [];
        foreach (['packages', 'packages-dev'] as $packageType) {
            if (isset($lockData->$packageType)) {
                foreach ($lockData->$packageType as $packageData) {
                    $package = new Package();
                    $package->setName($packageData->name);
                    $package->setVersion($packageData->version);
                    $package->setPackageManager('composer');
                    $additionalData = [
                        'type' => $packageData->type,
                        'devDependency' => $packageType == 'packages-dev',
                    ];
                    $this->handlePackageSource($packageData, $additionalData);
                    $this->handlePackageDist($packageData, $additionalData);
                    $package->setAdditional($additionalData);
                    $package->__dependencyData = $packageData;
                    $packageMap[strtolower($package->getName())] = $package;
                    if (isset($packageData->provide)) {
                        foreach ($packageData->provide as $providePackageName => $v) {
                            $packageMap[strtolower($providePackageName)] = $package;
                        }
                    }
                }
            }
        }
        foreach ($rootPackageNames as $rootPackageName) {
            if (!array_key_exists($rootPackageName, $packageMap)) {
                throw new PackageNotFoundException(sprintf('The dependency `%s` is missing from the composer.lock.', $rootPackageName), 1519317868);
            }
            $rootPackage = $packageMap[strtolower($rootPackageName)];
            $this->evaluateDependencyDepthHelper($rootPackage, $packageMap,0);
        }
        return array_values($packageMap);
    }

    /**
     * Assigns dependencies to packages and calculates their depth in the dependency-tree
     * @param Package $package
     * @param Package[string] $packageMap
     * @param int $depth
     */
    protected function evaluateDependencyDepthHelper(Package &$package, &$packageMap, $depth)
    {
        if ($package->getDepth() !== null && $package->getDepth() <= $depth) {
            return;
        }
        $isDevDependency = @$package->getAdditional()['devDependency'] === true;
        $package->setDepth($depth);
        // require-dev dependencies are most likely not installed
        $dependencyTypes = ['require', /*'require-dev'*/];
        if (isset($package->__dependencyData)) {
            foreach ($dependencyTypes as $dependencyType) {
                if (isset($package->__dependencyData->$dependencyType)) {
                    foreach ($package->__dependencyData->$dependencyType as $dependencyName =>$v) {
                        $dependencyName = strtolower($dependencyName);
                        if (strpos($dependencyName, '/') === false) {
                            // likely not a composer package but a system requirement
                            continue;
                        }
                        if (!array_key_exists($dependencyName, $packageMap)) {
                            // should not occur in a valid installation
                            continue;
                        }
                        /** @var Package $dependencyPackage */
                        $dependencyPackage = $packageMap[$dependencyName];
                        if (!$isDevDependency) {
                            // if a package is a dev dependency from a different dependency-path
                            // it isn't a dev dependency in this one.
                            $dependencyPackageAdditional = $dependencyPackage->getAdditional();
                            if ($dependencyPackageAdditional['devDependency']) {
                                $dependencyPackageAdditional['devDependency'] = false;
                                $dependencyPackage->setAdditional($dependencyPackageAdditional);
                            }
                        }
                        $package->addDependency($dependencyPackage);
                        $this->evaluateDependencyDepthHelper($dependencyPackage, $packageMap, $depth + 1);
                    }
                }
            }
            unset($package->__dependencyData);
        }
    }

    protected function handlePackageSource($packageData, &$additionalData)
    {
        if (isset($packageData->source)) {
            $sourceType = @$packageData->source->type;
            $sourceHost = null;
            if ($sourceType === 'git') {
                if (isset($packageData->source->url)) {
                    if (preg_match('/^.*?@(.*?):/', $packageData->source->url, $matches) === 1) {
                        $sourceHost = $matches[1];
                    } elseif (preg_match('/https?:\/\/(.*?)\//', $packageData->source->url, $matches) === 1) {
                        $sourceHost = $matches[1];
                    }
                } else {
                    \Neos\Flow\var_dump([$packageData->source, $packageData->name]);
                }
            }
            $additionalData['source'] = [
                'type' => $sourceType,
                'url' => $packageData->source->url,
                'host' => $sourceHost
            ];
        }
    }

    protected function handlePackageDist($packageData, &$additionalData)
    {
        if (isset($packageData->dist)) {
            $additionalData['dist'] = $packageData->dist;
        }
    }

    /**
     * @param \stdClass $data
     * @param Project $project
     * @return Project
     */
    public function evaluateComposerJson($data, Project $project)
    {
        if (!isset($data->name)) {
            $message = new Message('No property "name" in composer.json', 1514982084);
            $project->addMessage($message);
        }
        else {
            if (!$project->getName()) {
                $project->setName($data->name);
            }
        }
        if (isset($data->type)) {
            $project->setType($data->type);
        }
        if (isset($data->description)) {
            $project->setDescription($data->description);
        }
        $project->setPackageManager(Project::PACKAGEMGR_COMPOSER);
        if (!isset($data->require)) {
            return $project;
        }
        foreach ($data->require as $packageName => $packageVersion) {
            $package = new Package();
            $package->setName($packageName);
            $package->setVersion($packageVersion);
            $package->setPackageManager('composer');
            try {
                $project->addPackage($package);
            } catch (\Exception $e) {
                $message = new Message(
                    sprintf('Duplicate entry for package "%s" in composer.json', $package->getName()), $e->getCode(), Message::SEVERITY_ERROR);
                $project->addMessage($message);
            }
        }
        return $project;
    }
}
