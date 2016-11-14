<?php
/**
 * Class for OptionValue configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_OptionValue extends CRM_Civiconfig_Entity {

  /**
   * CRM_Civiconfig_OptionValue constructor.
   */
  public function __construct() {
    parent::__construct('OptionValue');
  }

  /**
   * Method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  public function validateCreateParams($params) {
    parent::validateCreateParams($params);
    if (empty($params['option_group_id'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'option_group_id' in class " . get_class() . ".");
    }
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    $params['is_reserved'] = 1;
    if (!isset($params['label'])) {
      $params['label'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }
    parent::prepareParams($params, $existing);
  }

  /**
   * Method to get the existing option value.
   *
   * @param array $params
   * @return array|boolean
   */
  public function getExisting(array $params) {
    $params2 = array(
      'name' => $params['name'],
      'option_group_id' => $params['option_group_id']
    );
    try {
      return civicrm_api3('OptionValue', 'Getsingle', $params2);
    } catch (\CiviCRM_API3_Exception $ex) {
      return array();
    }
  }
}