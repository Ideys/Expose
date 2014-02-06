<?php

namespace Ideys\Content;

/**
 * Contents parameters trait for Sections and Items.
 */
trait ContentTrait
{
    /**
     * Entity main attributes
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Custom content type parameters
     *
     * @var array
     */
    protected $parameters = array();


    /**
     * Magic method get, return attributes.
     *
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->getObjectTaxon($name);
    }

    public function __call($name, $parameters)
    {
        return $this->getObjectTaxon($name);
    }

    public function getObjectTaxon($name)
    {
        if (array_key_exists($name, (array)$this->attributes)) {
            return $this->attributes[$name];
        }
        return $this->getParameter($name);
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, (array)$this->attributes)) {
            $this->attributes[$name] = $value;
        } elseif (array_key_exists($name, static::getParameters())) {
            $this->setParameter($name, $value);
        }
    }

    public function setParameter($name, $value)
    {
        if (!is_array($this->parameters)) {
            $this->parameters = (array) unserialize($this->parameters);
        }
        $this->parameters[$name] = $value;
        $this->attributes['parameters'] = $this->parameters;
    }

    public function getParameter($name)
    {
        if (array_key_exists($name, (array)$this->parameters)) {
            return $this->parameters[$name];
        } elseif (array_key_exists($name, static::getParameters())) {
            return static::getParameters()[$name];
        } else {
            throw new \Exception(sprintf('Unable to find content parameter named "%s".', $name));
        }
    }
}
