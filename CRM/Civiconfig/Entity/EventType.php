<?php
/**
 * Class for EventType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_EventType extends CRM_Civiconfig_Entity_OptionValue {
  /**
   * Overridden parent method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  protected function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    $this->_apiParams = $params;
    try {
      $this->_apiParams['option_group_id'] = $this->getOptionGroupId();
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException("Unable to find option group for event_type in " . get_class() . ", contact your system administrator.");
    }
  }

  /**
   * Method to get option group id for event type
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public function getOptionGroupId() {
    return civicrm_api3('OptionGroup', 'Getvalue', array('name' => 'event_type', 'return' => 'id'));
  }
}