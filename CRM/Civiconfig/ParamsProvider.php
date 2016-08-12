<?php
/**
 * Abstract base class for a params provider.
 *
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @date 6 Jun 2016
 * @license AGPL-3.0
 */
abstract class CRM_Civiconfig_ParamsProvider {

  /**
   * Returns params needed to create entities.
   *
   * @param string $entityType CiviCRM entity type to create params for
   * 
   * If you want to get your configuration from something else than JSON files,
   * just overload this class and call CRM_Civiconfig_Config::updateConfig($yourCustomParamsProvider).
   */
  public abstract function getParamsArray($entityType);

}
