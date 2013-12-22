<?php

use Doctrine\DBAL\Connection;

/**
 * App settings manager.
 */
class Settings
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @var array
     */
    private $defaultParameters = array(
                'name' => 'Ideys Expose',
                'description' => 'Smart gallery',
                'authorName' => 'Your Name',
                'analyticsKey' => '',
                'verificationKey' => '',
                'layoutBackground' => 'white',
                'customStyle' => '',
                'contactContent' => 'Contact me',
                'hideMenuOnHomepage' => '0',
            );

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param array $app
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
        $this->retrieveParameters();
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

    /**
     * Add a parameter.
     */
    private function createParameter($attribute, $value)
    {
        $this->db->insert('expose_settings', array(
            'attribute' => $attribute,
            'value' => $value,
        ));
        // Update available parameters
        $this->parameters[$attribute] = $value;
    }

    /**
     * Update a parameter.
     */
    private function updateParameter($attribute, $value)
    {
        $this->db->update('expose_settings', array(
            'value' => $value,
        ), array('attribute' => $attribute));
        // Update object parameter
        $this->parameters[$attribute] = $value;
    }

    /**
     * Update custom parameters.
     *
     * @param type $parameters
     */
    public function updateParameters($parameters)
    {
        foreach ($parameters as $attribute => $value) {
            if ($this->parameters[$attribute] != $value) {
                $this->updateParameter($attribute, $parameters[$attribute]);
            }
        }
    }

    /**
     * Retrieve custom parameters from database.
     */
    private function retrieveParameters()
    {
        $parameters = $this->db->fetchAll('SELECT * FROM expose_settings');

        foreach ($parameters as $parameter) {
            $this->parameters[$parameter['attribute']] = $parameter['value'];
        }

        $unpersistedParameters = array_diff_key($this->defaultParameters, $this->parameters);

        foreach ($unpersistedParameters as $attribute => $value) {
            $this->createParameter($attribute, $value);
        }
    }
}
