<?php

require_once 'clonecontrib.civix.php';

use CRM_Clonecontrib_ExtensionUtil as E;

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function clonecontrib_civicrm_permission(&$permissions) {
  // name of extension or module
  $prefix = E::ts('CiviContribute') . ': ';
  $permissions['clone contributions'] = $prefix . E::ts('clone contributions');
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function clonecontrib_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if (CRM_Core_Permission::check('administer CiviCRM')) {
    return;
  }
  $permissions['contribution']['clone'] = 'clone contributions';
}

/**
 * Implements hook_civicrm_links().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_links
 */
function clonecontrib_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  if ($op == 'contribution.selector.row' && $objectName == 'Contribution') {
    $links[] = array(
      'name' => E::ts('Clone'),
      'url' => 'civicrm/clonecontrib/clone',
      'qs' => 'id=%%id%%&cid=%%cid%%&context=%%cxt%%',
      'title' => 'Clone contribution',
    );
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function clonecontrib_civicrm_config(&$config) {
  _clonecontrib_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function clonecontrib_civicrm_install() {
  _clonecontrib_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function clonecontrib_civicrm_enable() {
  _clonecontrib_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
 * function clonecontrib_civicrm_preProcess($formName, &$form) {
 * }
 */

/**
 * For an array of menu items, recursively get the value of the greatest navID
 * attribute.
 * @param <type> $menu
 * @param <type> $max_navID
 */
function _clonecontrib_get_max_navID(&$menu, &$max_navID = NULL) {
  foreach ($menu as $id => $item) {
    if (!empty($item['attributes']['navID'])) {
      $max_navID = max($max_navID, $item['attributes']['navID']);
    }
    if (!empty($item['child'])) {
      _clonecontrib_get_max_navID($item['child'], $max_navID);
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function clonecontrib_civicrm_navigationMenu(&$menu) {
  _clonecontrib_get_max_navID($menu, $max_navID);
  _clonecontrib_civix_insert_navigation_menu($menu, 'Administer/CiviContribute', array(
    'label' => E::ts('CloneContrib Settings'),
    'name' => 'Clone Settings',
    'url' => 'civicrm/admin/clonecontrib/settings',
    'permission' => 'administer CiviCRM',
    'operator' => 'AND',
    'separator' => NULL,
    'navID' => ++$max_navID,
  ));
  _clonecontrib_civix_navigationMenu($menu);
}
