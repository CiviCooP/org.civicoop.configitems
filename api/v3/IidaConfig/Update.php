<?php
/**
 * IidaConfig.Update API method.
 * Kept for backwards compatibility: calls are forwarded to the Civiconfig.LoadJson API.
 */

require_once __DIR__ . '/../Civiconfig/LoadJson.php';

/**
 * @param array $params API call parameters
 * @return mixed API call results
 * @deprecated
 */
function civicrm_api3_iida_config_update($params = []) {

  return civicrm_api3_civiconfig_load_json($params);
}
