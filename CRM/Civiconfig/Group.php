<?php
/**
 * Class for Group configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Group {

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
      throw new Exception('Missing mandatory param name in class CRM_Civiconfig_Group');
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update group
   *
   * @param $params
   * @throws Exception when error in API Group Create or when missing mandatory param name
   * @access public
   */
  public function create($params) {
    if (!empty($params['form_values'])) {
      // Hack for smart groups.
      $formValues = $params['form_values'];
      unset($params['form_values']);

      /*
      $savedSearch = $this->getSavedSearchWithFormValues($formValues);
      if (!isset($savedSearch['id'])) {
        try {
          $savedSearch = civicrm_api3('SavedSearch', 'create', array(
            'form_values' => $formValues,
          ));
        } catch (CiviCRM_API3_Exception $ex) {
          throw new Exception('Could not create custom search for smart group type with name '
            .$params['name'].', error from API SavedSearch Create: ' . $ex->getMessage());
        }
      }
      $params['saved_search_id'] = $savedSearch['id'];
      */
    }
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
      $this->handleSavedSearch($formValues, $existing['id']);
    }
    else {
      $this->handleSavedSearch($formValues);
    }
    $this->sanitizeParams();
    try {
      $group = civicrm_api3('Group', 'Create', $this->_apiParams);
      $this->fixName($group);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create or update group type with name'
        .$this->_apiParams['name'].', error from API Group Create: ' . $ex->getMessage());
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
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Creates, updates or deletes a saved search.
   * 
   * A saved search has no name. So I will identify it by the ID of the
   * smart group that uses the search. I assume that a saved search is
   * used by at most one smart group.
   * 
   * If $formValues is empty, an existing saved search will be deleted
   * from the group, so that it is not a smart group any more.
   * 
   * If the saved search is meant for a new smart group, just leave $groupId
   * empty.
   * 
   * API
   *
   * @param array $formValues
   * @param int $groupId ID of existing smart group for the saved search.
   */
  public function handleSavedSearch($formValues, $groupId = NULL) {
    $params = array();
    if (!empty($groupId)) {
      $groupResult = civicrm_api3('Group', 'getsingle', array(
        'id' => $groupId,
        'return' => array('saved_search_id')
      ));
      $params['id'] = $groupResult['saved_search_id'];
    }

    if (empty($formValues) && !empty($params['id'])) {
      // Delete existing saved search.
      civicrm_api3('SavedSearch', 'delete', $params);
      $this->_apiParams['saved_search_id'] = NULL;
    }
    else {
      $params['form_values'] = $formValues;
      $savedSearchResult = civicrm_api3('SavedSearch', 'create', $params);
      $this->_apiParams['saved_search_id'] = $savedSearchResult['id'];
    }
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