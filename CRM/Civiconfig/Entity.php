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
   * @param array $params Parameters
   * @return mixed
   * @throws Exception when error from API RelationshipType Create
   */  
  public abstract function create(array $params);
  
  /**
   * Creates/updates all objects at once.
   * This function now simply gets an array of items instead of having to fetch it here.
   *
   * @param array $paramsArray
   */
  public function createAll($paramsArray) {
    foreach ($paramsArray as $params) {
      $this->create($params);
    }    
  }

}
