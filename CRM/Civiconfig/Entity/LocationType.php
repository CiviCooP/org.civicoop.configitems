<?php
/**
 * Class for LocationType configuration
 *
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @date 6 Jun 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_LocationType extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_RelationshipType constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }

  /**
   * Method to validate params passed to create
   *
   * @param $params
   * @throws Exception when required param not found
   */
  private function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception('When trying to create a Location Type, name is mandatory
        parameter and can not be empty in class ' . get_class());
    }
    // The line below is in a strange place in the code. But I'll keep it
    // there, because it is there as well for every other entity type.
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update a location type
   *
   * @param array $params
   * @return mixed
   * @throws Exception when error from API LocationType Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    try {
      civicrm_api3('LocationType', 'Create', $this->_apiParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create or update location type with name '.$this->_apiParams['name']
        .', error from API LocationType Create: '.$ex->getMessage());
    }
  }

  /**
   * Function to get the location type with a name
   *
   * @param string $name
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithName($name) {
    try {
      return civicrm_api3('LocationType', 'Getsingle',
        array('name' => $name));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}