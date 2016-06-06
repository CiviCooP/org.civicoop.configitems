<?php
/**
 * Abstract base class for entity configuration
 *
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @date 6 Jun 2016
 * @license AGPL-3.0
 */
abstract class CRM_Civiconfig_Entity {
  /**
   * Method to create or update any entity
   *
   * @param array $params
   * @return mixed
   * @throws Exception when error from API RelationshipType Create
   */  
  public abstract function create(array $params);
  
  /**
   * Creates/updates all objects at once.
   * 
   * @param $paramsProvider ParamsProvider to provide the 'params' for the
   *                         objects to be created.
   */
  public function createAll(CRM_Civiconfig_ParamsProvider $paramsProvider) {
    $paramsArray = $paramsProvider->getParamsArray();
    
    foreach ($paramsArray as $params) {
      $this->create($params);
    }    
  }
}
