<?php
/**
 * Class for MembershipType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_MembershipType extends CRM_Civiconfig_Entity {

  /**
   * CRM_Civiconfig_MembershipType constructor.
   */
  public function __construct() {
    parent::__construct('MembershipType');
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    // get domain id
    $this->getDomainId($params, $existing);

    // get member of contact id
    $this->getMemberOfContactId($params, $existing);

    // get financial type id
    $this->getFinancialTypeId($params, $existing);

    // get duration unit and interval
    $this->getDuration($params, $existing);

    if (empty($params['description'])) {
      $params['description'] = CRM_Civiconfig_Utils::buildLabelFromName($params['name']);
    }

    parent::prepareParams($params, $existing);
  }

  /**
   * Method to retrieve or default duration unit and interval for a membership type (required by api)
   * Rules:
   * - if unit and interval in params use those
   * - if unit in params and interval not default interval to 1 and use those
   * - if unit not in params and in existing use those
   * - if unit not in params and not in existing default to unit = 'year' and interval = 1
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing
   */
  private function getDuration(array &$params, array $existing = []) {
    if (isset($params['duration_unit']) && isset($params['duration_interval'])) {
      return;
    }
    if (isset($params['duration_unit']) && !isset($params['duration_interval'])) {
      $params['duration_interval'] = 1;
    } else {
      if (is_array($existing)) {
        $params['duration_unit'] = $existing['duration_unit'];
        $params['duration_interval'] = $existing['duration_interval'];
      } else {
        $params['duration_unit'] = 'year';
        $params['duration_interval'] = 1;
      }
    }
  }

  /**
   * Method to retrieve or default financial type id for a membership type (required param in api)
   * Rules:
   * - if in params as financial_type_id take that one
   * - if in params as financial_type retrieve financial type with getvalue and use that id or throw exception
   * - if not in params and in existing use that
   * - if not in params and not in existing default to financial type for Member Dues, failing that one use 1
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing
   * @throws Exception when error in API
   */
  private function getFinancialTypeId(array &$params, array $existing = []) {
    if (!isset($params['financial_type_id'])) {
      if (isset($params['financial_type'])) {
        try {
          $params['financial_type_id'] = civicrm_api3('FinancialType', 'Getvalue',
            array('name' => $params['financial_type'], 'return' => 'id'));
          unset($params['financial_type']);
        } catch (\CiviCRM_API3_Exception $ex) {
          throw new \CRM_Civiconfig_EntityException('Could not find a financial type with name ' . $params['financial_type']
            . ', so the membership type with name' . $params['name'] . ' can not be updated or created.
            Error from API FinancialType.Getvalue: ' . $ex->getMessage() . '.');
        }
      } else {
        if (isset($existing['financial_type_id'])) {
          $params['financial_type_id']  = $existing['financial_type_id'];
        } else {
          try {
            $params['financial_type_id'] = civicrm_api3('FinancialType', 'Getvalue',
              array('name' => 'Member Dues', 'return' => 'id'));
          } catch (\CiviCRM_API3_Exception $ex) {
            $params['financial_type_id'] = 1;
          }
        }
      }
    }
  }

  /**
   * Method to retrieve or default the domain_id for a membership type as this is a required param in the api
   * Rules:
   * - if in $params take that one
   * - if not in $params but in existing take that one
   * - if not in $params and no existing, default to first domain record found in civicrm_domain
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing
   */
  private function getDomainId(array &$params, array $existing = []) {
    if (!isset($params['domain_id'])) {
      if (isset($existing['domain_id'])) {
        $params['domain_id'] = $existing['domain_id'];
      } else {
        $params['domain_id'] = CRM_Core_DAO::singleValueQuery('SELECT MIN(id) FROM civicrm_domain');
      }
    }
  }

  /**
   * Method to retrieve or default the member of contact id for a membership type (required param in api)
   * Rules:
   * - if in params as member_of_contact_id do nothing
   * - if in params as member_of_contact retrieve contact with getvalue use that one or exception
   * - if not in params but in existing use that one
   * - if not in params and not in existing default to contact_id of domain
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing
   * @throws Exception when no single contact found with API
   */
  private function getMemberOfContactId(array &$params, array $existing = []) {
    if (!isset($params['member_of_contact_id'])) {
      if (isset($params['member_of_contact'])) {
        try {
          $params['member_of_contact_id'] = civicrm_api3('Contact', 'Getvalue',
            array('sort_name' => $params['member_of_contact'], 'return' => 'id'));
          unset($params['member_of_contact']);
        } catch (\CiviCRM_API3_Exception $ex) {
          throw new \CRM_Civiconfig_EntityException('Could not find a contact with sort_name ' . $params['member_of_contact']
            . ', so the membership type with name' . $params['name']
            . ' can not be updated or created. Error from API Contact.Getvalue: ' . $ex->getMessage() . '.');
        }
      } else {
        if (isset($existing['member_of_contact_id'])) {
          $params['member_of_contact_id'] = $existing['member_of_contact_id'];
        } else {
          try {
            $params['member_of_contact_id'] = civicrm_api3('Domain', 'Getvalue',
              array('id' => $params['domain_id'], 'return' => 'contact_id'));
          } catch (\CiviCRM_API3_Exception $ex) {
            throw new \CRM_Civiconfig_EntityException('Could not find a domain ' . $params['domain_id']
              . ', the membership type with name' . $params['name'] . ' can not be updated or created.
              Error from API Domain Getvalue: ' . $ex->getMessage() . '.');
          }
        }
      }
    }
  }

  /**
   * Method to create or update a membership type
   *
   * @param array $params
   * @return int id of created/updated entity
   * @throws Exception when error from API MembershipType Create
   */
  public function create(array $params) {
    $id = parent::create($params);

    // hack to fix visibility settings which is mucked up by API
    if (isset($params['visibility'])) {
      $this->fixVisibility($id, $params['visibility']);
    }
    return $id;
  }

  /**
   * Method to fix visibility in membership type because API sets value and not label
   *
   * @param $membershipTypeId
   * @param string $visibility
   */
  private function fixVisibility($membershipTypeId, $visibility) {
    $query = 'UPDATE civicrm_membership_type SET visibility = %1 WHERE id = %2';
    $params = array(1 => array($visibility, 'String'), 2 => array($membershipTypeId, 'Integer'));
    CRM_Core_DAO::executeQuery($query, $params);
  }
}