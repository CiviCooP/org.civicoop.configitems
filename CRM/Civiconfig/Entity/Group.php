<?php
/**
 * Class for Group configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_Group extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_Group constructor.
   */
  public function __construct() {
    parent::__construct('Group');
  }

  /**
   * Method to create or update group.
   *
   * The way I handle smart group, is rather messy, mainly because of PR #8.
   * I completely replaced the 'create' function.
   *
   * @param array $params
   * @return void
   * @throws Exception when error in API Group Create or when missing mandatory param name
   * @access public
   */
  public function create(array $params) {
    // Hack for smart groups:
    if (!empty($params['form_values'])) {
      $formValues = $params['form_values'];
      unset($params['form_values']);
    }
    else{
      $formValues = NULL;
    }

    // This part is rather standard:
    $this->validateCreateParams($params);
    $existing = $this->getExisting($params);

    // Create or update saved search for smart group.
    if (!empty($formValues)) {
      $savedSearchId = $this->handleSavedSearch($formValues, $existing['id']);
      $params['saved_search_id'] = $savedSearchId;
    }

    // Standard call: prepare params.
    $this->prepareParams($params, $existing);

    // Only rebuild group if really needed (see PR #8).
    if ($existing['api.SavedSearch.get']['count'] > 0) {
      $existingSearch = CRM_Utils_Array::first($existing['api.SavedSearch.get']['values']);
    }
    else {
      $existingSearch = NULL;
    }
    if (is_array($existing) && !array_diff($params, $existing)
      && $formValues == $existingSearch['form_values']) {
      // No new things. We can return to save time.
      return;
    }
    try {
      $group = civicrm_api3('Group', 'Create', $params);
      $this->fixName($group, $params['name']);
      return $group['id'];
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update group type with name'
        .$params['name'].'. Error from API Group.Create: ' . $ex->getMessage() . '.');
    }
  }

  /**
   * Function to find an existing group based on the parameters.
   *
   * If no existing entity is found, an empty array is returned.
   * Overridden so that it also returns the saved search - if any.
   *
   * @param array $params
   * @return array
   * @access public
   * @static
   */
  public function getExisting(array $params) {
    try {
      return civicrm_api3('Group', 'getsingle', array(
        'name' => $params['name'],
        'api.SavedSearch.get' => array('id' => '$value.saved_search_id'),
      ));
    } catch (\CiviCRM_API3_Exception $ex) {
      return [];
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
   * The saved_search_id will be returned.
   *
   * If $formValues is empty, nothing will happen.
   *
   * @param array $formValues
   * @param int $groupId ID of existing smart group for the saved search.
   * @return int Saved Search ID.
   *
   * @throws ApiException
   */
  protected function handleSavedSearch($formValues, $groupId = NULL) {
    if (empty($formValues)) {
      return NULL;
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
    return $savedSearchResult['id'];
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    parent::prepareParams($params, $existing);

    if (isset($params['group_type'])) {
      $params['group_type'] = CRM_Utils_Array::implodePadded($params['group_type']);
    }
    if (empty($params['title'])) {
      $params['title'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }
    // if parent is set, retrieve parent number with name and set parents
    if (isset($params['parent'])) {
      $parentGroup = $this->getWithName($params['parent']);
      if ($parentGroup) {
        $params['parents'] = $parentGroup['id'];
      }
      unset($params['parent']);
    }
  }

  /**
   * Method to get the group with a name
   *
   * @param string $groupName
   * @return array|bool
   * @access public
   */
  private function getWithName($groupName) {
    try {
      return civicrm_api3('Group', 'getsingle', array(
        'name' => $groupName,
        'api.SavedSearch.get' => array('id' => '$value.saved_search_id'),
      ));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to correct group name directly in database because creating with API causes
   * id to be added at the end of name which kind of defeats the idea of having the same name in each install
   * Core bug https://issues.civicrm.org/jira/browse/CRM-14062, resolved in 4.4.4
   *
   * @param string $group
   * @param string name
   * @access private
   */
  private function fixName($group) {
    if (CRM_Core_BAO_Domain::version() < 4.5) {
      $query = 'UPDATE civicrm_group SET name = %1 WHERE id = %2';
      $queryParams = array(
        1 => array($name, 'String'),
        2 => array($group['id'], 'Integer'));
      CRM_Core_DAO::executeQuery($query, $queryParams);
    }
  }
}