<?php

namespace EzVpc\Tools;

class ClassLoader
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var int
     */
    protected $namespaceLength;

    /**
     * Init classLoader.
     *
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        $this->setNamespace($namespace);
    }

    /**
     * Determines if the given classname belongs to the given namespace.
     *
     * @param string $className
     * @return boolean
     */
    protected function isNestedClass(string $className)
    {
        if (!$this->namespaceLength) {
            return true;
        }

        if (substr($className, 0, $this->namespaceLength) === $this->namespace) {
            return true;
        }

        return false;
    }

    /**
     * Filter the given array of classes by namespace.
     *
     * @param array $classes
     * @return array
     */
    public function filterByNamespace(array $classes)
    {
        return array_filter($classes, array($this, 'isNestedClass'));
    }

    /**
     * Updates the namespace and triggers updateNamespaceLength().
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;

        $this->updateNamespaceLength();
    }

    /**
     * Updates the namespace length.
     *
     * @return void
     */
    public function updateNamespaceLength()
    {
        $this->namespaceLength = strlen($this->namespace);
    }
}
