<?php
/**
 * Class for MembershipType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_MembershipType extends CRM_Civiconfig_Entity {

  protected $_apiParams = array();

  /**
   * CRM_Civiconfig_MembershipType constructor.
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
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to build api params for create
   *
   * @param array $existing
   */
  private function buildApiParams($existing) {
    // get domain id
    $this->getDomainId($existing);

    // get member of contact id
    $this->getMemberOfContactId($existing);

    // get financial type id
    $this->getFinancialTypeId($existing);

    // get duration unit and interval
    $this->getDuration($existing);

    if (!isset($this->_apiParams['description']) || empty($this->_apiParams['description'])) {
      $this->_apiParams['description'] = CRM_Civiconfig_Utils::buildLabelFromName($this->_apiParams['name']);
    }
  }

  /**
   * Method to retrieve or default duration unit and interval for a membership type (required by api)
   * Rules:
   * - if unit and interval in params use those
   * - if unit in params and interval not default interval to 1 and use those
   * - if unit not in params and in existing use those
   * - if unit not in params and not in existing default to unit = 'year' and interval = 1
   *
   * @param array $existing
   */
  private function getDuration($existing) {
    if (isset($this->_apiParams['duration_unit']) && isset($this->_apiParams['duration_interval'])) {
      return;
    }
    if (isset($this->_apiParams['duration_unit']) && !isset($this->_apiParams['duration_interval'])) {
      $this->_apiParams['duration_interval'] = 1;
    } else {
      if (is_array($existing)) {
        $this->_apiParams['duration_unit'] = $existing['duration_unit'];
        $this->_apiParams['duration_interval'] = $existing['duration_interval'];
      } else {
        $this->_apiParams['duration_unit'] = 'year';
        $this->_apiParams['duration_interval'] = 1;
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
   * @param array $existing
   * @throws Exception when error in API
   */
  private function getFinancialTypeId($existing) {
    if (!isset($this->_apiParams['financial_type_id'])) {
      if (isset($params['financial_type'])) {
        try {
          $this->_apiParams['financial_type_id'] = civicrm_api3('FinancialType', 'Getvalue',
            array('name' => $this->_apiParams['financial_type'], 'return' => 'id'));
          unset($this->_apiParams['financial_type']);
        } catch (\CiviCRM_API3_Exception $ex) {
          throw new \CRM_Civiconfig_EntityException('Could not find a financial type with name ' . $this->_apiParams['financial_type']
            . ', so the membership type with name' . $this->_apiParams['name'] . ' can not be updated or created.
            Error from API FinancialType.Getvalue: ' . $ex->getMessage() . '.');
        }
      } else {
        if (isset($existing['financial_type_id'])) {
          $this->_apiParams['financial_type_id']  = $existing['financial_type_id'];
        } else {
          try {
            $this->_apiParams['financial_type_id'] = civicrm_api3('FinancialType', 'Getvalue',
              array('name' => 'Member Dues', 'return' => 'id'));
          } catch (\CiviCRM_API3_Exception $ex) {
            $this->_apiParams['financial_type_id'] = 1;
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
   * @param array $existing
   */
  private function getDomainId($existing) {
    if (!isset($this->_apiParams['domain_id'])) {
      if (isset($existing['domain_id'])) {
        $this->_apiParams['domain_id'] = $existing['domain_id'];
      } else {
        $this->_apiParams['domain_id'] = CRM_Core_DAO::singleValueQuery('SELECT MIN(id) FROM civicrm_domain');
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
   * @param array $existing
   * @throws Exception when no single contact found with API
   */
  private function getMemberOfContactId($existing) {
    if (!isset($this->_apiParams['member_of_contact_id'])) {
      if (isset($this->_apiParams['member_of_contact'])) {
        try {
          $this->_apiParams['member_of_contact_id'] = civicrm_api3('Contact', 'Getvalue',
            array('sort_name' => $this->_apiParams['member_of_contact'], 'return' => 'id'));
          unset($this->_apiParams['member_of_contact']);
        } catch (\CiviCRM_API3_Exception $ex) {
          throw new \CRM_Civiconfig_EntityException('Could not find a contact with sort_name ' . $this->_apiParams['member_of_contact']
            . ', so the membership type with name' . $this->_apiParams['name']
            . ' can not be updated or created. Error from API Contact.Getvalue: ' . $ex->getMessage() . '.');
        }
      } else {
        if (isset($existing['member_of_contact_id'])) {
          $this->_apiParams['member_of_contact_id'] = $existing['member_of_contact_id'];
        } else {
          try {
            $this->_apiParams['member_of_contact_id'] = civicrm_api3('Domain', 'Getvalue',
              array('id' => $this->_apiParams['domain_id'], 'return' => 'contact_id'));
          } catch (\CiviCRM_API3_Exception $ex) {
            throw new \CRM_Civiconfig_EntityException('Could not find a domain ' . $this->_apiParams['domain_id']
              . ', the membership type with name' . $this->_apiParams['name'] . ' can not be updated or created.
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
   * @return mixed
   * @throws Exception when error from API MembershipType Create
   */
  public function create(array $params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    $this->buildApiParams($existing);
    try {
      $membershipType = civicrm_api3('MembershipType', 'Create', $this->_apiParams);
      // hack to fix visibility settings which is mucked up by API
      if (isset($this->_apiParams['visibility'])) {
        $this->fixVisibility($membershipType['id'], $this->_apiParams['visibility']);
      }
    } catch (\CiviCRM_API3_Exception $ex) {
      throw new \CRM_Civiconfig_EntityException('Could not create or update membership type with name '.$this->_apiParams['name']
        .', error from API MembershipType Create: '.$ex->getMessage());
    }
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

  /**
   * Method to get a membership type with name
   *
   * @param $name
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithName($name) {
    try {
      return civicrm_api3('MembershipType', 'Getsingle', array('name' => $name));
    } catch (\CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}