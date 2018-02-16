<?php

namespace Mindscreen\ProjectPackages\Service;


use Mindscreen\ProjectPackages\Domain\Model\Message;
use Mindscreen\ProjectPackages\Domain\Model\Package;
use Mindscreen\ProjectPackages\Domain\Model\Project;

class ComposerService
{
    /**
     * @param \stdClass $data
     * @param Project $project
     * @return Project
     */
    public function evaluateComposerLock($data, Project $project)
    {
        foreach ($data->packages as $packageData) {
            $package = new Package();
            $package->setName($packageData->name);
            $package->setVersion($packageData->version);
            $package->setPackageManager('composer');
            $additionalData = [
                'type' => $packageData->type
            ];
            $this->handlePackageSource($packageData, $additionalData);
            $this->handlePackageDist($packageData, $additionalData);
            $package->setAdditional($additionalData);
            try {
                $project->addPackage($package);
            } catch (\Exception $e) {
                $projectPackage = $project->findPackageByName($package->getName());
                $message = new Message(
                    sprintf('Version for package "%s" differs between lock and package version', $package->getName()),
                    $e->getCode(),
                    Message::SEVERITY_WARNING,
                    [$projectPackage->getVersion(), $package->getVersion()]
                    );
                $project->addMessage($message);
            }
        }
        return $project;
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
