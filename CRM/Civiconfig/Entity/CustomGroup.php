<?php
/**
 * Class for CustomGroup configuration
 * 
 * This class creates the custom fields as well.
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_CustomGroup extends CRM_Civiconfig_Entity {
  /**
   * CRM_Civiconfig_CustomGroup constructor.
   */
  public function __construct() {
    parent::__construct('CustomGroup');
  }

  /**
   * Method to create custom group with custom fields.
   *
   * @param array $params
   * @return array
   * @throws Exception when error from API CustomGroup Create
   */
  public function create(array $params) {
    $fieldParamsArray = $params['fields'];
    $id = parent::create($params);

    $customFieldCreator = new CRM_Civiconfig_Entity_CustomField();
    foreach ($fieldParamsArray as $customFieldData) {
      $customFieldData['custom_group_id'] = $id;
      $customFieldCreator->create($customFieldData);
    }
    // remove custom fields that are still on install but no longer in config
    CRM_Civiconfig_Entity_CustomField::removeUnwantedCustomFields($id, $params);

    return $id;
  }

  /**
   * Manipulate $params before entity creation.
   *
   * TODO: This function is too complex and can probably be refactored.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    if (empty($params['title'])) {
      $params['title'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }
    parent::prepareParams($params, $existing);

    unset($params['fields']);

    switch ($params['extends']) {
      case "Activity":
        if (!empty($params['extends_entity_column_value'])) {
          if (is_array($params['extends_entity_column_value'])) {
            foreach ($params['extends_entity_column_value'] as $extendsValue) {
              $activityType = new CRM_Civiconfig_Entity_ActivityType();
              $found = $activityType->getExisting(['name' => $extendsValue]);
              if (isset($found['value'])) {
                $params['extends_entity_column_value'][] = $found['value'];
              }
              unset ($activityType);
            }
          } else {
            $activityType = new CRM_Civiconfig_Entity_ActivityType();
            $found = $activityType->getExisting(['name' => $params['extends_entity_column_value']]);
            if (isset($found['value'])) {
              $params['extends_entity_column_value'] = $found['value'];
            }
          }
        }
        break;
      case "Membership":
        if (!empty($params['extends_entity_column_value'])) {
          if (is_array($params['extends_entity_column_value'])) {
            foreach ($params['extends_entity_column_value'] as $extendsValue) {
              $membershipType = new CRM_Civiconfig_Entity_MembershipType();
              $found = $membershipType->getExisting(['name' => $extendsValue]);
              if (isset($found['id'])) {
                $params['extends_entity_column_value'][] = $found['id'];
              }
              unset ($membershipType);
            }
          } else {
            $membershipType = new CRM_Civiconfig_Entity_MembershipType();
            $found = $membershipType->getExisting(['name' => $params['extends_entity_column_value']]);
            if (isset($found['id'])) {
              $params['extends_entity_column_value'] = $found['id'];
            }
          }
        }
        break;
      case "Relationship":
        if (!empty($params['extends_entity_column_value'])) {
          if (is_array($params['extends_entity_column_value'])) {
            foreach ($params['extends_entity_column_value'] as $extendsValue) {
              $relationshipType = new CRM_Civiconfig_Entity_RelationshipType();
              $found = $relationshipType->getExisting(['name_a_b' => $extendsValue]);
              if (isset($found['id'])) {
                $params['extends_entity_column_value'][] = $found['id'];
              }
              unset ($relationshipType);
            }
          } else {
            $relationshipType = new CRM_Civiconfig_Entity_RelationshipType();
            $found = $relationshipType->getExisting(['name_a_b' => $params['extends_entity_column_value']]);
            if (isset($found['id'])) {
              $params['extends_entity_column_value'] = $found['id'];
            }
          }
        }
        break;
      case "ParticipantEventType":
        if (!empty($params['extends_entity_column_value'])) {
          if (is_array($params['extends_entity_column_value'])) {
            foreach ($params['extends_entity_column_value'] as $extendsValue) {
              $eventType = new CRM_Civiconfig_Entity_EventType();
              $found = $eventType->getExisting(['name' => $extendsValue]);
              if (isset($found['value'])) {
                $params['extends_entity_column_value'][] = $found['value'];
              }
              unset ($eventType);
            }
          } else {
            $eventType = new CRM_Civiconfig_Entity_EventType();
            $found = $eventType->getExisting(['name' => $params['extends_entity_column_value']]);
            if (isset($found['value'])) {
              $params['extends_entity_column_value'] = $found['value'];
            }
          }
        }
        break;
    }
  }
}