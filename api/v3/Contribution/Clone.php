<?php

use CRM_Clonecontrib_ExtensionUtil as E;

/**
 * Contribution.Clone API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_contribution_Clone_spec(&$spec) {
  $spec['id']['api.required'] = 1;
  $spec['id']['type'] = CRM_Utils_Type::T_INT;
  $spec['setParams']['description'] = E::ts('Array of values to define in the clone(s)');
}

/**
 * Contribution.Clone API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contribution_Clone($params) {
  // Get properties that should be excluded in cloning, per user config.
  $skippedFields = array();
  $result = civicrm_api3('setting', 'get', array(
    'return' => ['clonecontrib_skipped_fields'],
    'sequential' => 1,
  ));
  if ($values = CRM_Utils_Array::value(0, $result['values'])) {
    $skippedFields = $values['clonecontrib_skipped_fields'];
  }

  if (empty($params['api.ContributionSoft.get'])) {
    $params['api.ContributionSoft.get'] = array();
  }
  $params['api.ContributionSoft.get']['options']['limit'] = 0;

  // For some reason, Contribution api is sometimes not returning all fields
  // when 'return' parameter is absent; but we want them all since we're cloning.
  // So, use getfields api to get all available fields and explicitly ask for
  // them in 'return'.
  $result = civicrm_api3('Contribution', 'getfields', [
    'api_action' => "get",
  ]);
  $params['return'] = array_keys($result['values']);

  $returnValues = array();
  $setParams = $params['setParams'];
  unset($params['setParams']);
  $contributions = civicrm_api3('contribution', 'get', $params);
  foreach ($contributions['values'] as $newContribution) {
    $newContribution = array_merge($newContribution, $setParams);
    $newContribution['id'] = NULL;
    $newContribution['contribution_id'] = NULL;
    $newContribution['receive_date'] = CRM_Utils_Date::currentDBDate($timeStamp);

    // If we're not configured to skip them, clone soft credits too.
    if (!in_array('clonecontrib_all_soft_credits', $skippedFields)) {
      if ($newContribution['api.ContributionSoft.get']['count']) {
        $newContribution['api.ContributionSoft.create'] = $newContribution['api.ContributionSoft.get']['values'];
        foreach ($newContribution['api.ContributionSoft.create'] as &$value) {
          $value['id'] = NULL;
          $value['contribution_id'] = '$value.id';
        }
      }
    }
    $newContribution['api.ContributionSoft.get'] = NULL;
    $newContribution['soft_credit'] = NULL;
    $newContribution['soft_credit_to'] = NULL;
    $newContribution['soft_credit_id'] = NULL;

    // These values must be unique per contribution, so in a clone, we have to unset them:
    $newContribution['invoice_id'] = NULL;
    $newContribution['trxn_id'] = NULL;
    $newContribution['creditnote_id'] = NULL;

    foreach ($skippedFields as $skippedField) {
      unset($newContribution[$skippedField]);
    }

    // If total_amount is missing (which must be because it was in $skippedFields),
    // define it as zero.
    $newContribution['total_amount'] = CRM_Utils_Array::value('total_amount', $newContribution, 0);

    $returnValues[] = civicrm_api3('contribution', 'create', $newContribution);
  }
  return civicrm_api3_create_success($returnValues, $params, 'Contribution', 'clone');
}
