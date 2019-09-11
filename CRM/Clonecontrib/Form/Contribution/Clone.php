<?php

use CRM_Clonecontrib_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Clonecontrib_Form_Contribution_Clone extends CRM_Core_Form {
  public function buildQuickForm() {
    $this->addButtons([
      [
        'type' => 'next',
        'name' => ts('Clone'),
        'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ],
    ]);

   parent::buildQuickForm();
  }

  public function postProcess() {
    $id = CRM_Utils_Request::retrieve('id', 'Alphanumeric', $this);
    if (!$id) {
     CRM_Core_Session::setStatus(E::ts('Please specify a contribution to clone.'), E::ts('Error'), 'error');
     return;
    }

    try {
      $contribution = civicrm_api3('Contribution', 'clone', array('id' => $id));
    } catch (CiviCRM_API3_Exception $e) {
      $message = E::ts('Could not clone; Contribution.clone API error: %1.', array(
        1 => $e->getMessage(),
      ));
      CRM_Core_Session::setStatus($message, E::ts('API Error'), 'error');
      return;
    }
    $newId = $contribution['values'][0]['id'];
    if (!$newId) {
      dsm($contribution, '$contribution but no ID');
      $message = E::ts('Unknown error; could not determine ID of cloned contribution.');
      CRM_Core_Session::setStatus($message, E::ts('Unknown error'), 'error');
      return;
    }

    $message = E::ts('The new contribution has an ID of %1.', array(
      1 => $newId,
    ));
    CRM_Core_Session::setStatus($message, E::ts('Contribution cloned'), 'success');

    parent::postProcess();
  }

}
