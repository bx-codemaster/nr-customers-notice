<?php
/**
 * Admin Controller: Customer Notices
 *
 * Diese Datei steuert die komplette Administration der Customer Notices
 * (Anlegen, Bearbeiten, Aktivieren/Deaktivieren, Sortieren, Löschen,
 * Sprachtexte, Zielgruppen- und Ländereinschränkung, Seitenzuordnung).
 *
 * Funktionsumfang (Auszug):
 * - CRUD-Logik inkl. Mehrsprachigkeit über description-Tabelle
 * - Positionsverwaltung (hoch/runter) und Status-Toggle
 * - Zielgruppensteuerung über Kundengruppen, Länder und optionale customers_id
 * - Template-Auswahl und Datumssteuerung (Start/Ende)
 * - CSRF-geschützte POST-Aktionen für Status-/Reihenfolge-/Löschaktionen
 * - Ausgabesicherheit über HTML-Escaping in Listen- und InfoBox-Bereichen
 *
 * Datenbank-Hinweise:
 * - ALTER TABLE customer_notices ADD countries mediumtext DEFAULT NULL;
 * - ALTER TABLE customer_notices ADD customers_id INT(11) DEFAULT NULL AFTER position;
 *
 * Contributors:
 * - Timo Paul <mail@timopaul.biz> (Initialentwicklung, Basismodul)
 * - noRiddle (03-2020 bis 01-2022: Rework, ObjectInfo, DateTimePicker,
 *   Gruppen-Selektor, Länder- und Kunden-ID-Restriktionen, PHP-8-Anpassungen)
 * - benax (05-2026: Select2-AJAX-Kundensuche, CSRF-sichere POST-Flows,
 *   Anzeigeverbesserungen, Refactoring von Hilfsfunktionen)
 *
 * Changelog (historisch):
 * - 03-2020: Rework durch noRiddle, Nutzung von modified ObjectInfo,
 *   Checkbox für alle Kundengruppen, DateTimePicker statt spiffyCal,
 *   visuelles Tuning im unteren Edit-Bereich
 * - 10-2021: Länderrestriktion ergänzt (countries)
 * - 01-2022: PHP-8-Readiness und diverse Fixes; customers_id-Restriktion ergänzt
 * - 05-2026: Select2-Kundensuche per AJAX, CSRF-sichere POST-Requests,
 *   Verbesserungen bei Override-Anzeigen und Admin-Labels,
 *   Auslagerung von Hilfsfunktionen
 *
 * @package    modified eCommerce
 * @subpackage Admin
 * @author     Timo Paul <mail@timopaul.biz>
 * @copyright  (c) 2014, Timo Paul Dienstleistungen
 * @license    http://www.gnu.org/licenses/gpl-2.0.html
 *             GNU General Public License (GPL), Version 2.0
 * @version    1.0.0
 */

require_once 'includes/application_top.php';

if(!function_exists('xtc_get_country_name')) {
  require_once(DIR_FS_INC.'xtc_get_country_name.inc.php');
} //added for new feature "restrict to customer country", 10-2021, noRiddle

if(!function_exists('xtc_get_countriesList')) {
  require_once(DIR_FS_INC.'xtc_get_countries.inc.php');
} //added for new feature "restrict to customer country", 10-2021, noRiddle

// get all customer statuses indexed by status id
$customers_statuses_array = xtc_get_customers_statuses(true);

// get available languages
require_once DIR_WS_CLASSES . 'language.php';
$languages = xtc_get_languages();

// create notice
$notice = array(
  'customer_notice_id' => '',
  'status'             => 1,
  'position'           => 1,
  'customers_id'       => '',
  'startdate'          => date('Y-m-d H:i:s'),
  'enddate'            => '',
  'template'           => 'default.html',
  'customers_status'   => array(),
  'pages'              => array(),
  'countries'          => array(), //for new feature "restrict to customer country", 10-2021, noRiddle
  'lang'               => array(),
);

$noticeId = 0;
if (isset($_POST['nid'])) {
  $noticeId = (int) $_POST['nid'];
} elseif (isset($_GET['nid'])) {
  $noticeId = (int) $_GET['nid'];
}

if (!function_exists('getCustomerNoticeActionTokenField')) {
  function getCustomerNoticeActionTokenField()
  {
    if (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFName']) && isset($_SESSION['CSRFToken'])) {
      return xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
    }

    return '';
  }
}

if (!function_exists('renderCustomerNoticeActionButton')) {
  function renderCustomerNoticeActionButton(array $hiddenFields, string $buttonHtml, string $formAttributes = 'style="display:inline;margin:0;"')
  {
    static $formIndex = 0;

    $form = xtc_draw_form(
      'customer_notice_action_' . (++$formIndex),
      FILENAME_CUSTOMER_NOTICES,
      xtc_get_all_get_params(array('action', 'nid', 'flag')),
      'post',
      $formAttributes
    );
    $form .= getCustomerNoticeActionTokenField();

    foreach ($hiddenFields as $fieldName => $fieldValue) {
      $form .= xtc_draw_hidden_field($fieldName, (string) $fieldValue);
    }

    $form .= $buttonHtml;
    $form .= '</form>';

    return $form;
  }
}

if ($noticeId > 0) {
  $stmt = 'SELECT * FROM ' . TABLE_BX_CUSTOMER_NOTICES . ' WHERE customer_notice_id = ' . $noticeId;
  $query = xtc_db_query($stmt);

  if ($row = xtc_db_fetch_array($query)) {
    $notice = $row;
    $notice['customers_status'] = explode(',', $notice['customers_status']);
    $notice['pages'] = explode(',', $notice['pages']);
    $notice['countries'] = explode(',', $notice['countries']);

    $stmt = 'SELECT * FROM ' . TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION . ' WHERE customer_notice_id = ' . $noticeId;
    $query = xtc_db_query($stmt);
    while ($row = xtc_db_fetch_array($query)) {
      $notice['lang'][$row['languages_id']] = $row;
    }
  }
}

$action = isset($_POST['action']) && $_POST['action'] !== ''
  ? $_POST['action']
  : (key_exists('action', $_GET) ? $_GET['action'] : false);

switch ($action) {

  case 'ajax_customer_search':
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
      'results' => getCustomerNoticeCustomerSearchResults(isset($_REQUEST['term']) ? (string) $_REQUEST['term'] : ''),
    ));
    exit;

  case 'update':
  case 'insert':
    $notice['customers_status'] = array();
    $notice['pages'] = array();
    $notice['countries'] = array();
    $notice['lang'] = array();
    
    // get values from request
    if (key_exists('customer_notice_id', $_POST)) {
      $notice['customer_notice_id'] = $_POST['customer_notice_id'];
    }
    if (key_exists('status', $_POST)) {
      $notice['status'] = $_POST['status'];
    }
    if (key_exists('position', $_POST)) {
      $notice['position'] = $_POST['position'];
    }
    if (key_exists('customers_id', $_POST)) {
      $notice['customers_id'] = $_POST['customers_id'];
    } //new feature to filter by customers-id, 01-2022, noRiddle
    if (key_exists('startdate', $_POST)) {
      $notice['startdate'] = $_POST['startdate'];
    }
    if (key_exists('enddate', $_POST)) {
      $notice['enddate'] = $_POST['enddate'];
    }
    if (key_exists('template', $_POST)) {
      $notice['template'] = $_POST['template'];
    }
    if (key_exists('customers_status', $_POST) && is_array($_POST['customers_status'])) {
      foreach ($_POST['customers_status'] as $cs) {
        $notice['customers_status'][] = (int)$cs;
      }
    }
    if (key_exists('pages', $_POST) && is_array($_POST['pages'])) {
      foreach ($_POST['pages'] as $p) {
        $notice['pages'][] = $p;
      }
    }

    if (key_exists('countries', $_POST) && is_array($_POST['countries'])) {
      foreach ($_POST['countries'] as $cntr) {
        $notice['countries'][] = (int)$cntr;
      }
    } //for new feature "restrict to customer country", 10-2021, noRiddle

    foreach ($languages as $l) {
      $notice['lang'][$l['id']] = array();
      if (key_exists('title', $_POST) && key_exists($l['id'], $_POST['title'])) {
        $notice['lang'][$l['id']]['title'] = $_POST['title'][$l['id']];
      }
      if (key_exists('description', $_POST) && key_exists($l['id'], $_POST['description'])) {
        $notice['lang'][$l['id']]['description'] = $_POST['description'][$l['id']];
      }
    }

    // check values
    $activeLanguageId   = (int)$_SESSION['languages_id'];
    $requestTitle       = isset($_REQUEST['title'][$activeLanguageId]) ? trim((string)$_REQUEST['title'][$activeLanguageId]) : '';
    $requestDescription = isset($_REQUEST['description'][$activeLanguageId]) ? trim((string)$_REQUEST['description'][$activeLanguageId]) : '';
    $requestStartdate   = isset($_REQUEST['startdate']) ? trim((string)$_REQUEST['startdate']) : '';
    $requestEnddate     = isset($_REQUEST['enddate']) ? trim((string)$_REQUEST['enddate']) : '';

    if ($requestTitle === '') {
      $messageStack->add(ERROR_MISSING_TITLE, 'error');
    }

    if ($requestDescription === '') {
      $messageStack->add(ERROR_MISSING_DESCRIPTION, 'error');
    }
    
    //BOC different format because of datetimepicker, noRiddle
    $datetimeFormat2 = '#^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}[:]*[0-9]{0,2}$#'; //new format because of use of datepicker without seconds, noRiddle
    
    if ($requestStartdate !== '' && !preg_match($datetimeFormat2, $requestStartdate)) {
      $messageStack->add(ERROR_INVALID_STARTDATE, 'error');
    }

    if ($requestEnddate !== '' && !preg_match($datetimeFormat2, $requestEnddate)) {
      $messageStack->add(ERROR_INVALID_ENDDATE, 'error');
    }

    if (0 != $messageStack->size) {
      $action = 'edit';
      break;
    }

    $notice['startdate'] = ($requestStartdate !== '' ? $requestStartdate : getCustomerNoticeEmptyStartDate());
    $notice['enddate'] = ($requestEnddate !== '' ? $requestEnddate : getCustomerNoticeEmptyEndDate());
    if (!xtc_not_null($notice['position']) || !is_int((int) $notice['position'])) {
      $notice['position'] = 1;
    } elseif (1 > (int) $notice['position']) {
      $notice['position'] = 1;
    } elseif (999 < (int) $notice['position']) {
      $notice['position'] = 999;
    }

    // insert or update
    $update = isset($notice['customer_notice_id']) && $notice['customer_notice_id'] != '';
    $sqlData = array(
      'status'            => (int)$notice['status'],
      'position'          => (int)$notice['position'],
      'customers_id'      => ($notice['customers_id'] != '' ? (int)$notice['customers_id'] : 'null'), //new feature to filter by customers-id, 01-2022, noRiddle
      'startdate'         => $notice['startdate'],
      'enddate'           => $notice['enddate'],
      'template'          => $notice['template'],
      'customers_status'  => implode(',', $notice['customers_status']),
      'pages'             => implode(',', $notice['pages']),
      'countries'         => implode(',', $notice['countries']) //for new feature "restrict to customer country", 10-2021, noRiddle
    );
    
    xtc_db_perform(
      TABLE_BX_CUSTOMER_NOTICES,
      $sqlData,
      ($update ? 'update' : 'insert'),
      ($update ? 'customer_notice_id = ' . (int)$notice['customer_notice_id'] : '')
    );

    if (!$update) {
      $notice['customer_notice_id'] = xtc_db_insert_id();
    }

    foreach ($notice['lang'] as $languages_id => $lang) {
      $csn_lng_entr_qu = xtc_db_query("SELECT * 
                                         FROM ".TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION." 
                                        WHERE languages_id = ".(int)$languages_id."
                                          AND customer_notice_id = ".(int)$notice['customer_notice_id']);
      
      $sqlData = array('title' => $lang['title'],
                       'description' => $lang['description'],
                       'languages_id' => (int)$languages_id
                      );

      if(xtc_db_num_rows($csn_lng_entr_qu) == 0) {
        $sqlData['customer_notice_id'] = (int)$notice['customer_notice_id'];
        xtc_db_perform(TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION, $sqlData);
      } else {
        xtc_db_perform(TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION, $sqlData, 'update', 'customer_notice_id = '.(int)$notice['customer_notice_id'].' AND languages_id = '.(int)$languages_id);
      }
    }

    if (isset($_POST['save']) && BUTTON_SAVE == $_POST['save']) {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag')) . 'nid=' . $notice['customer_notice_id']));
    } else {
      $action = 'edit';
    }
    break;
    
  // update status
  case 'updatestatus':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag'))));
    }

    if (key_exists('flag', $_POST) && xtc_not_null($_POST['flag']) && $noticeId > 0) {
            $stmt = 'UPDATE ' . TABLE_BX_CUSTOMER_NOTICES . ' ' .
              'SET status = ' . (int) $_POST['flag'] . ' ' .
              'WHERE customer_notice_id = ' . $noticeId;
      xtc_db_query($stmt);
            xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag'))));
    }
    break;
    
  // delete
  case 'delete-confirm': 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $noticeId <= 0) {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag'))));
    }

        $stmt = 'DELETE FROM ' . TABLE_BX_CUSTOMER_NOTICES . ' ' .
            'WHERE customer_notice_id = ' . $noticeId;
    xtc_db_query($stmt);
        $stmt = 'DELETE FROM ' . TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION . ' ' .
            'WHERE customer_notice_id = ' . $noticeId;
    xtc_db_query($stmt);
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag'))));
    
    break;
  
  // update position
  case 'posup': 
  case 'posdown': 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $noticeId <= 0) {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action'))));
    }

        $stmt = 'UPDATE ' . TABLE_BX_CUSTOMER_NOTICES . ' ' .
            'SET position = position ' . ('posup' == $action ? '+' : '-') . ' 1 ' .
            'WHERE customer_notice_id = ' . $noticeId;
    xtc_db_query($stmt);
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action'))));
    break;
}

$langtabs = '';
$langtabsCss = '';
if (in_array($action, array('new', 'edit'))) {
	$langtabsStyle = 'border: 1px solid #aaaaaa; padding: 5px; width: 850px; margin-top: -1px; margin-bottom: 10px; float: left; background-color: #F3F3F3;';
	$langtabsCss .= '.customer-notices-langtabs .tablangmenu{display:none;}';
	if (USE_ADMIN_LANG_TABS != 'false') {
		$langtabs = '<div class="tablangmenu"><ul>';
	}
	foreach ($languages as $i => $l) {
		if (USE_ADMIN_LANG_TABS != 'false') {
      $tabtmp = "'tab_lang_{$i}',";
			$langtabs .= '<li onclick="showTab('. $tabtmp . count($languages) . ')" style="cursor: pointer;" id="tabselect_' . $i . '">' . xtc_image(DIR_WS_LANGUAGES . $l['directory'] . '/admin/images/' . $l['image'], $l['name']) . ' ' . $l['name'] . '</li>';
			$langtabsCss .= '.js .customer-notices-langtabs #tab_lang_' . $i . '{display: ' . (0 == $i ? 'block' : 'none') . '; ' . $langtabsStyle . '}';
		}
		$langtabsCss .= '.customer-notices-langtabs #tab_lang_' . $i . '{display: block; ' . $langtabsStyle . '}';
	}
	if (USE_ADMIN_LANG_TABS != 'false') {
		$langtabs .= '</ul></div>';
		$langtabsCss .= '.js .customer-notices-langtabs .tablangmenu{display:block;}';
	}
}

require DIR_WS_INCLUDES . 'head.php';

// Include WYSIWYG if is activated
if (USE_WYSIWYG == 'true') {
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');
  if (in_array($action, array('new', 'edit'))) {
    echo PHP_EOL . (!function_exists('editorJSLink') ? '<script src="includes/modules/ckeditor/ckeditor.js"></script>' : '') . PHP_EOL;
    for ($i = 0; $i < count($languages); $i++) {
      echo xtc_wysiwyg('customer_notices', $_SESSION['language_code'], $languages[$i]['id']);
    }
  }
}
?>
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table class="tableBody">
      <tr>
        <?php //left_navigation
        if (USE_ADMIN_TOP_MENU == 'false') {
          echo '<td class="columnLeft2">'.PHP_EOL;
          echo '<!-- left_navigation //-->'.PHP_EOL;       
          require_once(DIR_WS_INCLUDES . 'column_left.php');
        }
        ?>
        <!-- body_text //--> 
        <td class="boxCenter">
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/bx_customer_notices.png', HEADING_BX_TITLE, '', '', 'style="max-height: 40px;"'); ?></div>
          <div class="pageHeading flt-l">
            <?php echo HEADING_BX_TITLE; ?>
            <div class="main pdg2"><?php echo HEADING_BX_SUBTITLE; ?>
            </div>
          </div>

          <div class="main flt-r pdg2 mrg5" style="margin-left:20px;">
            Version: <?php echo MODULE_BX_CUSTOMER_NOTICES_VERSION; ?><br>
            Original version by TimoPaul<br />
            Contributions: karsta (kgd), noRiddle
          </div>
<?php if (in_array($action, array('new', 'edit'))) { ?>
          <table class="tableCenter">
            <tr>
              <td class="boxCenterLeft">
                <?php
                  $update = 1 < count($notice) && key_exists('customer_notice_id', $notice) && '' != trim($notice['customer_notice_id']);
                  echo xtc_draw_form('notice', FILENAME_CUSTOMER_NOTICES, 'action=' . ($update ? 'update' : 'insert'), 'POST') . PHP_EOL;
                  echo xtc_draw_hidden_field('action', ($update ? 'update' : 'insert')) . PHP_EOL;
                  if ($update)
                  {
                    echo xtc_draw_hidden_field('customer_notice_id', (string) $notice['customer_notice_id']) . PHP_EOL;
                  }

                  include('includes/lang_tabs.php');
                  foreach ($languages as $i => $l)
                  {
                    $lng_image = xtc_image(DIR_WS_LANGUAGES . $l['directory'] . '/admin/images/' . $l['image'], $l['name']);
                ?>
                  <div id="tab_lang_<?php echo $i; ?>">
                    <div class="main" style="background: #FFCC33; padding: 3px; line-height: 20px;">
                      <?php echo $lng_image; ?> &nbsp;<strong><?php echo LABEL_TXT_TITLE; ?></strong>
                      <?php echo xtc_draw_input_field('title[' . $l['id'] . ']', (isset($notice['lang'][$l['id']]['title']) ? $notice['lang'][$l['id']]['title'] : ''), 'style="width: 100%" maxlength="255"'); ?>
                    </div>
                    <div class="main" style="padding: 3px; line-height:20px;">
                      <strong><?php echo $lng_image; ?>&nbsp;<?php echo LABEL_TXT_DESCRIPTION; ?></strong><br />
                      <?php echo xtc_draw_textarea_field('description[' . $l['id'] . ']', 'soft', '100', '10', (isset($notice['lang'][$l['id']]['description']) ? $notice['lang'][$l['id']]['description'] : '')); ?>
                    </div>
                  </div>
                <?php
                  }
                ?>

                <table class="tableConfig">
                  <tr>
                    <td class="dataTableConfig col-left"><strong><?php echo LABEL_TXT_STATUS; ?></strong></td>
                    <td class="dataTableConfig col-middle">
                      <?php
                        $values = array(
                          array('id' => 0, 'text' => NO),
                          array('id' => 1, 'text' => YES),
                        );
                        echo xtc_draw_pull_down_menu('status', $values, (isset($notice['status']) ? $notice['status'] : ''));
                      ?>
                    </td>
                    <td class="dataTableConfig col-left"><strong><?php echo LABEL_TXT_POSITION; ?></strong></td>
                    <td class="dataTableConfig col-middle">
                      <?php echo xtc_draw_input_field('position', (isset($notice['position']) ? $notice['position'] : ''), 'maxlength="3" style="width: 50px; "'); ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left">
                      <strong><?php echo LABEL_TXT_STARTDATE; ?></strong><br />
                      <small>(<?php echo DATETIME_FORMAT; ?>)</small>
                    </td>
                    <td class="dataTableConfig col-middle">
                      <?php echo xtc_draw_input_field('startdate', (isCustomerNoticeEmptyDate((string)$notice['startdate']) ? '' : $notice['startdate']), 'style="width: 150px;" id="csn-startdate"'); ?>
                    </td>
                    <td class="dataTableConfig col-left">
                      <strong><?php echo LABEL_TXT_ENDDATE; ?></strong><br />
                      <small>(<?php echo DATETIME_FORMAT; ?>)</small>
                    </td>
                    <td class="dataTableConfig col-middle">
                      <?php echo xtc_draw_input_field('enddate', (isset($notice['enddate']) && isCustomerNoticeEmptyDate((string)$notice['enddate']) ? '' : (isset($notice['enddate']) ? $notice['enddate'] : '')), 'style="width: 150px;" id="csn-enddate"'); ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><strong><?php echo LABEL_TXT_TEMPLATE; ?></strong></td>
                    <td class="dataTableConfig col-middle">
                      <?php
                        $path = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/module/customer_notices/';
                        $templates = array();
                        foreach (glob($path . '*.html') as $file) {
                          $templates[] = array(
                            'id'   => basename($file),
                            'text' => basename($file)
                          );
                        }
                        echo xtc_draw_pull_down_menu('template', $templates, (isset($notice['template']) ? $notice['template'] : ''));
                      ?>
                    </td>
                    <td class="dataTableConfig col-right" colspan="2"><?php echo LABEL_TXT_TEMPLATE_HINT; ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left">
                      <strong><?php echo LABEL_TXT_CUSTOMERS_ID; ?></strong>
                    </td>
                    <td class="dataTableConfig col-middle">
                      <?php
                        $selectedCustomer = getCustomerNoticeCustomerById(isset($notice['customers_id']) ? (int) $notice['customers_id'] : 0);
                        $selectedCustomerId    = isset($notice['customers_id']) ? (int) $notice['customers_id'] : 0;
                        $selectedCustomerLabel = $selectedCustomerId > 0 ? getCustomerNoticeCustomerLabel(
                          !empty($selectedCustomer)
                            ? $selectedCustomer
                            : array('customers_id' => $selectedCustomerId)
                        ) : '';
                        echo '<input type="hidden" name="customers_id" id="customer-notices-customer-id" value="' . encode_htmlspecialchars(($selectedCustomerId > 0 ? (string) $selectedCustomerId : '')) . '">';
                      ?>
                      <select id="customer-notices-customer-search" style="width: 100%; max-width: 420px;">
                        <option value=""></option>
                        <?php if ($selectedCustomerId > 0) { ?>
                        <option value="<?php echo $selectedCustomerId; ?>" selected="selected"><?php echo htmlspecialchars($selectedCustomerLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td class="dataTableConfig col-right" colspan="2"><?php echo TEXT_EXPL_CUSTOMERS_ID; ?></td>
                  </tr>
                </table>
                <hr>
                <table class="tableConfig bx-table-config-3cols">
                  <tr>
                    <td class="dataTableConfig col-left">
                      <h4>
                        <?php echo LABEL_TXT_CUSTOMERS_GROUPS; ?>
                        <span class="badge"><?php echo TEXT_OPTIONAL; ?></span>
                      </h4>

                      <?php
                        echo xtc_draw_selection_field('all_cst', 'checkbox', '', '', '', 'id="chck-all-cst"') . ' <label for="chck-all-cst">' . TEXT_PAGES_SELECT_ALL . '</label><br />';
                        foreach ($customers_statuses_array as $g)
                        {
                          echo xtc_draw_selection_field('customers_status[]', 'checkbox', $g['id'], in_array($g['id'], $notice['customers_status']), '', 'id="cst-'.$g['id'].'"') . ' <label for="cst-'.$g['id'].'">' . $g['text'] . '</label><br />';
                        }
                      ?>
                    </td>
                    <td class="dataTableConfig col-middle">
                      <h4>
                        <?php echo LABEL_TXT_PAGES; ?>
                        <span class="badge"><?php echo TEXT_OPTIONAL; ?></span>
                      </h4>

                      <?php
                        echo xtc_draw_selection_field('all_pgs', 'checkbox', '', '', '', 'id="chck-all-pgs"') . ' <label for="chck-all-pgs">' . TEXT_PAGES_SELECT_ALL . '</label><br />';
                        foreach (array(
                          'index',
                          'category',
                          'product_info',
                          'shop_content',
                          'shopping_cart',
                          'account',
                          'checkout',
                        ) as $p) {
                          echo xtc_draw_selection_field('pages[]', 'checkbox', $p, in_array($p, $notice['pages']), '', 'id="pg-'.$p.'"') . ' <label for="pg-'.$p.'">' . constant('FIELD_VALUE_PAGES_' . strtoupper($p)) . '</label><br />';
                        }
                      ?>
                    </td>
                    <td class="dataTableConfig col-right">
                      <h4>
                        <?php echo LABEL_TXT_COUNTRIES; ?>
                        <span class="badge"><?php echo TEXT_OPTIONAL; ?></span>
                      </h4>

                      <?php $csn_countries = xtc_get_countriesList(); ?>
                      <input type="search" id="customer-notices-country-filter" class="customer-notices-country-filter" placeholder="<?php echo TEXT_COUNTRIES_FILTER; ?>" value="" autocomplete="off" />
                      <div class="customer-notices-country-links">
                        <a href="#" id="customer-notices-country-select-all"><?php echo TEXT_COUNTRIES_SELECT_ALL; ?></a>
                        <span>|</span>
                        <a href="#" id="customer-notices-country-clear"><?php echo TEXT_COUNTRIES_CLEAR; ?></a>
                      </div>
                      <select name="countries[]" id="customer-notices-countries" class="customer-notices-country-select" multiple="multiple" size="12">
                      <?php
                        foreach ($csn_countries as $csn_cntr) {
                          echo '<option value="'.(int)$csn_cntr['countries_id'].'"'.(in_array($csn_cntr['countries_id'], $notice['countries']) ? ' selected="selected"' : '').'>' . getCustomerNoticeCountryDisplayName($csn_cntr) . '</option>';
                        }
                      ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left" colspan="3">
                      <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('action'))); ?>"><?php echo BUTTON_CANCEL; ?></a>
                      <input type="submit" name="update" class="button" onclick="this.blur();" value="<?php echo $update ? BUTTON_UPDATE : BUTTON_INSERT; ?>" />
                      <input type="submit" name="save" class="button" onclick="this.blur();" value="<?php echo BUTTON_SAVE; ?>" />
                    </td>
                  </tr>
                </table>
                </form>

                </td>
                <td class="boxRight">
                  <?php
                    $heading[]  = array('text' => '<b>' . HEADING_BX_TITLE . '</b>');
                    $contents[] = array('text' => TEXT_NO_CUSTOMER_NOTICES);
                    $box = new box;
                    echo $box->infoBox($heading, $contents);
                  ?>
                </td>
              </tr>
          </table>
<?php } else { ?>
  <table class="tableCenter">
    <tr>
      <td class="boxCenterLeft">
        <div id="headboard">
          <div class="main"><strong><?php echo TEXT_SEARCH_FILTERS; ?></strong></div>
          <div class="main">
            <?php echo xtc_draw_form('status', FILENAME_CUSTOMER_NOTICES, '', 'get'); ?>
            <?php
            $select_data = array(
                  array('id' => '', 'text' => TEXT_ALL),
                  array('id' => '1', 'text' => TEXT_ACTIVE),
                  array('id' => '0', 'text' => TEXT_INACTIVE),
                );
                echo HEADING_BX_TITLE_STATUS . ' ';
                echo xtc_draw_pull_down_menu('status', $select_data, isset($_GET['status']) ? $_GET['status'] : '', 'onChange="this.form.submit();" style="min-width: 150px;"').'</form>';
            ?>
          </div>
          <div class="main">
            <?php
              echo xtc_draw_form('search', FILENAME_CUSTOMER_NOTICES, '', 'get') . HEADING_BX_TITLE_SEARCH . ' ' . xtc_draw_input_field('search', isset($_GET['search']) ? $_GET['search'] : '').'</form>';
            ?>
          </div>
        </div>
        <table class="tableBoxCenter collapse">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_ID . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'customer_notice_id'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_TITLE . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'title'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_STATUS . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'status'); ?></td>
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_TXT_POSITION . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'position'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_STARTDATE . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'startdate'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_ENDDATE . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'enddate'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_TEMPLATE . xtc_sorting(FILENAME_CUSTOMER_NOTICES, 'template'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_CUSTOMERS_STATUS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXT_PAGES; ?></td>
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_TXT_COUNTRIES; //new feature, 10-2021, noRiddle?></td>
          </tr>
          <?php
            $search = '';
            if(isset($_GET['search']) && (xtc_not_null($_GET['search'])))
            {
              $keywords = xtc_db_input(xtc_db_prepare_input($_GET['search']));
              $search   = " WHERE cnd.title LIKE '%".$keywords."%'";
            }
            
            if(isset($_GET['status']) && (xtc_not_null($_GET['status'])))
            {
              $search .= ('' == $search ? " WHERE" : " AND") . " cn.status = ".(int)$_GET['status'];
            }

            $sort = '';
            $allowedSortFields = array(
              'customer_notice_id' => 'cn.customer_notice_id',
              'title' => 'cnd.title',
              'status' => 'cn.status',
              'position' => 'cn.position',
              'startdate' => 'cn.startdate',
              'enddate' => 'cn.enddate',
              'template' => 'cn.template',
            );
            if (!isset($_GET['sorting']) || !xtc_not_null($_GET['sorting']))
            {
              $_GET['sorting'] = 'position';
            }

            $desc = '-desc' == substr($_GET['sorting'], -5);
            $sortKey = preg_replace('#-desc$#', '', (string) $_GET['sorting']);
            if (!isset($allowedSortFields[$sortKey])) {
              $sortKey = 'position';
            }
            $sort = ' ORDER BY ' . $allowedSortFields[$sortKey] . ($desc ? ' DESC' : ' ASC');

            //secure sql, added int cast to $_SESSION['languages_id'], noRiddle
            $stmt = "SELECT cn.*, cnd.title, cnd.description
                       FROM ".TABLE_BX_CUSTOMER_NOTICES." cn
                  LEFT JOIN ".TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION." cnd
                    ON cn.customer_notice_id = cnd.customer_notice_id
                    AND cnd.languages_id = ".(int)$_SESSION['languages_id']
                    .$search
                    .$sort;

            $split = new splitPageResults($_GET['page'], 30, $stmt, $numrows);

            $query = xtc_db_query($stmt);
            
            $cnInfo = null;
            while ($row = xtc_db_fetch_array($query))
            {
              $rowNoticeId = (int) $row['customer_notice_id'];
              $rowTitle = encode_htmlspecialchars((string) $row['title']);
              $rowTemplate = encode_htmlspecialchars((string) $row['template']);

              //BOC added objectInfo, noRiddle
              if(($noticeId === 0 || $noticeId == $rowNoticeId) 
                  && (!isset($cnInfo) && (isset($_GET['action']) ? substr($_GET['action'], 0, 3) != 'new' : true)))
              {
                $cnInfo                   = new objectInfo($row);
                $cnInfo->customers_status = explode(',', $row['customers_status']);
                $cnInfo->pages            = explode(',', $row['pages']);
                $cnInfo->countries        = explode(',', $row['countries']); //for new feature "restrict to customer country", 10-2021, noRiddle
              }

              if((isset(($cnInfo)) && is_object($cnInfo)) && ($rowNoticeId == $cnInfo->customer_notice_id))
              { //changed to modified standard with objectInfo (see above), noRiddle
                echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $rowNoticeId . '&action=edit') . '\'">' . PHP_EOL;
              } else {
                echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'; this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $rowNoticeId) . '\'">' . PHP_EOL;
              }
          ?>
            <td class="dataTableContent"><?php echo $rowNoticeId; ?></td>
            <td class="dataTableContent"><?php echo $rowTitle; ?></td>
            <td class="dataTableContent txta-c">
            <?php
              if (1 == (int) $row['status'])
              {
                echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10);
                echo '&nbsp;&nbsp;';
                echo renderCustomerNoticeActionButton(
                  array('action' => 'updatestatus', 'flag' => 0, 'nid' => $rowNoticeId),
                  '<button type="submit" style="border:0;background:none;padding:0;cursor:pointer;vertical-align:middle;">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</button>'
                );
              } else {
                echo renderCustomerNoticeActionButton(
                  array('action' => 'updatestatus', 'flag' => 1, 'nid' => $rowNoticeId),
                  '<button type="submit" style="border:0;background:none;padding:0;cursor:pointer;vertical-align:middle;">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</button>'
                );
                echo '&nbsp;&nbsp;';
                echo xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
              }
            ?>
            </td>
            <td class="dataTableContent txta-c"><?php echo $row['position']; ?></td>
            <td class="dataTableContent txta-c">
            <?php
              $position = (int) $row['position'];
              $positionControls = ($position < 999)
                ? renderCustomerNoticeActionButton(
                    array('action' => 'posup', 'nid' => $rowNoticeId),
                    '<button type="submit" style="border:0;background:none;padding:0;cursor:pointer;vertical-align:middle;">' . xtc_image(DIR_WS_IMAGES . 'arrow_up.gif', 'up', 12, 12) . '</button>'
                  ) . '&nbsp;'
                : '&nbsp;&nbsp;&nbsp;&nbsp;';

              if ($position > 1)
              {
                $positionControls .= renderCustomerNoticeActionButton(
                  array('action' => 'posdown', 'nid' => $rowNoticeId),
                  '<button type="submit" style="border:0;background:none;padding:0;cursor:pointer;vertical-align:middle;">' . xtc_image(DIR_WS_IMAGES . 'arrow_down.gif', 'down', 12, 12) . '</button>'
                );
              }

              echo $positionControls;
            ?>
            </td>
            <td class="dataTableContent"><?php echo formatCustomerNoticeDate($row['startdate']); ?></td>
            <td class="dataTableContent"><?php echo formatCustomerNoticeDate($row['enddate']); ?></td>
            <td class="dataTableContent"><?php echo $rowTemplate; ?></td>
            <td class="dataTableContent">
            <?php
              $statusNames = array();
              foreach (array_filter(explode(',', $row['customers_status']), static function ($cs) {
                return $cs !== '';
              }) as $cs)
              {
                if (isset($customers_statuses_array[$cs]['text']) && $customers_statuses_array[$cs]['text'] !== '')
                {
                  $statusNames[] = $customers_statuses_array[$cs]['text'];
                }
              }

              $statusNames = array_filter($statusNames);
              echo (count($statusNames) > 0 && count($statusNames) != count($customers_statuses_array)) ? implode(', ', $statusNames) : TEXT_ALL; //noRiddle
            ?>
            </td>
            <td class="dataTableContent">
            <?php
              if (xtc_not_null($row['pages']))
              {
                $pages = array();
                foreach (explode(',', $row['pages']) as $p)
                {
                  $pages[] = constant('FIELD_VALUE_PAGES_' . strtoupper($p));
                }
                echo implode(', ', $pages);
              } else {
                echo TEXT_ALL;
              }
            ?>
            </td>
            <td class="dataTableContent">
            <?php
              //BOC for new feature "restrict to customer country", 10-2021, noRiddle
              if(xtc_not_null($row['countries']))
              {
                $countries = array();
                foreach(explode(',', $row['countries']) as $cntrid)
                {
                  $countries[] = getCustomerNoticeCountryNameById((int)$cntrid);
                }
                echo implode(', ', $countries);
              } else {
                echo TEXT_ALL;
              }
              //EOC for new feature "restrict to customer country", 10-2021, noRiddle
            ?>
            </td>
            <td class="dataTableContent">
            <?php
              if((isset($cnInfo) && is_object($cnInfo)) && ($rowNoticeId == $cnInfo->customer_notice_id))
              { //changed to modified standard with objectInfo (see above), noRiddle
                echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT);
              } else {
                echo '<a href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $rowNoticeId) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>';
              }
            ?>
            </td>
          </tr>
<?php
            } // end while
?>
          </table>
          <div class="clear"></div>
          <div class="pdg2 flt-r smallText">
            <?php
              if (!isset($action) || !in_array($action, array('new', 'edit')))
              {
            ?>
            <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CUSTOMER_NOTICES); ?>?action=new"><?php echo BUTTON_CREATE_NOTICE; ?></a>
            <?php
              }
            ?>
          </div>
        </td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      if (is_object($cnInfo)) {
        $noticeTitle = encode_htmlspecialchars((string) $cnInfo->title);
        $heading[]  = array('text' => '<b>' . HEADING_BX_BOX_TITLE_DELETE . '</b>');
        
        $contents[] = array('text' => sprintf(TEXT_DELETE_NOTICE_CONFIRM, $noticeTitle)); //$notice
        
				$buttons[]  = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $cnInfo->customer_notice_id) . '">' . BUTTON_CANCEL . '</a>';
				$buttons[]  = renderCustomerNoticeActionButton(
				  array('action' => 'delete-confirm', 'nid' => (int) $cnInfo->customer_notice_id),
				  '<button type="submit" class="button" onclick="this.blur()">' . BUTTON_DELETE_NOTICE_CONFIRMATION . '</button>'
				); //objectinfo, noRiddle
				$contents[] = array(
				  'align' => 'center',
					'text' => implode(' ', $buttons)
				);
			}
      break;

      default:
        if(is_object($cnInfo))
        {
          $noticeTitle = encode_htmlspecialchars((string) $cnInfo->title);
          $noticeTemplate = encode_htmlspecialchars((string) $cnInfo->template);
          $heading[]  = array('text' => '<b>' . sprintf(HEADING_BX_BOX_TITLE_DEFAULT,  $noticeTitle). '</b>');
          $contents[] = array('text' => '<strong>'.LABEL_TXT_STATUS.'</strong> '.(1 == (int) $cnInfo->status ? YES : NO));
          $contents[] = array('text' => '<strong>'.LABEL_TXT_POSITION.'</strong> '.$cnInfo->position);
          $contents[] = array('text' => '<strong>'.LABEL_TXT_STARTDATE.'</strong> '.formatCustomerNoticeDate($cnInfo->startdate));
          $contents[] = array('text' => '<strong>'.LABEL_TXT_ENDDATE.'</strong> '.formatCustomerNoticeDate($cnInfo->enddate));
          $contents[] = array('text' => '<strong>'.LABEL_TXT_TEMPLATE.'</strong> '.$noticeTemplate);
          $hasRestrictedCustomer = ($cnInfo->customers_id != 0 && $cnInfo->customers_id != '');
          $contents[] = array('text' => '<strong>'.LABEL_TXT_CUSTOMERS_ID.'</strong> '.($hasRestrictedCustomer ? encode_htmlspecialchars(getCustomerNoticeCustomerAdminDisplay((int) $cnInfo->customers_id)) : ''));

          if ($hasRestrictedCustomer)
          {
            $contents[] = array('text' => '<strong>' . LABEL_TXT_CUSTOMERS_GROUPS . '</strong> ' . TEXT_CUSTOMER_NOTICES_OVERRIDDEN_BY_CUSTOMER_ID);
          } else {
            $groups = array();
            foreach($cnInfo->customers_status as $cs) {
              if (isset($customers_statuses_array[$cs]['text']) && $customers_statuses_array[$cs]['text'] !== '') {
                $groups[] = $customers_statuses_array[$cs]['text'];
              }
            }
            $groups = array_filter($groups);
            $contents[] = array('text' => '<strong>' . LABEL_TXT_CUSTOMERS_GROUPS . '</strong> ' . (count($groups) > 0 && count($groups) != count($customers_statuses_array) ? '<br /> - ' . implode('<br /> - ', $groups) : TEXT_ALL)); //noRiddle
          }
          $pages = array();
          foreach (array_filter($cnInfo->pages) as $p)
          {
            $pages[] = constant('FIELD_VALUE_PAGES_' . strtoupper($p));
          }
          $contents[] = array('text' => '<strong>' . LABEL_TXT_PAGES . '</strong> ' . (count($pages) ? '<br /> - ' . implode('<br /> - ', $pages) : TEXT_ALL));

          if ($hasRestrictedCustomer)
          {
            $contents[] = array('text' => '<strong>' . LABEL_TXT_COUNTRIES . '</strong> ' . TEXT_CUSTOMER_NOTICES_OVERRIDDEN_BY_CUSTOMER_ID);
          } else {
            $countries_arr = array();

            foreach(array_filter($cnInfo->countries) as $ccntr)
            {
              $countries_arr[] = getCustomerNoticeCountryNameById((int)$ccntr);
            }
            $contents[] = array('text' => '<strong>' . LABEL_TXT_COUNTRIES . '</strong> ' . (count($countries_arr) ? '<br /> - ' . implode('<br /> - ', $countries_arr) : TEXT_ALL));
          }
          
          $contents[] = array('text' => '<strong>' . LABEL_TXT_DESCRIPTION . '</strong><br />' . nl2br((strlen(strip_tags($cnInfo->description)) > 30 ? substr(strip_tags($cnInfo->description),0,30).' ...' : strip_tags($cnInfo->description))));
          $buttons = array();
          $buttons[] = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $cnInfo->customer_notice_id . '&action=edit') . '">' . BUTTON_EDIT_NOTICE . '</a>';
          $buttons[] = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $cnInfo->customer_notice_id . '&action=delete') . '">' . BUTTON_DELETE_NOTICE . '</a>';
          $contents[] = array(
            'align' => 'center',
            'text' => implode(' ', $buttons)
          );
        }
        break;
      }
      // display box
			if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
				echo '        <td class="boxRight">' . PHP_EOL;
				$box = new box;
				echo $box->infoBox($heading, $contents);
				echo '        </td>' . PHP_EOL;
      } else {
        $heading[]  = array('text' => '<b>' . HEADING_BX_TITLE . '</b>');
        $contents[] = array('text' => TEXT_NO_CUSTOMER_NOTICES);

				echo '        <td class="boxRight">' . PHP_EOL;
				$box = new box;
				echo $box->infoBox($heading, $contents);
				echo '        </td>' . PHP_EOL;
      }
?>
      </tr>
    </table>
<?php } // end of if-else (in_array($action, array('new', 'edit'))) ?>

    </td>
    <!-- body_text_eof //-->
  </tr>
</table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>