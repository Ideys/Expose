<?php

/**
 * App settings manager.
 */
class Settings
{
    /**
     * @var Doctrine
     */
    private $orm;

    /**
     * @var array
     */
    private $parameters = array(
                'name' => 'Ideys Expose',
                'description' => 'Smart gallery',
                'authorName' => 'Your Name',
                'analyticsKey' => '',
                'verificationKey' => '',
                'layoutBackground' => 'white',
            );

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param array $app
     */
    public function __construct($app)
    {
        $this->orm = $app['db'];
    }

    /**
     * Return a parameter.
     *
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->parameters[$name];
    }

    /**
     * Return settings.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->parameters;
    }
}
