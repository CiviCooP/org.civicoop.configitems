<?php
/**
 * Class for RelationshipType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_RelationshipType extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_RelationshipType constructor.
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
    if (!isset($params['name_a_b']) || empty($params['name_a_b']) ||
      !isset($params['name_b_a']) || empty($params['name_b_a'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name_a_b' and/or 'name_b_a' in class " . get_class() . ".");
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update a relationship type
   *
   * @param array $params
   * @return mixed
   * @throws Exception when error from API RelationshipType Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithNameAb($this->_apiParams['name_a_b']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    if (!isset($this->_apiParams['label_a_b']) || empty($this->_apiParams['label_a_b'])) {
      $this->_apiParams['label_a_b'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name_a_b']);
    }
    if (!isset($this->_apiParams['label_b_a']) || empty($this->_apiParams['label_b_a'])) {
      $this->_apiParams['label_b_a'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name_b_a']);
    }
    try {
      civicrm_api3('RelationshipType', 'Create', $this->_apiParams);
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update relationship type with name '.$this->_apiParams['name_a_b']
        .'. Error from API RelationshipType.Create: '.$ex->getMessage() . '.');
    }
  }

  /**
   * Function to get the relationship type with a name
   *
   * @param string $nameAb
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithNameAb($nameAb) {
    try {
      return civicrm_api3('RelationshipType', 'Getsingle',
        array('name_a_b' => $nameAb));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}