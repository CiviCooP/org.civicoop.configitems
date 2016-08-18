<?php
/**
 * Class for OptionValue configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_OptionValue extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_OptionValue constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }
  /**
   * Method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  protected function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    if (!isset($params['option_group_id']) || empty($params['option_group_id'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'option_group_id' in class " . get_class() . ".");
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update option value
   *
   * @param $params
   * @return array
   * @throws Exception when error in API Option Value Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithNameAndOptionGroupId($this->_apiParams['name'], $this->_apiParams['option_group_id']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    if (!isset($this->_apiParams['is_active'])) {
        // If is_active is not explicitely given, assume that the option value
        // should be active.
        $this->_apiParams['is_active'] = 1;
    }
    $this->_apiParams['is_reserved'] = 1;
    if (!isset($this->_apiParams['label'])) {
      $this->_apiParams['label'] = ucfirst($this->_apiParams['name']);
    }
    try {
      return civicrm_api3('OptionValue', 'Create', $this->_apiParams);
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update option_value with name'.$this->_apiParams['name']
        .' in option group with id '.$this->_apiParams['option_group_id'].'. Error from API OptionValue.Create: '
        .$ex->getMessage() . '.');
    }
  }

  /**
   * Method to get the option group with name
   *
   * @param string $name
   * @param int $optionGroupId
   * @return array|boolean
   */
  public function getWithNameAndOptionGroupId($name, $optionGroupId) {
    $params = array('name' => $name, 'option_group_id' => $optionGroupId);
    try {
      return civicrm_api3('OptionValue', 'Getsingle', $params);
    } catch (\CiviCRM_API3_Exception $ex) {
      return array();
    }
  }
}