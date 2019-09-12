<?php

require_once 'CRM/Core/Form.php';

use CRM_Clonecontrib_ExtensionUtil as E;

/**
 * Form controller class for extension Settings form.
 * Borrowed heavily from
 * https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Clonecontrib_Form_Settings extends CRM_Core_Form {

  static $settingFilter = array('group' => 'clonecontrib');
  static $extensionName = 'clonecontrib';
  private $_submittedValues = array();
  private $_settings = array();

  public function __construct(
  $state = NULL, $action = CRM_Core_Action::NONE, $method = 'post', $name = NULL
  ) {

    $this->setSettings();

    parent::__construct(
      $state = NULL, $action = CRM_Core_Action::NONE, $method = 'post', $name = NULL
    );
  }

  public function buildQuickForm() {
    $settings = $this->_settings;
    $descriptions = [];
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        switch ($setting['html_type']) {
          case 'Select':
            $this->add(
              $setting['html_type'], // field type
              $setting['name'], // field name
              $setting['title'], // field label
              $this->getSettingOptions($setting), NULL, $setting['html_attributes']
            );
            break;

          case 'CheckBox':
            $this->addCheckBox(
              $setting['name'], // field name
              $setting['title'], // field label
              array_flip($this->getSettingOptions($setting))
            );
            break;

          case 'Radio':
            $this->addRadio(
              $setting['name'], // field name
              $setting['title'], // field label
              $this->getSettingOptions($setting)
            );
            break;

          default:
            $add = 'add' . $setting['quick_form_type'];
            if ($add == 'addElement') {
              $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array()));
            }
            else {
              $this->$add($name, ts($setting['title']));
            }
            break;

        }
      }
      $descriptions[$setting['name']] = ts($setting['description']);

      if (!empty($setting['X_form_rules_args'])) {
        $rules_args = (array) $setting['X_form_rules_args'];
        foreach ($rules_args as $rule_args) {
          array_unshift($rule_args, $setting['name']);
          call_user_func_array(array($this, 'addRule'), $rule_args);
        }
      }
    }
    $this->assign("descriptions", $descriptions);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    $style_path = CRM_Core_Resources::singleton()->getPath(self::$extensionName, 'css/extension.css');
    if ($style_path) {
      CRM_Core_Resources::singleton()->addStyleFile(self::$extensionName, 'css/extension.css');
    }

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    $breadCrumb = array(
      'title' => E::ts('Clonecontrib Settings'),
      'url' => CRM_Utils_System::url('civicrm/admin/clonecontrib/settings', 'reset=1'),
    );
    CRM_Utils_System::appendBreadCrumb(array($breadCrumb));

    parent::buildQuickForm();
  }

  public function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/clonecontrib/settings', 'reset=1'));
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  private function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Define the list of settings we are going to allow to be set on this form.
   */
  private function setSettings() {
    if (empty($this->_settings)) {
      $this->_settings = self::getSettings();
    }
  }

  private static function getSettings() {
    $settings = civicrm_api3('setting', 'getfields', array('filters' => self::$settingFilter));
    return $settings['values'];
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   */
  private function saveSettings() {
    $settings = $this->_settings;
    $values = array_intersect_key($this->_submittedValues, $settings);

    // checkboxes need flipping because all values are '1' instead of the key.
    foreach ($values as $key => &$value) {
      $setting = CRM_Utils_Array::value($key, $settings, FALSE);
      if ('CheckBox' == CRM_Utils_Array::value('html_type', $setting)) {
        $value = array_keys($value);
      }
    }

    civicrm_api3('setting', 'create', $values);

    // Save any that are not submitted, as well (e.g., checkboxes that aren't checked).
    $unsettings = array_fill_keys(array_keys(array_diff_key($settings, $this->_submittedValues)), NULL);
    civicrm_api3('setting', 'create', $unsettings);

    CRM_Core_Session::setStatus(" ", ts('Settings saved.'), "success");
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    static $ret;
    if (!isset($ret)) {
      $result = civicrm_api3('setting', 'get', array(
        'return' => array_keys($this->_settings),
        'sequential' => 1,
      ));
      $ret = CRM_Utils_Array::value(0, $result['values']);

      // checkboxes need flipping because all values are '1' instead of the key.
      $settings = $this->_settings;
      foreach ($ret as $key => &$value) {
        $setting = CRM_Utils_Array::value($key, $settings, FALSE);
        if ('CheckBox' == CRM_Utils_Array::value('html_type', $setting)) {
          $value = array_fill_keys(array_values($value), '1');
        }
      }
    }
    return $ret;
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
    // The Contribution.clone API will force certain fields to be cloned or
    // omitted, so we don't ask the user about these.
    unset($options['id']);
    unset($options['contact_id']);
    unset($options['financial_type_id']);
    unset($options['total_amount']);
    unset($options['receive_date']);
    unset($options['invoice_id']);
    return $options;
  }

  public function getSettingOptions($setting) {
    if (!empty($setting['X_options_callback']) && is_callable($setting['X_options_callback'])) {
      return call_user_func($setting['X_options_callback']);
    }
    else {
      return CRM_Utils_Array::value('X_options', $setting, array());
    }
  }

}
