<?php
/**
 * Params provider that uses JSON files in a resources directory.
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @date 6 Jun 2016
 * @license AGPL-3.0
 *
 * This class is now initialised once, and reused for getting the parameters for each entity type.
 * It's injected into CRM_Civiconfig_Config, to make it easier to implement alternative ParamsProviders.
 * The constructor now only checks if the directory exists: getParamsArray tries to find and parse the JSON file.
 * @author Kevin Levie <kevin.levie@civicoop.org>
 */
class CRM_Civiconfig_ParamsProvider_ResourcesDir extends CRM_Civiconfig_ParamsProvider {

  protected $_resourcesPath = NULL;
  
  /**
   * Constructor.
   *
   * @param string $path Resources directory
   * @throws \CRM_Civiconfig_Exception Thrown if the JSON resource directory doesn't exist
   */
  function __construct($path) {

    // Path now always required: check if it exists
    if(!file_exists($path) || !is_dir($path)) {
      throw new \CRM_Civiconfig_Exception("Civiconfig resource directory does not exist: {$path}.");
    }

    // Resolve relative paths, and always add a trailing slash
    $this->_resourcesPath = realpath($path) . '/';
  }

  /**
   * Returns params to create entities for $entityType.
   *
   * @param string $entityType CiviCRM entity type to create params for
   * @return array $params Parameters
   * @throws \CRM_Civiconfig_EntityException Thrown if json_decode returns false
   */  
  public function getParamsArray($entityType) {

    // Get file name: convert camelcase to underscore separated, and add an 's'.
    // TODO: this will not work as expected with UFField, UFGroup, UFJoin, UFMatch:
    $fileName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $entityType)) . 's.json';
    $jsonFile = $this->_resourcesPath . $fileName;

    // Check if file exists
    if (!is_readable($jsonFile)) {
      throw new \CRM_Civiconfig_EntityException("No readable JSON file for entity type '$entityType'.");
    }

    // Read file and try to parse JSON
    $jsonData = file_get_contents($jsonFile);
    $params = json_decode($jsonData, true);

    if($params === null || !is_array($params)) {
      throw new \CRM_Civiconfig_EntityException("Could not parse JSON file '{$jsonFile}'.");
    }

    return $params;
  }
}
