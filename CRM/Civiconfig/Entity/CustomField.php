<?php
/**
 * Class for CustomField configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_CustomField extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_CustomField constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }

  /**
   * Method to validate params for create
   *
   * @param array $params
   * @throws Exception when missing mandatory params
   */
  private function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name']) || !isset($params['custom_group_id'])
      || empty($params['custom_group_id'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameters 'name' and/or 'custom_group_id' in class " . get_class() . ".");
    }
    $this->_apiParams = $params;
    if (isset($this->_apiParams['option_group'])) {
      $this->_apiParams['option_type'] = 0;
      $optionGroup = new CRM_Civiconfig_Entity_OptionGroup();
      $found = $optionGroup->getWithName($this->_apiParams['option_group']);
      if (!empty($found)) {
        $this->_apiParams['option_group_id'] = $found['id'];
      } else {
        $created = $optionGroup->create(array('name' => $this->_apiParams['option_group']));
        $this->_apiParams['option_group_id'] = $created['id'];
      }
      unset($this->_apiParams['option_group']);
    }
  }

  /**
   * Method to create or update custom field
   *
   * @param array $params
   * @throws Exception when error from API CustomField Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithNameCustomGroupId($this->_apiParams['name'], $this->_apiParams['custom_group_id']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    if (!isset($this->_apiParams['label']) || empty($this->_apiParams['label'])) {
      $this->_apiParams['label'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name']);
    }
    try {
      $customField = civicrm_api3('CustomField', 'Create', $this->_apiParams);
      if (isset($params['option_group'])) {
        $this->fixOptionGroups($customField['values'], $params['option_group']);
      }
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update custom field with name '.$this->_apiParams['name']
        .' in custom group '.$this->_apiParams['custom_group_id'].'. Error from API CustomField.Create: '.$ex->getMessage() . '.');
    }
  }

  /**
   * Method to get custom field with name and custom group id
   *
   * @param string $name
   * @param integer $customGroupId
   * @return array|bool
   */
  public function getWithNameCustomGroupId($name, $customGroupId) {
    try {
      return civicrm_api3('CustomField', 'Getsingle', array('name' => $name, 'custom_group_id' => $customGroupId));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to fix option group in custom field because API always creates an option group whatever you do
   * so change option group to the one we created and then remove the one api created
   *
   * @param array $customField
   * @param string $optionGroupName
   * @throws CiviCRM_API3_Exception
   */
  protected function fixOptionGroups($customField, $optionGroupName) {
    $optionGroup = new CRM_Civiconfig_Entity_OptionGroup();
    $found = $optionGroup->getWithName($optionGroupName);
    // only if found is not equal to created custom field value
    if ($found['id'] != $customField[key($customField)]['option_group_id']) {
      $qry = 'UPDATE civicrm_custom_field SET option_group_id = %1 WHERE id = %2';
      $params = array(
        1 => array($found['id'], 'Integer'),
        2 => array(key($customField), 'Integer')
      );
      CRM_Core_DAO::executeQuery($qry, $params);
      civicrm_api3('OptionGroup', 'Delete', array('id' => $customField[key($customField)]['option_group_id']));
    }
  }

  /**
   * Method to remove custom fields that are not in the config custom group data
   *
   * @param int $customGroupId
   * @param array $configCustomGroupData
   * @return boolean
   * @access public
   * @static
   */
  public static function removeUnwantedCustomFields($customGroupId, $configCustomGroupData) {
    if (empty($customGroupId)) {
      return FALSE;
    }
    // first get all existing custom fields from the custom group
    try {
      $existingCustomFields = civicrm_api3('CustomField', 'Get', array('custom_group_id' => $customGroupId));
      foreach ($existingCustomFields['values'] as $existingId => $existingField) {
        // If existing field not in config custom data, delete custom field
        // Fix KL: check field['name'] if the custom groups array doesn't use names as keys
        if (
          !isset($configCustomGroupData['fields'][$existingField['name']]) &&
          !in_array($existingField['name'], array_column($configCustomGroupData['fields'], 'name'))
        ) {
          civicrm_api3('CustomField', 'Delete', array('id' => $existingId));
        }
      }
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    return TRUE;
  }
}