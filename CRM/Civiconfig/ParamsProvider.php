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
   * If you want to get your configuration from something else than the
   * json-files in the resource directory, just overload this class, and use
   * it in Config.php.
   */
  public abstract function getParamsArray();
}
