<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 Jan 2016
 * @license AGPL-3.0
 * 
 * OOP-fixes Copyright Johan Vervloet (Chirojeugd-Vlaanderen vzw) 2016,
 * licensed under the terms of AGPL-3.0.
 */
class CRM_Civiconfig_Config {

  private static $_singleton;

  protected $_resourcesPath = null;

  /**
   * CRM_Civiconfig_Config constructor.
   */
  function __construct() {

    $settings = civicrm_api3('Setting', 'Getsingle', array());
    $this->resourcesPath = $settings['extensionsDir'].'/org.iida.civiconfig/resources/';
    
    foreach ($this->getConfigurableEntityTypes() as $entityType) {
      $configClass = "CRM_Civiconfig_$entityType";
      // TODO: Check whether class exists.
      $entityTypeConfig = new $configClass();
      // Create all entities using the json files in the resources directory.
      $entityTypeConfig->createAll(new CRM_Civiconfig_ResourcesDirParamsProvider($entityType));
    }
    
    // This is still here, because this is not really an entity.
    $this->setCustomData();
  }

  /**
   * Singleton method
   *
   * @return CRM_Civiconfig_Config
   * @access public
   * @static
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Civiconfig_Config();
    }
    return self::$_singleton;
  }
  
  /**
   * Returns all entity types that can be configured with this extension.
   * 
   * The order of this array determines the order of configuration of the
   * entity types.
   * 
   * @return array array of entity type names.
   */
  private function getConfigurableEntityTypes() {
    // TODO: make this list configurable.
    return array(
      'ContactType',
      'MembershipType',
      'RelationshipType',
      'OptionGroup',
      'Group',
      'EventType',
      'ActivityType',
      'Tag',
      // customData as last one because it might need one of the previous ones (option group, relationship types)
      // (I left custom data out for a moment, because it's a special case.)
    );
  }

  /**
   * Method to set the custom data groups and fields
   *
   * @throws Exception when config json could not be loaded
   * @access protected
   */
  protected function setCustomData() {
    $jsonFile = $this->resourcesPath.'custom_data.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load custom data configuration file for extension, contact your system administrator!');
    }
    $customDataJson = file_get_contents($jsonFile);
    $customData = json_decode($customDataJson, true);
    foreach ($customData as $customGroupName => $customGroupData) {
      $customGroup = new CRM_Civiconfig_CustomGroup();
      $created = $customGroup->create($customGroupData);
      foreach ($customGroupData['fields'] as $customFieldName => $customFieldData) {
        $customFieldData['custom_group_id'] = $created['id'];
        $customField = new CRM_Civiconfig_CustomField();
        $customField->create($customFieldData);
      }
      // remove custom fields that are still on install but no longer in config
      CRM_Civiconfig_CustomField::removeUnwantedCustomFields($created['id'], $customGroupData);
    }
  }
}