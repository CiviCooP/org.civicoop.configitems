<?php
/**
 * Params provider that uses the json files in the resources directory.
 *
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @date 6 Jun 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_ParamsProvider_ResourcesDir extends CRM_Civiconfig_ParamsProvider {
  protected $_resourcesPath = NULL;
  protected $_jsonFile = NULL;
  
  /**
   * Constructor.
   * 
   * @param string $entityType - CiviCRM entity type to create params for.
   * @param string $path - Location of the resources dir.
   * 
   * If $path is empty, the default location will be
   * (extensionsDir)/org.iida.civiconfig/resources/
   */
  function __construct($entityType, $path = NULL) {
    // TODO: Check whether $entity is actually a CiviCRM entity type.
    // TODO: Check whether $path exists.
    if ($path != NULL) {
      if (substr($path, -1) != '/') {
        // Add trailing slash if it's not there.
        $path .= '/';
      }
      $this->_resourcesPath = $path;
    }
    else {
      $settings = civicrm_api3('Setting', 'Getsingle', array());
      $this->_resourcesPath = $settings['extensionsDir'].'/org.iida.civiconfig/resources/';
    }

    // Convert camelcase to underscore separated, and add an 's'.
    // TODO: this will not work as expected with UFField, UFGroup, UFJoin, UFMatch:
    $fileName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $entityType)) . 's.json';
    $this->_jsonFile = $this->_resourcesPath . $fileName;
    if (!file_exists($this->_jsonFile)) {
      throw new Exception("Could not load $entityType configuration file for extension,
      contact your system administrator!");
    }    
  }

  /**
   * Returns params to create entities.
   */  
  public function getParamsArray() {
    $membershipTypesJson = file_get_contents($this->_jsonFile);
    return json_decode($membershipTypesJson, true);    
  }
}
