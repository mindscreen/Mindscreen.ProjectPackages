<?php
namespace Mindscreen\ProjectPackages\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a repository in a repository source (usually a VCS host like gitlab/github/...)
 *
 * @Flow\Entity
 */
class Repository
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $webUrl;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $defaultBranch;

    /**
     * @var string
     */
    protected $repositorySource;

    /**
     * @ORM\OneToMany(mappedBy="repository", cascade={"persist"})
     * @var Collection<Project>
     */
    protected $projects;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
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
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getFullName(): string
    {
        $fullName = $this->getName();
        if (!empty($this->getNamespace())) {
            $fullName = $this->getNamespace() . '/' . $fullName;
        }
        return $fullName;
    }

    /**
     * @return string
     */
    public function getWebUrl(): string
    {
        return $this->webUrl;
    }

    /**
     * @param string $webUrl
     */
    public function setWebUrl(string $webUrl): void
    {
        $this->webUrl = $webUrl;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated): void
    {
        $this->updated = $updated;
    }

    /**
     * @return string
     */
    public function getDefaultBranch(): string
    {
        return $this->defaultBranch ? $this->defaultBranch : 'master';
    }

    /**
     * @param string $defaultBranch
     */
    public function setDefaultBranch(string $defaultBranch): void
    {
        $this->defaultBranch = $defaultBranch;
    }

    /**
     * @return string
     */
    public function getRepositorySource(): string
    {
        return $this->repositorySource;
    }

    /**
     * @param string $repositorySource
     */
    public function setRepositorySource(string $repositorySource): void
    {
        $this->repositorySource = $repositorySource;
    }

    /**
     * @return Collection
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    /**
     * @param Collection $projects
     */
    public function setProjects(Collection $projects): void
    {
        $this->projects = $projects;
    }

    /**
     * @param Project $project
     */
    public function addProject(Project $project) : void
    {
        if ($this->projects === null) {
            $this->projects = new ArrayCollection();
        }
        $this->projects->add($project);
    }
}