<?php

namespace Mindscreen\ProjectPackages\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(nullable=true)
     * @var int
     */
    protected $depth = null;

    /**
     * @ORM\ManyToMany(orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinTable(name="mindscreen_projectpackages_domain_model_package_join",
     *     joinColumns={@ORM\JoinColumn(name="package", referencedColumnName="persistence_object_identifier", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="dependency", referencedColumnName="persistence_object_identifier", onDelete="CASCADE")},
     * )
     * @var Collection<Package>
     */
    protected $dependencies;

    /**
     * @return int|null
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth): void
    {
        $this->depth = $depth;
    }

    /**
     * @return Collection
     */
    public function getDependencies(): Collection
    {
        if ($this->dependencies === null) {
            $this->dependencies = new ArrayCollection();
        }
        return $this->dependencies;
    }

    /**
     * @param Collection $dependencies
     */
    public function setDependencies(Collection $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @param Package $package
     */
    public function addDependency(Package $package): void
    {
        if ($this->dependencies === null) {
            $this->dependencies = new ArrayCollection();
        }
        $this->dependencies->add($package);
    }

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
     * @param null $depth
     * @return array
     */
    public function toArray($depth = null)
    {
        return [
            'name' => $this->getName(),
            'packageManager' => $this->getPackageManager(),
            'version' => $this->getVersion(),
            'additional' => $this->getAdditional(),
            'depth' => $this->getDepth(),
            'hasDependencies' => $this->getDependencies()->count() > 0,
            'dependencies' => $depth < 3 ? array_map(
                function(Package $d) use ($depth) { return $d->toArray($depth === null ? 1 : $depth + 1); },
                $this->getDependencies()->toArray()) : [],
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