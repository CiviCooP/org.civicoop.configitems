<?php
/**
 * IidaConfig.Update API Updates the configuration of IIDA with new settings
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_iida_config_update($params) {
  CRM_Civiconfig_Config::singleton();
  return civicrm_api3_create_success(array(ts('Updated IIDA CiviCRM installation')), $params, 'IidaConfig', 'Update');
}

