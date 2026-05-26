<?php
/**
 * Manage Customers notice
 * 
 * @author    Timo Paul <mail@timopaul.biz>
 * @copyright (c) 2014, Timo Paul Dienstleistungen
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 *            GNU General Public License (GPL), Version 2.0
 *
 * ================================================================
 *
 * reworked by noRiddle 03-2020
 * added use of modified standard objectinfo
 * added checkbox to check all customer groups at once
 * use datetimepicker instead of spiffyCal (which btw. didn't work anyway)
 * styled a bit in lower section in edit mode
 * added new feature: restrict notice to customer country, 10-2021, noRiddle
   (ALTER TABLE customer_notices ADD countries mediumtext DEFAULT NULL;)
 * made PHP 8 ready and fixed a few issues, 01-2022, noRiddle
 * added feature to restrict to customers_id if isset, 01-2022, noRiddle
   (ALTER TABLE customer_notices ADD customers_id INT(11) DEFAULT NULL AFTER position;)
 * added Select2 customer search with AJAX and CSRF-safe POST requests, 05-2026, benax
 * improved customer override display for groups/countries and admin info labels, 05-2026, benax
 * moved helper functions to admin/includes/extra/functions/bx_customer_notices.php, 05-2026, benax
   
 * Version 1.0.0
 */

require_once 'includes/application_top.php';

if(!function_exists('xtc_get_country_name')) {
  require_once(DIR_FS_INC.'xtc_get_country_name.inc.php');
} //added for new feature "restrict to customer country", 10-2021, noRiddle

if(!function_exists('xtc_get_countriesList')) {
  require_once(DIR_FS_INC.'xtc_get_countries.inc.php');
} //added for new feature "restrict to customer country", 10-2021, noRiddle

// get all customer statuses
$customers_statuses_array = array();
foreach (xtc_get_customers_statuses() as $s) {
  $customers_statuses_array[$s['id']] = $s;
}

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

if (key_exists('nid', $_GET) && '' != $_GET['nid']) {
  $stmt = 'SELECT * FROM ' . TABLE_BX_CUSTOMER_NOTICES . ' WHERE customer_notice_id = ' . xtc_db_input($_GET['nid']);
  $query = xtc_db_query($stmt);

  if ($row = xtc_db_fetch_array($query)) {
    $notice = $row;
    $notice['customers_status'] = explode(',', $notice['customers_status']);
    $notice['pages'] = explode(',', $notice['pages']);
    $notice['countries'] = explode(',', $notice['countries']);

    $stmt = 'SELECT * FROM ' . TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION . ' WHERE customer_notice_id = ' . xtc_db_input($_GET['nid']);
    $query = xtc_db_query($stmt);
    while ($row = xtc_db_fetch_array($query)) {
      $notice['lang'][$row['languages_id']] = $row;
    }
  }
}

$action = key_exists('action', $_GET) ? $_GET['action'] : false;

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
    
    // format values
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
    if (key_exists('flag', $_GET) && xtc_not_null($_GET['flag']) && key_exists('nid', $_GET) && xtc_not_null($_GET['nid'])) {
            $stmt = 'UPDATE ' . TABLE_BX_CUSTOMER_NOTICES . ' ' .
              'SET status = ' . (int) $_GET['flag'] . ' ' .
              'WHERE customer_notice_id = ' . (int) $_GET['nid'];
      xtc_db_query($stmt);
            xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag'))));
    }
    break;
    
  // delete
  case 'delete-confirm': 
        $stmt = 'DELETE FROM ' . TABLE_BX_CUSTOMER_NOTICES . ' ' .
            'WHERE customer_notice_id = ' . (int) $_GET['nid'];
    xtc_db_query($stmt);
        $stmt = 'DELETE FROM ' . TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION . ' ' .
            'WHERE customer_notice_id = ' . (int) $_GET['nid'];
    xtc_db_query($stmt);
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action', 'flag'))));
    
    break;
  
  // update position
  case 'posup': 
  case 'posdown': 
        $stmt = 'UPDATE ' . TABLE_BX_CUSTOMER_NOTICES . ' ' .
            'SET position = position ' . ('posup' == $action ? '+' : '-') . ' 1 ' .
            'WHERE customer_notice_id = ' . (int) $_GET['nid'];
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
        <div id="headboard" style="justify-content: flex-start !important;">
          <div class="main"><strong>
            <?php 
            switch ($action) {
              case 'new':
                echo HEADING_BX_SUBTITLE_NEW_NOTICE;
              break;
              case 'edit':
                echo sprintf(HEADING_BX_SUBTITLE_EDIT_NOTICE, strip_tags($notice['lang'][$_SESSION['languages_id']]['title']));
              break;
              default:
                echo HEADING_BX_SUBTITLE_EDIT_NOTICE;
              break;
              }
            ?></strong></div>
        </div>
        <?php
          $update = 1 < count($notice) && key_exists('customer_notice_id', $notice) && '' != trim($notice['customer_notice_id']);
          echo xtc_draw_form('notice', FILENAME_CUSTOMER_NOTICES, 'action=' . ($update ? 'update' : 'insert'), 'POST') . PHP_EOL;
          echo xtc_draw_hidden_field('action', ($update ? 'update' : 'insert')) . PHP_EOL;
          if ($update)
          {
            echo xtc_draw_hidden_field('customer_notice_id', (string) $notice['customer_notice_id']) . PHP_EOL;
          }


          echo '</form>' . PHP_EOL;
        ?>


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














            <table class="customer-notices-form-shell">
              <tr>
                <td class="main">
                  <?php $update = 1 < count($notice) && key_exists('customer_notice_id', $notice) && '' != trim($notice['customer_notice_id']); ?>
                  <?php echo xtc_draw_form('notice', FILENAME_CUSTOMER_NOTICES, 'action=' . ($update ? 'update' : 'insert'), 'POST'); ?>
                    <?php
                      echo '<input type="hidden" name="action" value="' . encode_htmlspecialchars(($update ? 'update' : 'insert')) . '">';
                      if ($update) {
                        echo '<input type="hidden" name="customer_notice_id" value="' . encode_htmlspecialchars((string) $notice['customer_notice_id']) . '">';
                      }
                    ?>
                    <table class="customer-notices-form-layout">
                      <tr>
                        <td class="formArea">
                          
                          <div class="cf customer-notices-langtabs">
                            <?php echo $langtabs; ?>
                            <?php
                              foreach ($languages as $i => $l) {
                                echo ('<div id="tab_lang_' . $i . '">');
                                $lng_image = xtc_image(DIR_WS_LANGUAGES . $l['directory'] . '/admin/images/' . $l['image'], $l['name']);
                                ?>
                                <div style="background: #000000; height: 10px; ">&nbsp;</div>
                                <div class="main" style="background: #FFCC33; padding: 3px; line-height: 20px;">
                                  <?php echo $lng_image ?>&nbsp;<b><?php echo LABEL_TXT_TITLE; ?>&nbsp;</b>
                                  <?php echo xtc_draw_input_field('title[' . $l['id'] . ']', (isset($notice['lang'][$l['id']]['title']) ? $notice['lang'][$l['id']]['title'] : ''), 'style="width: 80%" maxlength="255"'); ?>
                                </div>
                                <div class="main" style="padding: 3px; line-height:20px;">
                                    <b><?php echo $lng_image . '&nbsp;' . LABEL_TXT_DESCRIPTION; ?></b><br />
                                    <?php echo xtc_draw_textarea_field('description[' . $l['id'] . ']', 'soft', '100', '10', (isset($notice['lang'][$l['id']]['description']) ? $notice['lang'][$l['id']]['description'] : '')); ?>
                                </div>
                                <?php
                                echo ('</div>');
                              }
                            ?>
                          </div>

                          <table class="customer-notices-main-table">
                            <tr>
                              <td class="dataTableContent"><strong><?php echo LABEL_TXT_STATUS; ?></strong></td>
                              <td class="dataTableContent">
                                <?php 
                                  $values = array(
                                    array('id' => 0, 'text' => NO),
                                    array('id' => 1, 'text' => YES),
                                  );
                                  //$GLOBALS['status'] = $notice['status']; // fix to select default value //what is this for ?, 01-2022, noRiddle
                                  echo xtc_draw_pull_down_menu('status', $values, (isset($notice['status']) ? $notice['status'] : ''));
                                  //echo xtc_draw_pull_down_menu('status', 'checkbox', $notice['status']); //we could do simply this, at least in 2.0.6.0, 01-2022, noRiddle
                                ?>
                              </td>
                              <td class="dataTableContent"><strong><?php echo LABEL_TXT_POSITION; ?></strong></td>
                              <td class="dataTableContent">
                                <?php echo xtc_draw_input_field('position', (isset($notice['position']) ? $notice['position'] : ''), 'maxlength="3" style="width: 50px; "'); ?>
                              </td>
                              <td class="dataTableContent">&nbsp;</td>
                              <td class="dataTableContent">&nbsp;</td>
                            </tr>
                            <tr>
                              <td class="dataTableContent"><strong><?php echo LABEL_TXT_STARTDATE; ?></strong><br /><small>(<?php echo DATETIME_FORMAT; ?>)</small></td>
                              <td class="dataTableContent">
                                <?php
                                //echo xtc_draw_input_field('startdate', $notice['startdate'], 'style="width: 150px;" id="csn-startdate"');
                                echo xtc_draw_input_field('startdate', (isCustomerNoticeEmptyDate((string)$notice['startdate']) ? '' : $notice['startdate']), 'style="width: 150px;" id="csn-startdate"');
                                ?>
                              </td>
                              <td class="dataTableContent"><strong><?php echo LABEL_TXT_ENDDATE; ?></strong><br /><small>(<?php echo DATETIME_FORMAT; ?>)</small></td>
                              <td class="dataTableContent">
                                <?php
                                //echo xtc_draw_input_field('enddate', $notice['enddate'], 'style="width: 150px;" id="csn-enddate"');
                                echo xtc_draw_input_field('enddate', (isset($notice['enddate']) && isCustomerNoticeEmptyDate((string)$notice['enddate']) ? '' : (isset($notice['enddate']) ? $notice['enddate'] : '')), 'style="width: 150px;" id="csn-enddate"');
                                ?>
                              </td>
                              <td class="dataTableContent">&nbsp;</td>
                              <td class="dataTableContent">&nbsp;</td>
                            </tr>
                            <tr>
                              <td class="dataTableContent"><strong><?php echo LABEL_TXT_TEMPLATE; ?></strong></td>
                              <td class="dataTableContent">
                                <?php
                                  $path = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/module/customer_notices/';
                                  $templates = array();
                                  foreach (glob($path . '*.html') as $file) {
                                    $templates[] = array(
                                      'id' => basename($file),
                                      'text' => basename($file)
                                    );
                                  }
                                  echo xtc_draw_pull_down_menu('template', $templates, (isset($notice['template']) ? $notice['template'] : ''));
                                ?>
                              </td>
                              <td class="dataTableContent" colspan="4"><?php echo LABEL_TXT_TEMPLATE_HINT; ?></td>
                            </tr>
                            <?php //BOC added field for customers_id, 01-2022, noRiddle ?>
                            <tr>
                              <td class="dataTableContent" colspan="2"><strong><?php echo LABEL_TXT_CUSTOMERS_ID; ?></strong><br /><small>(<?php echo TEXT_EXPL_CUSTOMERS_ID; ?>)</small></td>
                              <td class="dataTableContent" colspan="4">
                                <?php
                                  $selectedCustomer = getCustomerNoticeCustomerById(isset($notice['customers_id']) ? (int) $notice['customers_id'] : 0);
                                  $selectedCustomerId = isset($notice['customers_id']) ? (int) $notice['customers_id'] : 0;
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
                            </tr>
                            <?php //EOC added field for customers_id, 01-2022, noRiddle ?>
                            <tr>
                              <td class="dataTableContent vat customer-notices-third" colspan="2"><strong><?php echo LABEL_TXT_CUSTOMERS_GROUPS; ?></strong><br /><small>(<?php echo TEXT_OPTIONAL; ?>)</small><br /><br />
                                <?php
                                  //BOC new checkbox to check/uncheck all customer groups at once, noRiddle
                                  echo xtc_draw_selection_field('all_cst', 'checkbox', '', '', '', 'id="chck-all-cst"') . ' <label for="chck-all-cst">All</label><br />';
                                  //BOC new checkbox to check/uncheck all customer groups at once, noRiddle
                                  foreach ($customers_statuses_array as $g)
                                  {
                                    //BOC use label for more comfort when checking checkboxes, noRiddle
                                    //echo xtc_draw_selection_field('customers_status[]', 'checkbox', $g['id'], in_array($g['id'], $notice['customers_status'])) . ' ' . $g['text'] . '<br />';
                                    echo xtc_draw_selection_field('customers_status[]', 'checkbox', $g['id'], in_array($g['id'], $notice['customers_status']), '', 'id="cst-'.$g['id'].'"') . ' <label for="cst-'.$g['id'].'">' . $g['text'] . '</label><br />';
                                    //EOC use label for more comfort when checking checkboxes, noRiddle
                                  }
                                ?>
                              </td>
                              <td class="dataTableContent vat customer-notices-third" colspan="2"><strong><?php echo LABEL_TXT_PAGES; ?></strong><br /><small>(<?php echo TEXT_OPTIONAL; ?>)</small><br /><br />
                                <?php
                                  //BOC new checkbox to check/uncheck all customer groups at once, noRiddle
                                  echo xtc_draw_selection_field('all_pgs', 'checkbox', '', '', '', 'id="chck-all-pgs"') . ' <label for="chck-all-pgs">All</label><br />';
                                  //BOC new checkbox to check/uncheck all customer groups at once, noRiddle
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
                              <td class="dataTableContent vat customer-notices-third" colspan="2"><strong><?php echo LABEL_TXT_COUNTRIES; ?></strong><br /><small>(<?php echo TEXT_OPTIONAL; ?>)</small><br /><br />
                              <?php
                              //BOC for new feature "restrict to customer country", 10-2021, noRiddle
                              $csn_countries = xtc_get_countriesList();
                              ?>
                              <input type="search" id="customer-notices-country-filter" class="customer-notices-country-filter" placeholder="<?php echo TEXT_COUNTRIES_FILTER; ?>" value="" autocomplete="off" />
                              <div class="customer-notices-country-links">
                                <a href="#" id="customer-notices-country-select-all"><?php echo TEXT_COUNTRIES_SELECT_ALL; ?></a>
                                <span>|</span>
                                <a href="#" id="customer-notices-country-clear"><?php echo TEXT_COUNTRIES_CLEAR; ?></a>
                              </div>
                              <select name="countries[]" id="customer-notices-countries" class="customer-notices-country-select" multiple="multiple" size="12">
                              <?php
                              foreach($csn_countries as $csn_cntr) {
                                echo '<option value="'.(int)$csn_cntr['countries_id'].'"'.(in_array($csn_cntr['countries_id'], $notice['countries']) ? ' selected="selected"' : '').'>' . getCustomerNoticeCountryDisplayName($csn_cntr) . '</option>';
                              }
                              ?>
                              </select>
                              <?php
                              //EOC for new feature "restrict to customer country", 10-2021, noRiddle
                              ?>
                              </td>
                            </tr>
                            <tr>
                              <td class="dataTableContent customer-notices-actions" colspan="6">
                                <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('action'))); ?>"><?php echo BUTTON_CANCEL; ?></a>
                                <input type="submit" name="update" class="button" onclick="this.blur();" value="<?php echo $update ? BUTTON_UPDATE : BUTTON_INSERT; ?>" />
                                <input type="submit" name="save" class="button" onclick="this.blur();" value="<?php echo BUTTON_SAVE; ?>" />
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <br />
                  </form>
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
            if (!isset($_GET['sorting']) || !xtc_not_null($_GET['sorting']))
            {
              $_GET['sorting'] = 'position';
            }

            $desc = '-desc' == substr($_GET['sorting'], -5);
            $sort = preg_replace('#-desc$#', '', $_GET['sorting']);
            $sort = " ORDER BY ".$sort.($desc ? ' DESC' : ' ASC');

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
              //BOC added objectInfo, noRiddle
              if((!isset($_GET['nid']) || (isset($_GET['nid']) && $_GET['nid'] == $row['customer_notice_id'])) 
                  && (!isset($cnInfo) && (isset($_GET['action']) ? substr($_GET['action'], 0, 3) != 'new' : true)))
              {
                $cnInfo                   = new objectInfo($row);
                $cnInfo->customers_status = explode(',', $row['customers_status']);
                $cnInfo->pages            = explode(',', $row['pages']);
                $cnInfo->countries        = explode(',', $row['countries']); //for new feature "restrict to customer country", 10-2021, noRiddle
              }

              if((isset(($cnInfo)) && is_object($cnInfo)) && ($row['customer_notice_id'] == $cnInfo->customer_notice_id))
              { //changed to modified standard with objectInfo (see above), noRiddle
                echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $row['customer_notice_id'] . '&action=edit') . '\'">' . PHP_EOL;
              } else {
                echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'; this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $row['customer_notice_id']) . '\'">' . PHP_EOL;
              }
          ?>
            <td class="dataTableContent"><?php echo $row['customer_notice_id']; ?></td>
            <td class="dataTableContent"><?php echo $row['title']; ?></td>
            <td class="dataTableContent txta-c">
            <?php
              if (1 == (int) $row['status'])
              {
                echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10);
                echo '&nbsp;&nbsp;';
                echo '<a href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'action=updatestatus&flag=0&nid=' . $row['customer_notice_id']) . '">';
                echo xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10);
                echo '</a>';
              } else {
                echo '<a href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'action=updatestatus&flag=1&nid=' . $row['customer_notice_id']) . '">';
                echo xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10);
                echo'</a>';
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
                ? '<a href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'action=posup&nid=' . $row['customer_notice_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'arrow_up.gif', 'up', 12, 12) . '</a>&nbsp;'
                : '&nbsp;&nbsp;&nbsp;&nbsp;';

              if ($position > 1)
              {
                $positionControls .= '<a href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'action=posdown&nid=' . $row['customer_notice_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'arrow_down.gif', 'down', 12, 12) . '</a>';
              }

              echo $positionControls;
            ?>
            </td>
            <td class="dataTableContent"><?php echo formatCustomerNoticeDate($row['startdate']); ?></td>
            <td class="dataTableContent"><?php echo formatCustomerNoticeDate($row['enddate']); ?></td>
            <td class="dataTableContent"><?php echo $row['template']; ?></td>
            <td class="dataTableContent">
            <?php
              $statusNames = array();
              foreach (array_filter(explode(',', $row['customers_status'])) as $cs)
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
              if((isset($cnInfo) && is_object($cnInfo)) && ($row['customer_notice_id'] == $cnInfo->customer_notice_id))
              { //changed to modified standard with objectInfo (see above), noRiddle
                echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT);
              } else {
                echo '<a href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $row['customer_notice_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>';
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
        $heading[]  = array('text' => '<b>' . HEADING_BX_BOX_TITLE_DELETE . '</b>');
        $contents[] = array('text' => sprintf(TEXT_DELETE_NOTICE_CONFIRM, $cnInfo->title)); //$notice
				$buttons[]  = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $cnInfo->customer_notice_id) . '">' . BUTTON_CANCEL . '</a>';
				$buttons[]  = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CUSTOMER_NOTICES, xtc_get_all_get_params(array('nid', 'action')) . 'nid=' . $cnInfo->customer_notice_id . '&action=delete-confirm') . '">' . BUTTON_DELETE_NOTICE_CONFIRMATION . '</a>'; //objectinfo, noRiddle
				$contents[] = array(
				  'align' => 'center',
					'text' => implode(' ', $buttons)
				);
			}
      break;

      default:
        if(is_object($cnInfo))
        {
          $heading[]  = array('text' => '<b>' . sprintf(HEADING_BX_BOX_TITLE_DEFAULT,  $cnInfo->title). '</b>');
          $contents[] = array('text' => '<strong>'.LABEL_TXT_STATUS.'</strong> '.(1 == (int) $cnInfo->status ? YES : NO));
          $contents[] = array('text' => '<strong>'.LABEL_TXT_POSITION.'</strong> '.$cnInfo->position);
          $contents[] = array('text' => '<strong>'.LABEL_TXT_STARTDATE.'</strong> '.formatCustomerNoticeDate($cnInfo->startdate));
          $contents[] = array('text' => '<strong>'.LABEL_TXT_ENDDATE.'</strong> '.formatCustomerNoticeDate($cnInfo->enddate));
          $contents[] = array('text' => '<strong>'.LABEL_TXT_TEMPLATE.'</strong> '.$cnInfo->template);
          $hasRestrictedCustomer = ($cnInfo->customers_id != 0 && $cnInfo->customers_id != '');
          $contents[] = array('text' => '<strong>'.LABEL_TXT_CUSTOMERS_ID.'</strong> '.($hasRestrictedCustomer ? encode_htmlspecialchars(getCustomerNoticeCustomerAdminDisplay((int) $cnInfo->customers_id)) : ''));

          if ($hasRestrictedCustomer)
          {
            $contents[] = array('text' => '<strong>' . LABEL_TXT_CUSTOMERS_GROUPS . '</strong> ' . TEXT_CUSTOMER_NOTICES_OVERRIDDEN_BY_CUSTOMER_ID);
          } else {
            $groups = array();
            foreach($cnInfo->customers_status as $cs) {
              $groups[] = $customers_statuses_array[$cs]['text'];
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