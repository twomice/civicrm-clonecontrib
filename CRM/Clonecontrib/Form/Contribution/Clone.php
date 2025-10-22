<?php

use CRM_Clonecontrib_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Clonecontrib_Form_Contribution_Clone extends CRM_Core_Form {

  public function buildQuickForm() {
    $contributionStatusOptions = CRM_Contribute_BAO_Contribution::buildOptions('contribution_status_id');
    $this->add(
      // field type
      'Select',
      // field name
      'contribution_status_id',
      // field label
      E::ts('Contribution Status'),
      //, $setting['html_attributes']
      $contributionStatusOptions, NULL
    );
    $this->addButtons([
      [
        'type' => 'next',
        'name' => E::ts('Clone'),
        'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ],
    ]);
    $this->assign('elementNames', ['contribution_status_id']);
    parent::buildQuickForm();
  }

  public function postProcess() {
    $id = CRM_Utils_Request::retrieve('id', 'Alphanumeric', $this);
    if (!$id) {
      CRM_Core_Session::setStatus(E::ts('Please specify a contribution to clone.'), E::ts('Error'), 'error');
      return;
    }
    $contributionParams = array('id' => $id);

    $formValues = $this->exportValues();
    $contributionGetFields = civicrm_api3('Contribution', 'getfields', [
      'api_action' => "create",
    ]);
    $contributionFieldNames = array_keys($contributionGetFields['values']);
    foreach ($formValues as $formKey => $formValue) {
      if (in_array($formKey, $contributionFieldNames)) {
        $contributionParams['setParams'][$formKey] = $formValue;
      }
    }

    try {
      $contribution = civicrm_api3('Contribution', 'clone', $contributionParams);
    }
    catch (CiviCRM_API3_Exception $e) {
      $message = E::ts('Could not clone; Contribution.clone API error: %1.', array(
        1 => $e->getMessage(),
      ));
      CRM_Core_Session::setStatus($message, E::ts('API Error'), 'error');
      return;
    }
    $newContribution = $contribution['values'][0] ?? NULL;
    if (!$newContribution) {
      $message = E::ts('Unknown error; could not determine ID of cloned contribution.');
      CRM_Core_Session::setStatus($message, E::ts('Unknown error'), 'error');
      return;
    }

    $message = E::ts('The new contribution has been created with an ID of %1. Edit it here as needed.', array(
      1 => $newContribution['id'] ?? NULL,
    ));
    CRM_Core_Session::setStatus($message, E::ts('Contribution cloned'), 'info');

    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view/contribution', array(
      'reset' => '1',
      'action' => 'update',
      'id' => $newContribution['id'] ?? NULL,
      'cid' => $newContribution['contact_id'] ?? NULL,
      'context' => 'contribution',
    )));

    parent::postProcess();
  }

}
