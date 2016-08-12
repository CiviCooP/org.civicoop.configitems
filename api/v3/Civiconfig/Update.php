<?php

/**
 * Civiconfig.Update API Updates the configuration with new settings
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civiconfig_update($params) {

  try {

    // Get default path if it isn't set ([this.extension]/resources/)
    if (empty($params['path'])) {
      $params['path'] = \CRM_Civiconfig_Utils::getDefaultResourcesPath();
    }

    // Run loader and try to parse some JSON files!
    $loader = new \CRM_Civiconfig_Loader;
    $result = $loader->updateConfigurationFromJson($params['path']);

    // Seems everything went well!
    // Individual entity types may or may have not run correctly - this will be shown in $result
    return civicrm_api3_create_success($result, $params, 'Civiconfig', 'Update');

  } catch(\CRM_Civiconfig_Exception $e) {

    // A serious error occurred, loader couldn't continue: return exception message
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * Civiconfig.Update API specification
 * This is used for documentation and validation.
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 *
 * @param array $params description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_civiconfig_update_spec(&$params) {
    $params = [
        'path' => [
          'required' => 0,
          'name' => 'path',
          'title' => 'Resource Directory Path (default: org.civicoop.configitems/resources)',
          'type' => \CRM_Utils_Type::T_STRING,
        ],
    ];
}