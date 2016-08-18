<?php
/**
 * Class CRM_Civiconfig_Entity_CivicrmSetting.
 * This class adds support for updating CiviCRM system settings that are stored
 * in the civicrm_setting table using the Setting.Create API.
 *
 * @author Kevin Levie (CiviCooP) <kevin.levie@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_CivicrmSetting extends CRM_Civiconfig_Entity {

  protected $_apiParams = [];

  /**
   * CRM_Civiconfig_Entity_CivicrmSetting constructor.
   */
  public function __construct() {
    $this->_apiParams = [];
  }

  /**
   * Creates/updates all objects at once.
   * Overloading createAll here because we can add all settings in one API call.
   *
   * @param array $paramsArray
   * @return bool Success
   * @throws Exception if an API error occurs
   */
  public function createAll($paramsArray) {

    if (!is_array($paramsArray) || count($paramsArray) == 0) {
      return FALSE;
    }
    $this->_apiParams = $paramsArray;

    try {
      civicrm_api3('Setting', 'create', $this->_apiParams);
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update CiviCRM settings. Error from API Setting.Create: ' . $ex->getMessage() . '.');
    }

    return TRUE;
  }

  /**
   * Method to create or update a CiviCRM setting (redirects to createAll above).
   *
   * @param array $params Parameters
   * @return bool Success
   */
  public function create(array $params) {
    return $this->createAll([$params]);
  }

}