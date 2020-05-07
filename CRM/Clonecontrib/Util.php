<?php

use CRM_Clonecontrib_ExtensionUtil as E;

/**
 * Utility methods for CloneContrib
 *
 * @author as
 */
class CRM_Clonecontrib_Util {
  public static function getSetting($settingName) {
    $result = civicrm_api3('setting', 'get', array(
      'return' => [$settingName],
      'sequential' => 1,
    ));
    $settingValue = $result['values'][0][$settingName];

    if ($settingName == 'clonecontrib_skipped_fields') {
      $validOptionKeys = array_keys(self::getSkippedFieldOptions());
      $settingValue = array_intersect($settingValue, $validOptionKeys);
    }

    return $settingValue;
  }

  public static function getSkippedFieldOptions() {
    $options = array();
    $result = civicrm_api3('Contribution', 'getfields', [
      'api_action' => "",
    ]);

    foreach ($result['values'] as $id => $value) {
      $options[$id] = $value['title'];
    }
    asort($options);
    $options['clonecontrib_all_soft_credits'] = '(' . E::ts('All soft credits') . ')';

    // The Contribution.clone API will force certain fields to be cloned or
    // omitted, so we don't ask the user about these.
    unset($options['id']);
    unset($options['contact_id']);
    unset($options['financial_type_id']);
    unset($options['receive_date']);
    unset($options['invoice_id']);
    unset($options['contribution_status_id']);
    return $options;
  }
}
