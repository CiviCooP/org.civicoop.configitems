<?php
/**
 * Class for RelationshipType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_RelationshipType extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_RelationshipType constructor.
   */
  public function __construct() {
    parent::__construct('RelationshipType', 'name_a_b');
  }

  /**
   * Method to validate params passed to create
   *
   * @param $params
   * @throws Exception when required param not found
   */
  public function validateCreateParams($params) {
    if (empty($params['name_a_b']) || empty($params['name_b_a'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name_a_b' and/or 'name_b_a' in class " . get_class() . ".");
    }
  }

  /**
   * Function to find an existing entity based on the entity's parameters.
   *
   * If no existing entity is found, an empty array is returned.
   * This default implementation searches on the name, but you can override it.
   *
   * @param array $params
   * @return array
   * @access public
   * @static
   */
  public function getExisting(array $params) {
    try {
      return civicrm_api3($this->entity, 'Getsingle', array('name_a_b'=> $params['name_a_b']));
    } catch (\CiviCRM_API3_Exception $ex) {
      return [];
    }
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    if (empty($params['label_a_b'])) {
      $params['label_a_b'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name_a_b']);
    }
    if (empty($params['label_b_a'])) {
      $params['label_b_a'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name_b_a']);
    }
    parent::prepareParams($params, $existing);
  }
}