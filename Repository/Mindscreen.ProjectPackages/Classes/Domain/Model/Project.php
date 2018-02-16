<?php

namespace Mindscreen\ProjectPackages\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 */
class Project
{
    const PACKAGEMGR_COMPOSER = 'composer';
    const PACKAGEMGR_NPM = 'npm';
    const PACKAGEMGR_UNKNOWN = 'unknown';

    /**
     * @ORM\Column(length=32, name="`key`")
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(inversedBy="projects", cascade={"all"})
     * @var Repository
     */
    protected $repository;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(nullable=true)
     * @var array
     */
    protected $additional;

    /**
     * @ORM\OneToMany(mappedBy="project", cascade={"persist"})
     * @var Collection<Package>
     */
    protected $packages;

    /**
     * @var string
     */
    protected $packageManager;

    /**
     * @ORM\OneToMany(mappedBy="project", cascade={"persist"})
     * @var Collection<Message>
     */
    protected $messages;

    /**
     * @var \DateTime
     */
    protected $updated;

    public function __construct()
    {
        $this->setUpdated(new \DateTime());
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
     * @return int
     */
    public function getId(): int
    {
        return $this->repository->getId();
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->repository->getWebUrl();
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity(): \DateTime
    {
        return $this->repository->getUpdated();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description !== null ? $this->description : '';
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @param Package $package
     * @param bool $sameVersion
     * @return bool
     */
    public function packageExists(Package $package, $sameVersion = false): bool
    {
        $projectPackage = $this->findPackageByName($package->getName());
        if ($projectPackage === null) {
            return false;
        }
        return $sameVersion || $projectPackage->getVersion() == $package->getVersion();
    }

    // Can't be called `getPackageName` for ObjectAccess reasons...
    /**
     * @param string $packageName
     * @return Package|null
     */
    public function findPackageByName($packageName)
    {
        $packages = $this->getPackages();
        if (!is_array($packages)) {
            return null;
        }
        foreach ($packages as $package) {
            if ($package->getName() === $packageName) {
                return $package;
            }
        }
        return null;
    }

    /**
     * @return Package[]
     */
    public function getPackages(): array
    {
        return $this->packages !== null ? $this->packages->getValues() : [];
    }

    /**
     * @param Package $package
     */
    public function addPackage(Package $package): void
    {
        /*
        $projectPackage = $this->getPackageByName($package->getName());
        if ($projectPackage !== null) {
            TODO maybe check for something like lock version < package version
            if ($projectPackage->getVersion() != $package->getVersion()) {
                throw new \Exception(sprintf('Package "%s" already exists with a different version', $package->getName()), 1514968388);
            }
        }
        */
        if ($this->packages === null) {
            $this->packages = new ArrayCollection();
        }
        $package->setProject($this);
        $this->packages->set($package->getName(), $package);
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages !== null ? $this->messages->getValues() : [];
    }

    /**
     * @param Message $message
     */
    public function addMessage(Message $message): void
    {
        if ($this->messages === null) {
            $this->messages = new ArrayCollection();
        }
        $message->setProject($this);
        $this->messages->add($message);
    }

    public function toArray(): array
    {
        return [
            'repository' => [
                'id' => $this->getRepository()->getId(),
                'url' => $this->getRepository()->getWebUrl(),
                'source' => $this->getRepository()->getRepositorySource(),
                'namespace' => $this->getRepository()->getNamespace(),
                'name' => $this->getRepository()->getName(),
                'full_name' => $this->getRepository()->getFullName(),
            ],
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'packageManager' => $this->getPackageManager(),
            'type' => $this->getType(),
            'additional' => $this->getAdditional(),
            'description' => $this->getDescription(),
        ];
    }

    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * @param Repository $repository
     */
    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
        $this->key = $this->getKey();
    }

    public function getKey()
    {
        $parts = [
            $this->repository->getRepositorySource(),
            $this->repository->getId(),
            $this->repository->getNamespace(),
            $this->repository->getName(),
            $this->getPackageManager()
        ];
        return md5(implode('_', $parts));
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
        $this->key = $this->getKey();
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
    public function getType(): string
    {
        return $this->type !== null ? $this->type : '';
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getAdditional(): array
    {
        return is_array($this->additional) ? $this->additional : array();
    }

    /**
     * @param array $additional
     */
    public function setAdditional(array $additional)
    {
        $this->additional = $additional;
    }

}
