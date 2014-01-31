<?php

/**
 * Contents parameters trait for Sections and Items.
 */
trait ContentParametersTrait
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
        return $this->attributes[$name];
    }

    public function __call($name, $parameters)
    {
        if (array_key_exists($name, (array)$this->attributes)) {
            return $this->attributes[$name];
        } elseif (array_key_exists($name, (array)$this->parameters)) {
            return $this->parameters[$name];
        } elseif (array_key_exists($name, static::getParameters())) {
            return static::getParameters()[$name];
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, (array)$this->attributes)) {
            $this->attributes[$name] = $value;
        } elseif (array_key_exists($name, (array)$this->parameters)) {
            $this->parameters[$name] = $value;
        } elseif (array_key_exists($name, static::getParameters())) {
            $this->parameters[$name] = $value;
        }
    }
}
