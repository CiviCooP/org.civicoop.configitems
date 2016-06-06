<?php
/**
 * Class for Tag configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Tag extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_Tag constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }

  /**
   * Method to validate params for create
   *
   * @param array $params
   * @throws Exception when mandatory param not found
   */
  private function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception('Missing mandatory param name when constructing class CRM_Civiconfig_Tag');
    }
    $this->_apiParams = $params;
    // if parent is set, retrieve parent number with name and set parents
    if (isset($this->_apiParams['parent'])) {
      $parentTag = $this->getWithName($this->_apiParams['parent']);
      if ($parentTag) {
        $this->_apiParams['parent_id'] = $parentTag['id'];
      }
      unset($this->_apiParams['parent']);
    }

  }
  /**
   * Method to create or update a tag
   *
   * @param array $params
   * @return mixed
   * @throws Exception when error from API Tag Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existingTag = $this->getWithName($this->_apiParams['name']);
    if (isset($existingTag['id'])) {
      $this->_apiParams['id'] = $existingTag['id'];
    }
    if (!isset($this->_apiParams['is_active'])) {
      $this->_apiParams['is_active'] = 1;
      if (empty($this->_apiParams['description']) || !isset($this->_apiParams['description'])) {
        $this->_apiParams['description'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name']);
      }
    }
    try {
      civicrm_api3('Tag', 'Create', $this->_apiParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create or update tag type with name'
        .$this->_apiParams['name'].', error from API Tag Create: ' . $ex->getMessage());
    }
  }

  /**
   * Function to get the tag with a name
   *
   * @param string $name
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithName($name) {
    try {
      return civicrm_api3('Tag', 'Getsingle', array('name' => $name));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}