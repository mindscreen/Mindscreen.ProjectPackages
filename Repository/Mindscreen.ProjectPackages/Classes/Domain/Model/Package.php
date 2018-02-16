<?php

namespace Mindscreen\ProjectPackages\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A model containing usual package-management information like name and version.
 * Dependencies will most likely only be used in the project packages to associate
 * packages.
 *
 * @Flow\Entity
 */
class Package
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * @ORM\Column(nullable=true)
     * @var array
     */
    protected $additional;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(inversedBy="packages", cascade={"all"})
     * @var Project
     */
    protected $project;
    /**
     * @var string
     */
    protected $packageManager;

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'packageManager' => $this->getPackageManager(),
            'version' => $this->getVersion(),
            'additional' => $this->getAdditional()
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPackageManager()
    {
        return $this->packageManager;
    }

    /**
     * @param string $packageManager
     */
    public function setPackageManager(string $packageManager): void
    {
        $this->packageManager = $packageManager;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function getAdditional(): array
    {
        return is_array($this->additional) ? $this->additional : [];
    }

    /**
     * @param array $additional
     */
    public function setAdditional(array $additional): void
    {
        $this->additional = $additional;
    }

}