<?php
/**
 * Class for OptionGroup configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_OptionGroup extends CRM_Civiconfig_Entity {

  /**
   * CRM_Civiconfig_OptionGroup constructor.
   */
  public function __construct() {
    parent::__construct('OptionGroup');
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    if (!isset($params['is_active'])) {
      // If is_active is not explicitly given, assume that the option value
      // should be active.
      $params['is_active'] = 1;
    }
    $params['is_reserved'] = 1;
    if (!isset($params['title'])) {
      $params['title'] = ucfirst($params['name']);
    }
    parent::prepareParams($params, $existing);
  }

  /**
   * Method to create or update option group
   *
   * @param $params
   * @return int id of created/updated entity
   * @throws Exception when error in API Option Group Create
   */
  public function create(array $params) {
    $id = parent::create($params);
    if (isset($params['option_values'])) {
      try {
        $this->processOptionValues($id, $params['option_values']);
      } catch (\CiviCRM_API3_Exception $ex) {
        throw new \CRM_Civiconfig_EntityException('Could not create or update option_group with name '
          . $params['name'] . '. Error from API OptionGroup.Create: ' . $ex->getMessage() . '.');
      }
    }
    return $id;
  }

  /**
   * Method to process option values for option group
   *
   * @param int $optionGroupId
   * @param array $optionValueParams
   */
  protected function processOptionValues($optionGroupId, $optionValueParams) {
    $optionValueCreator = new CRM_Civiconfig_Entity_OptionValue();
    foreach ($optionValueParams as $optionValueName => $params) {
      $params['option_group_id'] = $optionGroupId;
      $optionValueCreator->create($params);
    }
  }
}