<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 Jan 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Config {

  private static $_singleton;

  protected $_resourcesPath = null;

  /**
   * CRM_Civiconfig_Config constructor.
   */
  function __construct() {

    $settings = civicrm_api3('Setting', 'Getsingle', array());
    $this->resourcesPath = $settings['extensionsDir'].'/org.iida.civiconfig/resources/';
    $this->setContactTypes();
    $this->setMembershipTypes();
    $this->setRelationshipTypes();
    $this->setOptionGroups();
    $this->setGroups();
    $this->setEventTypes();
    $this->setActivityTypes();
    $this->setTags();
    // customData as last one because it might need one of the previous ones (option group, relationship types)
    $this->setCustomData();
  }

  /**
   * Singleton method
   *
   * @return CRM_Civiconfig_Config
   * @access public
   * @static
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Civiconfig_Config();
    }
    return self::$_singleton;
  }

  /**
   * Method to create or get relationship types
   *
   * @throws Exception when resource file could not be loaded
   */
  protected function setRelationshipTypes() {
    $jsonFile = $this->resourcesPath.'relationship_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load relationship types configuration file for extension,
      contact your system administrator!');
    }
    $relationshipTypesJson = file_get_contents($jsonFile);
    $relationshipTypes = json_decode($relationshipTypesJson, true);
    foreach ($relationshipTypes as $relationName => $params) {
      $relationshipType = new CRM_Civiconfig_RelationshipType();
      $relationshipType->create($params);
    }
  }

  /**
   * Method to create or get membership types
   *
   * @throws Exception when resource file could not be loaded
   */
  protected function setMembershipTypes() {
    $jsonFile = $this->resourcesPath.'membership_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load membership types configuration file for extension,
      contact your system administrator!');
    }
    $membershipTypesJson = file_get_contents($jsonFile);
    $membershipTypes = json_decode($membershipTypesJson, true);
    foreach ($membershipTypes as $membershipName => $params) {
      $membershipType = new CRM_Civiconfig_MembershipType();
      $membershipType->create($params);
    }
  }

  /**
   * Method to create option groups
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setOptionGroups() {
    $jsonFile = $this->resourcesPath.'option_groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load option_groups configuration file for extension,
      contact your system administrator!');
    }
    $optionGroupsJson = file_get_contents($jsonFile);
    $optionGroups = json_decode($optionGroupsJson, true);
    foreach ($optionGroups as $name => $optionGroupParams) {
      $optionGroup = new CRM_Civiconfig_OptionGroup();
      $optionGroup->create($optionGroupParams);
    }
  }

  /**
   * Method to create contact types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setContactTypes() {
    $jsonFile = $this->resourcesPath.'contact_sub_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load contact_sub_types configuration file for extension,
      contact your system administrator!');
    }
    $contactTypesJson = file_get_contents($jsonFile);
    $contactTypes = json_decode($contactTypesJson, true);
    foreach ($contactTypes as $name => $params) {
      $contactType = new CRM_Civiconfig_ContactType();
      $contactType->create($params);
    }
  }

  /**
   * Method to create event types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setEventTypes() {
    $jsonFile = $this->resourcesPath.'event_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load event_types configuration file for extension,
      contact your system administrator!');
    }
    $eventTypesJson = file_get_contents($jsonFile);
    $eventTypes = json_decode($eventTypesJson, true);
    foreach ($eventTypes as $name => $params) {
      $eventType = new CRM_Civiconfig_EventType();
      $eventType->create($params);
    }
  }

  /**
   * Method to create activity types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setActivityTypes() {
    $jsonFile = $this->resourcesPath.'activity_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load activity_types configuration file for extension,
      contact your system administrator!');
    }
    $activityTypesJson = file_get_contents($jsonFile);
    $activityTypes = json_decode($activityTypesJson, true);
    foreach ($activityTypes as $name => $params) {
      $activityType = new CRM_Civiconfig_ActivityType();
      $activityType->create($params);
    }
  }

  /**
   * Method to create or get groups
   *
   * @throws Exception when resource file could not be loaded
   */
  protected function setGroups() {
    $jsonFile = $this->resourcesPath . 'groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load groups configuration file for extension,
      contact your system administrator!');
    }
    $groupJson = file_get_contents($jsonFile);
    $groups = json_decode($groupJson, true);
    foreach ($groups as $params) {
      $group = new CRM_Civiconfig_Group();
      $group->create($params);
    }
  }

  /**
   * Method to create or get tags
   *
   * @throws Exception when resource file could not be loaded
   */
  protected function setTags() {
    $jsonFile = $this->resourcesPath . 'tags.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load tags configuration file for extension,
      contact your system administrator!');
    }
    $tagsJson = file_get_contents($jsonFile);
    $tags = json_decode($tagsJson, true);
    foreach ($tags as $params) {
      $tag = new CRM_Civiconfig_Tag();
      $tag->create($params);
    }
  }

  /**
   * Method to set the custom data groups and fields
   *
   * @throws Exception when config json could not be loaded
   * @access protected
   */
  protected function setCustomData() {
    $jsonFile = $this->resourcesPath.'custom_data.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load custom data configuration file for extension, contact your system administrator!');
    }
    $customDataJson = file_get_contents($jsonFile);
    $customData = json_decode($customDataJson, true);
    foreach ($customData as $customGroupName => $customGroupData) {
      $customGroup = new CRM_Civiconfig_CustomGroup();
      $created = $customGroup->create($customGroupData);
      foreach ($customGroupData['fields'] as $customFieldName => $customFieldData) {
        $customFieldData['custom_group_id'] = $created['id'];
        $customField = new CRM_Civiconfig_CustomField();
        $customField->create($customFieldData);
      }
      // remove custom fields that are still on install but no longer in config
      CRM_Civiconfig_CustomField::removeUnwantedCustomFields($created['id'], $customGroupData);
    }
  }
}