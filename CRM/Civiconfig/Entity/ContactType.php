<?php
/**
 * Class for ContactType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_ContactType extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_ContactType constructor.
   */
  public function __construct() {
    parent::__construct('ContactType');
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    if (empty($params['label'])) {
      $params['label'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }
    parent::prepareParams($params, $existing);
  }

  /**
   * Method to create contact type.
   *
   * @param array $params
   * @return int $id ID of created contact type
   * @throws Exception when error from API ContactType Create
   */
  public function create(array $params) {
    $id = parent::create($params);
    $this->updateNavigationMenuUrl($params);
    return $id;
  }

  /**
   * Method to check if there is a navigation menu option for the contact type
   * and if so, update name and url
   *
   * @param array $params
   * @access private
   */
  private function updateNavigationMenuUrl($params) {
    // check if there is a "New <label>" entry in the navigation table
    $query = "SELECT * FROM civicrm_navigation WHERE label = %1";
    $label = "New ".$params['label'];
    $dao = CRM_Core_DAO::executeQuery($query, array(1 => array($label, 'String')));
    $validParent = array("New Organization", "New Individual", "New Household");
    $newUrl = 'civicrm/contact/add&ct=Organization&cst='.$params['name'].'&reset=1';
    $newName = "New ".$params['name'];
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
}