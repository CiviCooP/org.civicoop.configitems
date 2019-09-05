<?php
/**
 * Class for identity tracker mapping configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_IdentityTrackerMapping extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_Group constructor.
   */
  public function __construct() {
    parent::__construct('IdentityTrackerMapping');
  }

  /**
   * Method to update the identity tracker settings.
   * Only available when identity tracker extension is installed.
   *
   * @param array $params Parameters
   * @return int id of created/updated entity
   * @throws Exception when error from API Entity Create
   */
  public function create(array $params) {
    $configuration = CRM_Identitytracker_Configuration::instance();
    $mapping = $configuration->getCustomFieldMapping();
    $mappingToMigrate = array();

    $field_id = civicrm_api3('CustomField', 'getvalue', array(
      'return' => 'id',
      'name' => $params['custom_field'],
      'custom_group_id' => $params['custom_group'],
    ));
    $mapping[$field_id] = $params['id_type'];
    $mappingToMigrate[$field_id] = $params['id_type'];
    $configuration->setCustomFieldMapping($mapping);
    foreach ($mappingToMigrate as $custom_field_id => $identity_type) {
      CRM_Identitytracker_Migration::migrateCustom($identity_type, $custom_field_id);
    }
    return;
  }

}
