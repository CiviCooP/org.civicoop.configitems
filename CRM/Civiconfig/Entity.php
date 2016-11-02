<?php
/**
 * Abstract base class for entity configuration
 *
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @date 6 Jun 2016
 * @license AGPL-3.0
 */
abstract class CRM_Civiconfig_Entity {

  protected $entity;

  /**
   * CRM_Civiconfig_Entity constructor.
   *
   * @param string $entity The type of entity you want to configure,
   *        e.g. ContactType, RelationshipType, Group.
   */
  public function __construct($entity) {
    // TODO: validate whether $entity is a CiviCRM entity.
    $this->entity = $entity;
  }

  /**
   * Function to find an existing entity based on the entity's parameters.
   *
   * If no existing entity is found, an empty array is returned.
   * This default implementation searches on the name, but you can override it.
   *
   * @param array $params
   * @return array
   * @access public
   * @static
   */
  public function getExisting(array $params) {
    try {
      return civicrm_api3($this->entity, 'Getsingle', array('name'=> $params['name']));
    } catch (\CiviCRM_API3_Exception $ex) {
      return [];
    }
  }

  /**
   * Method to create or update any entity
   *
   * @param array $params Parameters
   * @return int id of created/updated entity
   * @throws Exception when error from API Entity Create
   */  
  public function create(array $params) {
    $existingEntity = $this->getExisting($params);
    // First prepare, then validate, because for e.g. event types, the
    // option_group_id (which should be set) is set by the prepare function.
    $this->prepareParams($params, $existingEntity);
    $this->validateCreateParams($params);
    try {
      $result = civicrm_api3($this->entity, 'Create', $params);
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException("Could not create or update {$this->entity} type with name "
        .$params['name'].". Error from API {$this->entity}.Create: " . $ex->getMessage() . '.');
    }
    return $result['id'];
  }
  
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
    if (isset($existing['id'])) {
      $params['id'] = $existing['id'];
    }
    if (!isset($params['is_active'])) {
        // if is_active is not explicitly given, assume that the entity is active.
        // the entities 'setting' and 'tag' don't have an 'is_active' field, but I don't think
        // it hurts that the param is set.
        $params['is_active'] = 1;
    }
  }
}
