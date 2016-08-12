<?php
/**
 * This class contains this extension's main function, to update the configuration based on files provided.
 * Call the updateConfiguration and pass a custom ParamsProvider for custom loading implementations.
 * The API method calls the updateConfigurationFromJson function in this class.
 *
 * @hashtag #extensiongraffitiwall
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @author Kevin Levie <kevin.levie@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Loader {

  /**
   * Update configuration using the JSON files in the $resourcePath directory.
   *
   * @param string $resourcePath Resource path
   * @return bool Success
   * @throws \CRM_Civiconfig_Exception Thrown if resource path isn't valid
   */
  public function updateConfigurationFromJson($resourcePath) {

    $paramsProvider = new \CRM_Civiconfig_ParamsProvider_ResourcesDir($resourcePath);
    return $this->updateConfiguration($paramsProvider);
  }

  /**
   * Update configuration by walking supported entity types and trying to fetch data from the passed ParamsProvider.
   *
   * @param \CRM_Civiconfig_ParamsProvider $paramsProvider ParamsProvider implementation
   * @return array Array with loader status per entity type (SUCCESS or ERROR + caught EntityException message)
   */
  public function updateConfiguration(\CRM_Civiconfig_ParamsProvider $paramsProvider) {

    $ret = [];

    // Get supported entity types
    $config = \CRM_Civiconfig_Config::singleton();
    $supportedEntityTypes = $config->getSupportedEntityTypes();

    // Walk all types and try to fetch and process data for each one
    foreach ($supportedEntityTypes as $entityType) {

      try {
        // Check if we have a class to support this type
        $configClass = "\\CRM_Civiconfig_Entity_$entityType";
        if (!class_exists($configClass)) {
          throw new \CRM_Civiconfig_EntityException("Class '{$configClass}' does not exist!");
        }

        // Fetch data from ParamsProvider
        $params = $paramsProvider->getParamsArray($entityType);

        // Instantiate class and try to process the data provided
        /** @var \CRM_Civiconfig_Entity $entityTypeConfig */
        $entityTypeConfig = new $configClass();
        $entityTypeConfig->createAll($params);

        $ret[$entityType] = "SUCCESS";

      } catch (\CRM_Civiconfig_EntityException $e) {
        $ret[$entityType] = "ERROR: " . $e->getMessage();
      }

    }

    return $ret;
  }

}