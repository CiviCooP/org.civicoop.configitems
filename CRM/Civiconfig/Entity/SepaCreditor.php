<?php
/**
 * Class for ActivityType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Civiconfig_Entity_SepaCreditor extends CRM_Civiconfig_Entity {

  protected $settings;

  protected $cycledays_override = array();

  protected $batching_OOFF_horizon_override = array();

  protected $batching_OOFF_notice_override = array();

  protected $batching_RCUR_horizon_override = array();

  protected $batching_RCUR_grace_override = array();

  protected $batching_RCUR_notice_override = array();

  protected $batching_FRST_notice_override = array();

  protected $custom_txmsg_override = array();

  /**
   * CRM_Civiconfig_OptionValue constructor.
   */
  public function __construct() {
    parent::__construct('SepaCreditor');
    $settings = civicrm_api3('Setting', 'get', []);
    $this->settings = $settings['values'];

    if (isset($this->settings['cycledays_override'])) {
      $this->cycledays_override = json_decode($this->settings['cycledays_override'], true);
    }
    if (isset($this->settings['batching_OOFF_horizon_override'])) {
      $this->batching_OOFF_horizon_override = json_decode($this->settings['batching_OOFF_horizon_override'], true);
    }
    if (isset($this->settings['batching_OOFF_notice_override'])) {
      $this->batching_OOFF_notice_override = json_decode($this->settings['batching_OOFF_notice_override'], true);
    }
    if (isset($this->settings['batching_RCUR_horizon_override'])) {
      $this->batching_RCUR_horizon_override = json_decode($this->settings['batching_RCUR_horizon_override'], true);
    }
    if (isset($this->settings['batching_RCUR_grace_override'])) {
      $this->batching_RCUR_grace_override = json_decode($this->settings['batching_RCUR_grace_override'], true);
    }
    if (isset($this->settings['batching_RCUR_notice_override'])) {
      $this->batching_RCUR_notice_override = json_decode($this->settings['batching_RCUR_notice_override'], true);
    }
    if (isset($this->settings['batching_FRST_notice_override'])) {
      $this->batching_FRST_notice_override = json_decode($this->settings['batching_FRST_notice_override'], true);
    }
    if (isset($this->settings['custom_txmsg_override'])) {
      $this->custom_txmsg_override = json_decode($this->settings['custom_txmsg_override'], true);
    }
  }

  /**
   * Method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  public function validateCreateParams($params) {
    parent::validateCreateParams($params);
    if (empty($params['identifier'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'identifier' in class " . get_class() . ".");
    }
    if (empty($params['name'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'name' in class " . get_class() . ".");
    }
    if (empty($params['iban'])) {
      throw new \CRM_Civiconfig_EntityException("Missing mandatory parameter 'iban' in class " . get_class() . ".");
    }
  }

  /**
   * Manipulate $params before entity creation.
   *
   * @param array $params params that will be used for entity creation
   * @param array $existing existing entity (if available)
   */
  protected function prepareParams(array &$params, array $existing = []) {
    if (isset($params['sepa_file_format'])) {
      $params['sepa_file_format_id'] = $this->getFileFormatByName($params['sepa_file_format']);
    }
    parent::prepareParams($params, $existing);
  }

  /**
   * Method to get the existing option value.
   *
   * @param array $params
   * @return array|boolean
   */
  public function getExisting(array $params) {
    $params = array(
      'identifier' => $params['identifier'],
    );
    try {
      return civicrm_api3('SepaCreditor', 'Getsingle', $params);
    } catch (\CiviCRM_API3_Exception $ex) {
      return array();
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
    $id = parent::create($params);
    if (isset($params['cycledays'])) {
      $this->cycledays_override[$id] = $params['cycledays'];
    } else {
      unset($this->cycledays_override[$id]);
    }
    if (isset($params['OOFF_horizon'])) {
      $this->batching_OOFF_horizon_override[$id] = $params['OOFF_horizon'];
    } else {
      unset($this->batching_OOFF_horizon_override[$id]);
    }
    if (isset($params['OOFF_notice'])) {
      $this->batching_OOFF_notice_override[$id] = $params['OOFF_notice'];
    } else {
      unset($this->batching_OOFF_notice_override[$id]);
    }
    if (isset($params['RCUR_horizon'])) {
      $this->batching_RCUR_horizon_override[$id] = $params['RCUR_horizon'];
    } else {
      unset($this->batching_RCUR_horizon_override[$id]);
    }
    if (isset($params['RCUR_grace'])) {
      $this->batching_RCUR_grace_override[$id] = $params['RCUR_grace'];
    } else {
      unset($this->batching_RCUR_grace_override[$id]);
    }
    if (isset($params['RCUR_notice'])) {
      $this->batching_RCUR_notice_override[$id] = $params['RCUR_notice'];
    } else {
      unset($this->batching_RCUR_notice_override[$id]);
    }
    if (isset($params['FRST_notice'])) {
      $this->batching_FRST_notice_override[$id] = $params['FRST_notice'];
    } else {
      unset($this->batching_FRST_notice_override[$id]);
    }
    if (isset($params['txmsg'])) {
      $this->custom_txmsg_override[$id] = $params['txmsg'];
    } else {
      unset($this->custom_txmsg_override[$id]);
    }
  }

  /**
   * Creates/updates all objects at once.
   * This function now simply gets an array of items instead of having to fetch it here.
   *
   * @param array $paramsArray
   */
  public function createAll($paramsArray) {
    parent::createAll($paramsArray);
    civicrm_api3('Setting', 'create', array(
      'cycledays_override' => json_encode($this->cycledays_override),
      'batching_OOFF_horizon_override' => json_encode($this->batching_OOFF_horizon_override),
      'batching_OOFF_notice_override' => json_encode($this->batching_OOFF_notice_override),
      'batching_RCUR_horizon_override' => json_encode($this->batching_RCUR_horizon_override),
      'batching_RCUR_grace_override' => json_encode($this->batching_RCUR_grace_override),
      'batching_RCUR_notice_override' => json_encode($this->batching_RCUR_notice_override),
      'batching_FRST_notice_override' => json_encode($this->batching_FRST_notice_override),
      'custom_txmsg_override' => json_encode($this->custom_txmsg_override),
    ));
  }

  /**
   * Returns the file format id
   *
   * @param $file_format
   *
   * @return array
   * @throws \Exception
   */
  protected function getFileFormatByName($file_format) {
    try {
      $file_format = civicrm_api3('OptionValue', 'getvalue', [
        'return' => 'value',
        'name' => $file_format,
        'option_group_id' => 'sepa_file_format'
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new \Exception("Could not find SEPA File Format: ".$file_format);
    }
    return $file_format;
  }
}
