<?php
/** 
 * BX Customer Notices - German System Module Texts
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
    <h3 style="margin-top: 0;">Kundenhinweise im Shop verwalten</h3>
    <p>Mit diesem Modul können zeitgesteuerte Kundenhinweise zentral verwaltet werden. Hinweise lassen sich gezielt nach Kundengruppen, Seiten, Ländern und optional einzelnen Kunden ausspielen.</p>';

  if (basename($_SERVER['PHP_SELF']) == 'module_export.php' && 
  (!defined('MODULE_BX_CUSTOMER_NOTICES_STATUS') || (defined('MODULE_BX_CUSTOMER_NOTICES_STATUS') && MODULE_BX_CUSTOMER_NOTICES_STATUS !== 'True'))) { 
    $description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Alte Moduldateien löschen?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_customer_notices&action=custom&task=delete_old_files').'">Alte Moduldateien löschen?</a></p>';
  }
  $description .= '</div></details>';

  define('MODULE_BX_CUSTOMER_NOTICES_DESC', $description);

  define('MODULE_BX_CUSTOMER_NOTICES_STATUS_TITLE', 'Modul aktiv?');
  define('MODULE_BX_CUSTOMER_NOTICES_STATUS_DESC', 'Soll das Modul angezeigt werden?');

  define('MODULE_BX_CUSTOMER_NOTICES_CONFIG_ID_TITLE', 'Konfigurations-ID');
  define('MODULE_BX_CUSTOMER_NOTICES_CONFIG_ID_DESC', 'Die Konfigurations-ID ermöglicht die Zuordnung von Kundenhinweisen zu bestimmten Shops oder Shopbereichen. Bei Mehrshopsystemen mit unterschiedlichen Kundenhinweisen in den Shops muss hier eine eindeutige ID eingetragen werden, damit die Kundenhinweise korrekt zugeordnet werden können. Bei einem Einzelsystem kann die ID frei vergeben werden, z.B. "default".');

  define('MODULE_BX_CUSTOMER_NOTICES_VERSION_TITLE', 'Modulversion');
  define('MODULE_BX_CUSTOMER_NOTICES_VERSION_DESC', 'Die aktuelle Modulversion.');