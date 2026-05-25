<?php
/** 
 * NR Customers Notice - English System Module Texts
 * 
 * System module configuration texts for NR Customers Notice.
 * Module description, title, description and status constants.
 * 
 * @package    NR Customers Notice
 * @subpackage Language
 * @category   System Module
 * @author     Axel Benkert
 * @version    1.2
 * @since      1.0.0
 * @date       2026-05-25
 * @copyright  2020-2026 Axel Benkert
 * @license    GNU General Public License
 */

  define('MODULE_CUSTOMER_NOTICES_TITLE', 'NR Customer Notices');

  $description = '
<details class="bxac-card">
  <summary class="bxac-summary" style="list-style: none;">
  <span class="bxac-arrow">▸</span>
  <span class="bxac-title">' . xtc_image(DIR_WS_ICONS.'heading/nr_customers_notice.png', 'NR Customers Notice', '', '', 'style="max-height: 32px; vertical-align: middle; margin-right: 8px;"') . 'NR Customers Notice</span>
  </summary>
  <div class="bxac-body">
    <h3 style="margin-top: 0;">Manage Customer Notices in the Shop</h3>
    <p>With this module, time-controlled customer notices can be managed centrally. Notices can be targeted to specific customer groups, pages, countries, and optionally individual customers.</p>';

  if (basename($_SERVER['PHP_SELF']) == 'module_export.php') { 
    $description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Delete all files?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=customer_notices&action=custom').'">Delete all module files</a></p>';
  }
  $description .= '</div></details>';

  define('MODULE_CUSTOMER_NOTICES_DESC', $description);

  define('MODULE_CUSTOMER_NOTICES_STATUS_TITLE', 'Module active?');
  define('MODULE_CUSTOMER_NOTICES_STATUS_DESC', 'Should the module be displayed?');
