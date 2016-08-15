<?php
/**
 *  Class CRM_Civiconfig_Entity_CaseType.
 *
 * @author Kevin Levie (CiviCooP) <kevin.levie@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_CaseType extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_Entity_CaseType constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }

  /**
   * Method to validate params passed to create
   *
   * @param $params
   * @throws Exception when required param not found
   */
  private function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    // The line below is in a strange place in the code. But I'll keep it
    // there, because it is there as well for every other entity type.
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update a case type
   *
   * @param array $params
   * @return mixed
   * @throws Exception if an API error occurs
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    try {
      civicrm_api3('FinancialType', 'Create', $this->_apiParams);
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update financial type with name '.$this->_apiParams['name'] . '. Error from API CaseType.Create: '.$ex->getMessage() . '.');
    }
  }

  /**
   * Function to get the case type by name
   *
   * @param string $name
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithName($name) {
    try {
      return civicrm_api3('CaseType', 'Getsingle',
        array('name' => $name));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}