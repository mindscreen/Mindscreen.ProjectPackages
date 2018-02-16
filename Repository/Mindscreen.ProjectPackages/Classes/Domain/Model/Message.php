<?php

namespace Mindscreen\ProjectPackages\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 */
class Message
{
    const SEVERITY_DEBUG = 1;

    const SEVERITY_NOTICE = 2;

    const SEVERITY_WARNING = 3;

    const SEVERITY_ERROR = 4;

    /**
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $severity;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(inversedBy="messages", cascade={"all"})
     * @var Project
     */
    protected $project;

    public function __construct($message, $code = null, $severity = self::SEVERITY_WARNING, array $arguments = [], $title = null)
    {
        $this->setMessage($message);
        if ($code !== null) {
            $this->setCode($code);
        }
        $this->setSeverity($severity);
        $this->setArguments($arguments);
        if ($title !== null) {
            $this->setTitle($title);
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code || '';
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title || '';
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }

    /**
     * @param int $severity
     */
    public function setSeverity(int $severity): void
    {
        $this->severity = $severity;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
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
}
