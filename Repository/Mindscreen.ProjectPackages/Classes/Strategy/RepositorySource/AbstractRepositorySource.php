<?php
namespace Mindscreen\ProjectPackages\Strategy\RepositorySource;


abstract class AbstractRepositorySource implements RepositorySourceInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $options;

    public function __construct($identifier, array $options)
    {
        $this->options = $options;
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}