<?php

namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Package;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Exception\FileNotFoundException;
use Mindscreen\ProjectPackages\Exception\PermissionDeniedException;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;
use Mindscreen\YarnLock\ParserException;
use Mindscreen\YarnLock\YarnLock;

class NpmProject extends FallbackStrategy
{

    protected static $priority = 10;

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
    public static function isApplicable(RepositorySourceInterface $repositorySource, Repository $repository)
    {
        return $repositorySource->fileExists($repository->getId(), 'package.json', $repository->getDefaultBranch());
    }

    /**
     * @return void
     */
    public function evaluate()
    {
        $this->initializeProject();
        $projectLockExists = false;
        $yarnLockExists = false;
        if ($this->repositorySource->fileExists($this->repository->getId(), 'yarn.lock')) {
            $this->project->setPackageManager('yarn');
            $yarnLockExists = true;
        } else {
            $this->project->setPackageManager('npm');
            if (!$this->repositorySource->fileExists($this->repository->getId(), 'package-lock.json')) {
                $this->project->addMessage(new Message('No node-package version lock committed.'));
            } else {
                $projectLockExists = true;
            }
        }
        try {
            $packageJsonContent = $this->repositorySource->getFileContents(
                $this->repository->getId(),
                'package.json',
                $this->repository->getDefaultBranch());
            $packageJson = json_decode($packageJsonContent, true);
            if (array_key_exists('name', $packageJson)) {
                $this->project->setName($packageJson['name']);
            }
            if (array_key_exists('description', $packageJson)) {
                $this->project->setDescription($packageJson['description']);
            }
            $packageDependencies = [];
            if (array_key_exists('dependencies', $packageJson) && is_array($packageJson['dependencies'])) {
                $packageDependencies = $packageJson['dependencies'];
            }
            $packageDevDependencies = [];
            if (array_key_exists('devDependencies', $packageJson) && is_array($packageJson['devDependencies'])) {
                $packageDevDependencies = $packageJson['devDependencies'];
            }
            if ($projectLockExists) {
                $packageLockContent = $this->repositorySource->getFileContents(
                    $this->repository->getId(),
                    'package-lock.json',
                    $this->repository->getDefaultBranch());
                $packageLockData = json_decode($packageLockContent, true);
                $this->evaluatePackageLock($packageLockData);
            } elseif ($yarnLockExists) {
                $this->evaluateYarnLock(array_merge($packageDependencies, $packageDevDependencies));
            } else {
                $this->createPackages($packageDependencies, false);
                $this->createPackages($packageDevDependencies, true);
            }
        } catch (FileNotFoundException $e) {
        } catch (PermissionDeniedException $e) {
        }
        $this->persist();
    }

    protected function evaluatePackageLock(array $data)
    {
        foreach ($data['dependencies'] as $dependencyName => $dependencyData) {
            $package = $this->evaluateNpmDependencyObject($dependencyName, $dependencyData, 0);
            $this->project->addPackage($package);
        }
    }

    protected function evaluateNpmDependencyObject($name, array $data, $depth)
    {
        $package = new Package;
        $package->setName($name);
        $package->setDepth($depth);
        $package->setVersion($data['version']);
        $package->setProject($this->project);
        $package->setPackageManager('npm');
        $additionalData = [];
        if (array_key_exists('requires', $data)) {
            $additionalData['requires'] = $data['requires'];
        }
        $package->setAdditional($additionalData);
        if (array_key_exists('dependencies', $data)) {
            foreach ($data['dependencies'] as $dependencyName => $dependencyData) {
                $p = $this->evaluateNpmDependencyObject($dependencyName, $dependencyData, $depth + 1);
                $package->addDependency($p);
            }
        }
        return $package;
    }

    /**
     * @param array $packageDependencies
     * @throws \Mindscreen\ProjectPackages\Exception\FileNotFoundException
     * @throws \Mindscreen\ProjectPackages\Exception\PermissionDeniedException
     */
    protected function evaluateYarnLock(array $packageDependencies)
    {
        $yarnLockContent = $this->repositorySource->getFileContents(
            $this->repository->getId(),
            'yarn.lock',
            $this->repository->getDefaultBranch());
        try {
            $yarnLock = YarnLock::fromString($yarnLockContent);
            $rootPackages = [];
            foreach ($packageDependencies as $name => $version) {
                $p = $yarnLock->getPackage($name, $version);
                if ($p !== null) {
                    $rootPackages[] = $p;
                }
            }
            $yarnLock->calculateDepth($rootPackages);
            /** @var Package[string] $packageMap */
            $packageMap = [];
            $yarnPackages = $yarnLock->getPackages();
            foreach ($yarnPackages as $yarnPackage) {
                $package = new Package();
                $package->setName($yarnPackage->getName());
                $package->setVersion($yarnPackage->getVersion());
                $package->setPackageManager('yarn');
                $package->setDepth($yarnPackage->getDepth());
                $packageMap[$yarnPackage->__toString()] = $package;
                $this->project->addPackage($package);
            }
            foreach ($yarnPackages as $yarnPackage) {
                $package = $packageMap[$yarnPackage->__toString()];
                foreach ($yarnPackage->getAllDependencies() as $dependency) {
                    $dependencyPackage = $packageMap[$dependency->__toString()];
                    $package->addDependency($dependencyPackage);
                }
            }
        } catch (ParserException $e) {
            $this->project->addMessage(
                new Message(
                    'Could not parse yarn.lock',
                    null,
                    Message::SEVERITY_ERROR,
                    ['error' => $e->getMessage()])
            );
        }
    }

    protected function createPackages(array $dependencies, $devDependency)
    {
        foreach ($dependencies as $packageName => $packageVersion) {
            $package = new Package();
            $package->setName($packageName);
            $package->setVersion($packageVersion);
            $package->setPackageManager('npm');
            $package->setProject($this->project);
            $package->setAdditional(['devDependency' => $devDependency]);
            $this->project->addPackage($package);
        }
    }
}