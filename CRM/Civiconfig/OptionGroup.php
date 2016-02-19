<?php
/**
 * Class for OptionGroup configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_OptionGroup {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_OptionGroup constructor.
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
      throw new Exception('Missing mandatory param name in class CRM_Civiconfig_OptionGroup');
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update option group
   *
   * @param $params
   * @return array
   * @throws Exception when error in API Option Group Create
   */
  public function create($params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    $this->_apiParams['is_active'] = 1;
    $this->_apiParams['is_reserved'] = 1;
    if (!isset($this->_apiParams['title'])) {
      $this->_apiParams['title'] = ucfirst($this->_apiParams['name']);
    }
    try {
      $optionGroup = civicrm_api3('OptionGroup', 'Create', $this->_apiParams);
      if (isset($params['option_values'])) {
        $this->processOptionValues($optionGroup['id'], $params['option_values']);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create or update option_group with name'
        .$this->_apiParams['name'].', error from API OptionGroup Create: ' . $ex->getMessage());
    }
  }

  /**
   * Method to process option values for option group
   *
   * @param int $optionGroupId
   * @param array $optionValueParams
   */
  protected function processOptionValues($optionGroupId, $optionValueParams) {
    foreach ($optionValueParams as $optionValueName => $params) {
      $params['option_group_id'] = $optionGroupId;
      $optionValue = new CRM_Civiconfig_OptionValue();
      $optionValue->create($params);
    }
  }

  /**
   * Function to get the option group with name
   *
   * @param string $name
   * @return array|boolean
   */
  public function getWithName($name) {
    $params = array('name' => $name);
    try {
      return civicrm_api3('OptionGroup', 'Getsingle', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }
}