<?php
/**
 * Class following Singleton pattern for specific extension configuration
 * The actual configuration loader has moved to CRM_Civiconfig_Loader!
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 Jan 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Config {

  /**
   * @var self $_singleton
   */
  private static $_singleton;

  /**
   * Return class instance.
   * @return self
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new \CRM_Civiconfig_Config();
    }
    return self::$_singleton;
  }

  /**
   * Returns all entity types that can be configured with this extension.
   * The order of this array determines the order of configuration of the
   * entity types.
   *
   * @return array array of entity type names.
   */
  public function getSupportedEntityTypes() {
    // TODO: make this list configurable.
    return array(
      'CivicrmSetting',
      'ContactType',
      'MembershipType',
      'RelationshipType',
      'OptionGroup',
      'Group',
      'Tag',
      'FinancialAccount',
      'FinancialType',
      'EventType',
      'ActivityType',
      'LocationType',
      'CaseType',
      'CustomGroup',
      // CustomGroup as last one because it might need one of the previous ones (option group, relationship types)
      // DO NOT INCLUDE CustomField, because custom fields are updated together with custom groups.
    );
  }
}