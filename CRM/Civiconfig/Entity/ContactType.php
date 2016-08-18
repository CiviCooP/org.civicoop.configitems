<?php
/**
 * Class for ContactType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_ContactType extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

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
    $this->_apiParams = $params;
  }

  /**
   * Method to create contact type
   *
   * @param array $params
   * @return mixed
   * @throws Exception when error from API ContactType Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    if (!isset($this->_apiParams['label']) || empty($this->_apiParams['label'])) {
      $this->_apiParams['label'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name']);
    }
    try {
      civicrm_api3('ContactType', 'Create', $this->_apiParams);
      $this->updateNavigationMenuUrl();
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update contact type with name '.$this->_apiParams['name']
        .'. Error from API ContactType.Create: '.$ex->getMessage().'.');
    }
  }

  /**
   * Method to check if there is a navigation menu option for the contact type
   * and if so, update name and url
   *
   * @access private
   */
  private function updateNavigationMenuUrl() {
    // check if there is a "New <label>" entry in the navigation table
    $query = "SELECT * FROM civicrm_navigation WHERE label = %1";
    $label = "New ".$this->_apiParams['label'];
    $dao = CRM_Core_DAO::executeQuery($query, array(1 => array($label, 'String')));
    $validParent = array("New Organization", "New Individual", "New Household");
    $newUrl = 'civicrm/contact/add&ct=Organization&cst='.$this->_apiParams['name'].'&reset=1';
    $newName = "New ".$this->_apiParams['name'];
    while ($dao->fetch()) {
      // parent should be either New Organization, New Individual or New Household
      if (isset($dao->parent_id)) {
        $parentQuery = "SELECT name FROM civicrm_navigation WHERE id = %1";
        $parentName = CRM_Core_DAO::singleValueQuery($parentQuery, array(1 => array($dao->parent_id, 'Integer')));
        if (in_array($parentName, $validParent)) {
          $update = "UPDATE civicrm_navigation SET url = %1, name = %2 WHERE id = %3";
          $params = array(
            1 => array($newUrl, 'String'),
            2 => array($newName, 'String'),
            3 => array($dao->id, 'Integer')
          );
          CRM_Core_DAO::executeQuery($update, $params);
        }
      }
    }
  }

  /**
   * Method to get contact sub type with name
   *
   * @param string $contactTypeName
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithName($contactTypeName) {
    try {
      return civicrm_api3('ContactType', 'Getsingle', array('name' => $contactTypeName));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

}