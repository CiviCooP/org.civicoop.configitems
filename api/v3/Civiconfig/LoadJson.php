<?php

/**
 * Civiconfig.LoadJson API method.
 * Calls the loader to add and update config items from JSON files.
 *
 * @param array $params Parameters
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civiconfig_load_json($params) {

  try {

    //  default path if it isn't set ([this.extension]/resources/)
    if (empty($params['path'])) {
      $params['path'] = \CRM_Civiconfig_Utils::getDefaultResourcesPath();
    }

    // Run loader and try to parse some JSON files!
    $loader = new \CRM_Civiconfig_Loader;
    $result = $loader->updateConfigurationFromJson($params['path']);

    // Seems everything went well!
    // Individual entity types may or may have not run correctly - this will be shown in $result
    return civicrm_api3_create_success($result, $params, 'Civiconfig', 'Run');

  } catch(\CRM_Civiconfig_Exception $e) {

    // A serious error occurred, loader couldn't continue: return exception message
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * Civiconfig.LoadJson API specification.
 * This is used for documentation and validation.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 * @param array $params Info about parameters this API call supports
 */
function _civicrm_api3_civiconfig_load_json_spec(&$params) {
    $params = [
        'path' => [
          'required' => 0,
          'name' => 'path',
          'title' => 'JSON Resources Path',
          'type' => \CRM_Utils_Type::T_STRING,
        ],
    ];
}
