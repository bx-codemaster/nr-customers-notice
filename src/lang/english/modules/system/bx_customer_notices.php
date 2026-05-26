<?php
/** 
 * BX Customer Notices - English System Module Texts
 * 
 * System module configuration texts for BX Customer Notices.
 * Module description, title, description and status constants.
 * 
 * @package    BX Customer Notices
 * @subpackage Language
 * @category   System Module
 * @author     Axel Benkert
 * @version    1.0.0
 * @since      1.0.0
 * @date       2026-05-25
 * @copyright  2020-2026 Axel Benkert
 * @license    GNU General Public License
 */

  define('MODULE_BX_CUSTOMER_NOTICES_TITLE', 'BX Customer Notices');

  $description = '
<details class="bxac-card">
  <summary class="bxac-summary" style="list-style: none;">
  <span class="bxac-arrow">▸</span>
  <span class="bxac-title">' . xtc_image(DIR_WS_ICONS.'heading/bx_customer_notices.png', 'BX Customer Notices', '', '', 'style="max-height: 32px; vertical-align: middle; margin-right: 8px;"') . 'BX Customer Notices</span>
  </summary>
  <div class="bxac-body">
    <h3 style="margin-top: 0;">Manage customer notices in the shop</h3>
    <p>With this module, time-controlled customer notices can be managed centrally. Notices can be targeted to specific customer groups, pages, countries, and optionally individual customers.</p>';

  if (basename($_SERVER['PHP_SELF']) == 'module_export.php' && 
  (!defined('MODULE_BX_CUSTOMER_NOTICES_STATUS') || (defined('MODULE_BX_CUSTOMER_NOTICES_STATUS') && MODULE_BX_CUSTOMER_NOTICES_STATUS !== 'True'))) { 
    $description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Delete old module files?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_customer_notices&action=custom&task=delete_old_files').'">Delete old module files?</a></p>';
  }
  $description .= '</div></details>';

  define('MODULE_BX_CUSTOMER_NOTICES_DESC', $description);

  define('MODULE_BX_CUSTOMER_NOTICES_STATUS_TITLE', 'Module active?');
  define('MODULE_BX_CUSTOMER_NOTICES_STATUS_DESC', 'Should the module be displayed?');

  define('MODULE_BX_CUSTOMER_NOTICES_CONFIG_ID_TITLE', 'Configuration ID');
  define('MODULE_BX_CUSTOMER_NOTICES_CONFIG_ID_DESC', 'The configuration ID allows the assignment of customer notices to specific shops or shop areas. In multi-shop systems with different customer notices in the shops, a unique ID must be entered here to ensure that the customer notices are correctly assigned. In a single system, the ID can be freely assigned, e.g., "default".');

  define('MODULE_BX_CUSTOMER_NOTICES_VERSION_TITLE', 'Module version');
  define('MODULE_BX_CUSTOMER_NOTICES_VERSION_DESC', 'The current module version.');
