<?php

namespace Ideys\Settings;

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
                'authorName' => 'Your Name',
                'maintenance' => '0',
                'analyticsKey' => '',
                'verificationKey' => '',
                'googleFonts' => '',
                'layoutBackground' => 'white',
                'customStyle' => '',
                'customJavascript' => '',
                'adminLink' => 'contact.section',
                'contactContent' => 'Contact me',
                'contactSection' => 'enabled',
                'contactSendToEmail' => '',
                'menuPosition' => 'top',
                'hideMenuOnHomepage' => '0',
                'shareFiles' => '0',
                'newSectiondefaultVisibility' => 'public',
            );

    /**
     * Constructor: inject database connexion.
     *
     * @param \Doctrine\DBAL\Connection $connection
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
     * Return Admin link position choices.
     *
     * @return array
     */
    public static function getAdminLinkChoices()
    {
        return array(
            'contact.section' => 'admin.link.on.contact.section',
            'menu' => 'admin.link.on.menu',
            'disabled' => 'admin.link.disabled',
        );
    }

    /**
     * Return contact displaying choices.
     *
     * @return array
     */
    public static function getContactSectionChoices()
    {
        return array(
            'enabled' => 'contact.enabled',
            'no.form' => 'contact.no.form',
            'disabled' => 'contact.disabled',
        );
    }

    /**
     * Return menu position choices.
     *
     * @return array
     */
    public static function getMenuPositionChoices()
    {
        return array(
            'top' => 'top',
            'left' => 'left',
        );
    }

    /**
     * Return yes / no choices for form selects.
     *
     * @return array
     */
    public static function getIOChoices()
    {
        return array(
            '1' => 'yes',
            '0' => 'no',
        );
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
