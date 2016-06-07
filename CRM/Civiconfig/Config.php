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
    // TODO: If the extensions dir is '[civicrm.files]/ext/' (which is the default)
    // the construction below will not work.
    $settings = civicrm_api3('Setting', 'Getsingle', array());
    $this->resourcesPath = $settings['extensionsDir'].'/org.iida.civiconfig/resources/';
    
    foreach ($this->getConfigurableEntityTypes() as $entityType) {
      $configClass = "CRM_Civiconfig_Entity_$entityType";
      // TODO: Check whether class exists.
      $entityTypeConfig = new $configClass();
      // Create all entities using the json files in the resources directory.
      $entityTypeConfig->createAll(new CRM_Civiconfig_ParamsProvider_ResourcesDir($entityType));
    }
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
      'LocationType',
      'CustomGroup',
      // customGroep as last one because it might need one of the previous ones (option group, relationship types)
      // DO NOT INCLUDE CustomField, because custom fields are updated together
      // with custom groups.
    );
  }
}