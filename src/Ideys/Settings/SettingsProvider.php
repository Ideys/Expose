<?php

namespace Ideys\Settings;

use Doctrine\DBAL\Connection;

/**
 * App settings provider.
 */
class SettingsProvider
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * Parameters that need a .htaccess file updating.
     *
     * @var array
     */
    private $htaccessParameters = array(
        'subDomain',
    );

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * Retrieve settings parameters from database.
     *
     * @return Settings
     */
    public function getSettings()
    {
        $parameterRows = $this->db->fetchAll('SELECT * FROM '.'expose_settings');

        // Flatten extracted data
        $parameters = array();
        foreach ($parameterRows as $row) {
            $parameters[$row['attribute']] = $row['value'];
        }

        // Hydrate Settings with related parameters
        $settings = new Settings();
        $reflection = new \ReflectionClass($settings);

        foreach ($reflection->getProperties() as $property) {

            $propertyName = $property->getName();

            if ($reflection->hasMethod('get' . ucfirst($propertyName))
                && array_key_exists($propertyName, $parameters)) {

                $settings->{'set' . ucfirst($propertyName)}($parameters[$propertyName]);
            }
        }

        return $settings;
    }

    /**
     * Update settings parameters.
     *
     * @param Settings $settings
     */
    public function persistSettings(Settings $settings)
    {
        $updatedParameters = array();

        $previousSettings = $this->getSettings();

        $reflection = new \ReflectionClass($settings);

        foreach ($reflection->getProperties() as $property) {

            $propertyName = $property->getName();
            $accessorName = 'get' . ucfirst($propertyName);

            // For all accessible Settings properties
            if ($reflection->hasMethod($accessorName)) {

                $settingsParameter = $settings->{$accessorName}();
                $previousSettingsParameter = $previousSettings->{$accessorName}();

                // Save parameters that have been changed
                if ($settingsParameter != $previousSettingsParameter) {

                    // Update if parameter is already persisted...
                    $isUpdated = $this->db->update('expose_settings', array(
                        'value' => $settingsParameter,
                    ), array('attribute' => $propertyName));

                    // ...create entry otherwise
                    if (! $isUpdated) {
                        $this->db->insert('expose_settings', array(
                            'attribute' => $propertyName,
                            'value' => $settingsParameter,
                        ));
                    }

                    $updatedParameters[] = $propertyName;
                }
            }
        }

        // Update .htaccess file if a related parameter have been changed
        $htaccessUpdatedParameters = array_intersect($updatedParameters, $this->htaccessParameters);
        if (count($htaccessUpdatedParameters) > 0) {
            $htaccessManager = new HtaccessManager();
            $htaccessManager->updateHtaccess($settings);
        }
    }
}
