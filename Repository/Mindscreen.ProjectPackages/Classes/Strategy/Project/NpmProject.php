<?php
namespace Mindscreen\ProjectPackages\Strategy\Project;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Package;
use Mindscreen\ProjectPackages\Domain\Model\Repository;
use Mindscreen\ProjectPackages\Strategy\RepositorySource\RepositorySourceInterface;

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
        if ($this->repositorySource->fileExists($this->repository->getId(), 'yarn.lock')) {
            $this->project->setPackageManager('yarn');
        } else {
            $this->project->setPackageManager('npm');
            if (!$this->repositorySource->fileExists($this->repository->getId(), 'package-lock.json')) {
                $this->project->addMessage(new Message('No node-package version lock committed.'));
            }
        }
        $packageJsonContent = $this->repositorySource->getFileContents($this->repository->getId(), 'package.json', $this->repository->getDefaultBranch());
        $packageJson = json_decode($packageJsonContent, true);
        if (array_key_exists("name", $packageJson)) {
            $this->project->setName($packageJson['name']);
        }
        if (array_key_exists("description", $packageJson)) {
            $this->project->setDescription($packageJson['description']);
        }
        if (array_key_exists("dependencies", $packageJson) && is_array($packageJson['dependencies'])) {
            $this->createPackages($packageJson['dependencies'], false);
        }
        if (array_key_exists("devDependencies", $packageJson) && is_array($packageJson['devDependencies'])) {
            $this->createPackages($packageJson['devDependencies'], true);
        }
        $this->persist();
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