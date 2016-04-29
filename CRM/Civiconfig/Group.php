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
    }
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
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
   * Method to get a custom search based on the form values.
   *
   * @param array $formValues
   */
  public function getSavedSearchWithFormValues($formValues) {
    try {
      // At the moment I think I have to serialize the result, otherwise
      // SavedSearch.get won't find a thing.
      // I don't use GetSingle, because things will go wrong in case of
      // two saved searches with the same form values.
      $get_result = civicrm_api3('SavedSearch', 'get', array('form_values' => serialize($formValues)));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    if ($get_result['count'] == 0 || $get_result['is_error']) {
      return NULL;
    }
    return CRM_Utils_Array::first($get_result['values']);
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