<?php

use CRM_Clonecontrib_ExtensionUtil as E;

return array(
  'clonecontrib_skipped_fields' => array(
    'group_name' => 'Clonecontrib Settings',
    'group' => 'clonecontrib',
    'name' => 'clonecontrib_skipped_fields',
    'type' => 'String',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Which properties should NOT be copied to the cloned contribution?'),
    'title' => E::ts('Skipped contribution properties'),
    'html_type' => 'CheckBox',
    'quick_form_type' => 'Element',
    'X_options_callback' => 'CRM_Clonecontrib_Form_Settings::getSkippedFieldOptions',
  ),
);
