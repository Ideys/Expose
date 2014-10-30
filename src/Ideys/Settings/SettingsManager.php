<?php

namespace Ideys\Settings;

use Silex\Application;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

/**
 * App settings manager.
 */
class SettingsManager
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Parameters that need to be serialized/un-serialized.
     *
     * @var array
     */
    private $arrayParameters = array(
        'languages',
    );

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
     * Return settings hydrated from database.
     * Behave as a singleton.
     *
     * @return Settings
     */
    public function getSettings()
    {
        if (! $this->settings instanceof Settings) {
            $this->settings = $this->findSettings();
        }

        return $this->settings;
    }

    /**
     * Retrieve settings parameters from database.
     *
     * @return Settings
     */
    private function findSettings()
    {
        $parameterRows = $this->db->fetchAll('SELECT * FROM '.'expose_settings');

        // Flatten extracted data
        $parameters = array();
        foreach ($parameterRows as $row) {
            if (in_array($row['attribute'], $this->arrayParameters)) {
                $row['value'] = unserialize($row['value']);
            }
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

        $previousSettings = $this->findSettings();

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

                    // Handle array parameters
                    if (in_array($propertyName, $this->arrayParameters)) {
                        $settingsParameter = serialize($settingsParameter);
                    }

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

    /**
     * Guess client language, relies on browser data.
     * Limit to settings allowed languages.
     *
     * @param Request $request
     *
     * @return string The selected language code
     */
    function clientLanguageSelector(Request $request) {
        $acceptLanguage = $request->headers->get('accept-language');
        $userLanguage   = strtolower(substr($acceptLanguage, 0, 2));
        $language       = (in_array($userLanguage, $this->getSettings()->getLanguages()))
            ? $userLanguage : Settings::LOCALE_FALLBACK;

        return $language;
    }
}
