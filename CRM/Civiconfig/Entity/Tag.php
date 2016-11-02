<?php
/**
 * Class for Tag configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_Tag extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_Tag constructor.
   */
  public function __construct() {
    parent::__construct('Tag');
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    // if parent is set, retrieve parent number with name and set parents
    if (isset($params['parent'])) {
      $parentTag = $this->getWithName($params['parent']);
      if ($parentTag) {
        $params['parent_id'] = $parentTag['id'];
      }
      unset($params['parent']);
    }
    if (empty($params['description'])) {
      $params['description'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }
    parent::prepareParams($params, $existing);
  }

  /**
   * Function to get the tag with a name
   *
   * @param string $name
   * @return array|bool
   * @access public
   * @static
   */
  protected function getWithName($name) {
    try {
      return civicrm_api3('Tag', 'Getsingle', array('name' => $name));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}