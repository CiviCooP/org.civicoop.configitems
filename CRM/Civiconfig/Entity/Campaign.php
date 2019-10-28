<?php
/**
 *  Class CRM_Civiconfig_Entity_Campaign.
 *
 * @author Kevin Levie (CiviCooP) <kevin.levie@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_Campaign extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_Entity_CaseType constructor.
   */
  public function __construct() {
    parent::__construct('Campaign');
  }

  /**
   * Method to validate params for create.
   *
   * Override this method if you want custom validation for the entity params.
   *
   * @param array $params
   * @throws Exception when mandatory param not found
   */
  public function validateCreateParams($params) {
    if (empty($params['name'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    if (empty($params['campaign_type_id'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'campaign_type_id' in class " . get_class() . ".");
    }
    // TODO: use API spec to check for other mandatory fields.
  }

  /**
   * Override this method if you want to manipulate your params before creation.
   *
   * But don't forget to call the base one, to get $params['id'] right.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    parent::prepareParams($params, $existing);
    if (!isset($params['campaign_type_id']) && isset($params['campaign_type'])) {
      $params['campaign_type_id'] = $params['campaign_type'];
      unset($params['campaign_type']);
    }
  }
}
