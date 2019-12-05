<?php
/**
 * Class for CustomField configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_CustomField extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_CustomField constructor.
   */
  public function __construct() {
    parent::__construct('CustomField');
  }

  /**
   * Method to validate params for create
   *
   * @param array $params
   * @throws Exception when missing mandatory params
   */
  public function validateCreateParams($params) {
    parent::validateCreateParams($params);
    if (empty($params['custom_group_id'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameters 'name' and/or 'custom_group_id' in class " . get_class() . ".");
    }
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    if (isset($params['option_group'])) {
      $params['option_type'] = 0;
      $optionGroup = new CRM_Civiconfig_Entity_OptionGroup();
      $found = $optionGroup->getExisting(['name' => $params['option_group']]);
      if (!empty($found)) {
        $params['option_group_id'] = $found['id'];
      } else {
        $id = $optionGroup->create(array('name' => $params['option_group']));
        $params['option_group_id'] = $id;
      }
      unset($params['option_group']);
    }
    if (empty($params['label'])) {
      $params['label'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }
    parent::prepareParams($params, $existing);
  }

  /**
   * Method to create or update custom field
   *
   * @param array $params
   * @return int $id ID of created custom field.
   *
   * @throws Exception when error from API CustomField Create
   */
  public function create(array $params) {
    $id = parent::create($params);
    if (isset($params['option_group'])) {
      $this->fixOptionGroups($id, $params['option_group']);
    }
    // Flush the cache
    CRM_Utils_System::flushCache();
    return $id;
  }

  /**
   * Method to get the existing custom field
   * If no existing entity is found, an empty array is returned.
   *
   * @param array $params
   * @return array
   */
  public function getExisting(array $params) {
    try {
      return civicrm_api3('CustomField', 'Getsingle', array('name' => $params['name'], 'custom_group_id' => $params['custom_group_id']));
    } catch (\CiviCRM_API3_Exception $ex) {
      return [];
    }
  }

  /**
   * Method to fix option group in custom field because API always creates an option group whatever you do
   * so change option group to the one we created and then remove the one api created
   *
   * @param int $id custom field ID
   * @param string $optionGroupName
   * @throws CiviCRM_API3_Exception
   */
  protected function fixOptionGroups($id, $optionGroupName) {
    $customField = civicrm_api3('CustomField', 'getsingle', ['id' => $id]);
    $optionGroupConfig = new CRM_Civiconfig_Entity_OptionGroup();
    $found = $optionGroupConfig->getExisting(['name' => $optionGroupName]);
    // only if found is not equal to created custom field value
    if ($found['id'] != $customField['option_group_id']) {
      $qry = 'UPDATE civicrm_custom_field SET option_group_id = %1 WHERE id = %2';
      $params = array(
        1 => array($found['id'], 'Integer'),
        2 => array($id, 'Integer')
      );
      CRM_Core_DAO::executeQuery($qry, $params);
      civicrm_api3('OptionGroup', 'Delete', array('id' => $customField['option_group_id']));
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
