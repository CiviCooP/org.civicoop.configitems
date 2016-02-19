<?php
/**
 * Class with extension specific util functions
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 Jan 2016
 * @license AGPL-3.0
 */

class CRM_Civiconfig_Utils {

  /**
   * Public function to generate label from name
   *
   * @param $name
   * @return string
   * @access public
   * @static
   */
  public static function buildLabelFromName($name) {
    $nameParts = explode('_', strtolower($name));
    foreach ($nameParts as $key => $value) {
      $nameParts[$key] = ucfirst($value);
    }
    return implode(' ', $nameParts);
  }

  /**
   * Method to get list of active option values for select lists
   *
   * @param string $optionGroupName
   * @return array
   * @throws Exception when no option group found
   * @access public
   * @static
   */
  public static function getOptionGroupList($optionGroupName) {
    $valueList = array();
    $optionGroupParams = array(
      'name' => $optionGroupName,
      'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
      $optionValueParams = array(
        'option_group_id' => $optionGroupId,
        'is_active' => 1,
        'options' => array('limit' => 99999));
      $optionValues = civicrm_api3('OptionValue', 'Get', $optionValueParams);
      foreach ($optionValues['values'] as $optionValue) {
        $valueList[$optionValue['value']] = $optionValue['label'];
      }
      $valueList[0] = ts('- select -');
      asort($valueList);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name '.$optionGroupName
        .' contact your system administrator. Error from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    return $valueList;
  }
}