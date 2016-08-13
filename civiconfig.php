<?php
/**
 * CiviCRM Configuration Loader (org.civicoop.configitems)
 * For information about this extension, see README.md and info.xml.
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @author Kevin Levie <kevin.levie@civicoop.org>
 *
 * @package org.civicoop.configitems
 * @license AGPL-3.0
 * @link https://github.com/civicoop/org.civicoop.configitems
 */

require_once 'civiconfig.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civiconfig_civicrm_config(&$config) {
  _civiconfig_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function civiconfig_civicrm_xmlMenu(&$files) {
  _civiconfig_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civiconfig_civicrm_install() {
  _civiconfig_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civiconfig_civicrm_uninstall() {
  _civiconfig_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civiconfig_civicrm_enable() {
  _civiconfig_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civiconfig_civicrm_disable() {
  _civiconfig_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 * @return mixed Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending) for 'enqueue', returns void
 */
function civiconfig_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civiconfig_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civiconfig_civicrm_managed(&$entities) {
  _civiconfig_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civiconfig_civicrm_caseTypes(&$caseTypes) {
  _civiconfig_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civiconfig_civicrm_angularModules(&$angularModules) {
_civiconfig_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civiconfig_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civiconfig_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
