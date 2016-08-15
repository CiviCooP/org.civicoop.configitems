<?php
/**
 * Class for Group configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_Group extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_Group constructor.
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
  private function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update group
   *
   * @param array $params
   * @return void
   * @throws Exception when error in API Group Create or when missing mandatory param name
   * @access public
   */
  public function create(array $params) {
    if (!empty($params['form_values'])) {
      // Hack for smart groups.
      $formValues = $params['form_values'];
      unset($params['form_values']);
    }
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
      // Some exception handling here would be nice:
      $this->handleSavedSearch($formValues, $existing['id']);
    }
    else {
      // Some exception handling here would be nice:
      $this->handleSavedSearch($formValues);
    }
    $this->sanitizeParams();
    try {
      $group = civicrm_api3('Group', 'Create', $this->_apiParams);
      $this->fixName($group);
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update group type with name'
        .$this->_apiParams['name'].'. Error from API Group.Create: ' . $ex->getMessage() . '.');
    }
  }

  /**
   * Method to get the group with a name
   *
   * @param string $groupName
   * @return array|bool
   * @access public
   */
  public function getWithName($groupName) {
    try {
      return civicrm_api3('Group', 'Getsingle', array('name' => $groupName));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Creates or updates a saved search.
   * 
   * A saved search has no name. So I will identify it by the ID of the
   * smart group that uses the search. I assume that a saved search is
   * used by at most one smart group.
   * 
   * If the saved search is meant for a new smart group, just leave $groupId
   * empty.
   * 
   * The saved_search_id will be assigned to the API params.
   * 
   * If $formValues is empty, nothing will happen.
   *
   * @param array $formValues
   * @param int $groupId ID of existing smart group for the saved search.
   * 
   * @throws ApiException
   */
  public function handleSavedSearch($formValues, $groupId = NULL) {
    if (empty($formValues)) {
      return;
    }
    $params = array();
    if (!empty($groupId)) {
      $groupResult = civicrm_api3('Group', 'getsingle', array(
        'id' => $groupId,
        'return' => array('saved_search_id')
      ));
      $params['id'] = $groupResult['saved_search_id'];
    }
    $params['form_values'] = $formValues;
    $savedSearchResult = civicrm_api3('SavedSearch', 'create', $params);
    $this->_apiParams['saved_search_id'] = $savedSearchResult['id'];
  }

  /**
   * Method to sanitize params for group create api
   *
   * @access private
   */
  private function sanitizeParams() {
    if (!isset($this->_apiParams['is_active'])) {
      $this->_apiParams['is_active'] = 1;
    }
    if (isset($this->_apiParams['group_type'])) {
      $this->_apiParams['group_type'] = CRM_Core_DAO::VALUE_SEPARATOR
        .$this->_apiParams['group_type'].CRM_Core_DAO::VALUE_SEPARATOR;
    }
    if (empty($this->_apiParams['title']) || !isset($this->_apiParams['title'])) {
      $this->_apiParams['title'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name']);
    }
    // if parent is set, retrieve parent number with name and set parents
    if (isset($this->_apiParams['parent'])) {
      $parentGroup = $this->getWithName($this->_apiParams['parent']);
      if ($parentGroup) {
        $this->_apiParams['parents'] = $parentGroup['id'];
      }
      unset($this->_apiParams['parent']);
    }
  }

  /**
   * Method to correct group name directly in database because creating with API causes
   * id to be added at the end of name which kind of defeats the idea of having the same name in each install
   * Core bug https://issues.civicrm.org/jira/browse/CRM-14062, resolved in 4.4.4
   *
   * @param $group
   * @access private
   */
  private function fixName($group) {
    if (CRM_Core_BAO_Domain::version() < 4.5) {
      $query = 'UPDATE civicrm_group SET name = %1 WHERE id = %2';
      $queryParams = array(
        1 => array($this->_apiParams['name'], 'String'),
        2 => array($group['id'], 'Integer'));
      CRM_Core_DAO::executeQuery($query, $queryParams);
    }
  }
}