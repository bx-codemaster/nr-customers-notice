<?php
/**
 * Manage Customers notice
 * 
 * @author    Timo Paul <mail@timopaul.biz>
 * @copyright (c) 2014, Timo Paul Dienstleistungen
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 *            GNU General Public License (GPL), Version 2.0
 *
 * =================================================================
 * reworked by noRiddle, 03-2020
 * use configuration constant instead of hard coded 1 to exclude guests from newsletter popup
 * took of single quotes in WHERE-clauses for integer fields
 * simplify query with FIND_IN_SET
 * secure queries (see comments)
 * fixed minor faults (notices or warnings)
 * formated code a bit
 * new feature "restrict to customer country", 10-2021, noRiddle
 * new feature restrict to customers_id if isset, 01-2022, noRiddle
 * 
 * Version 1.0.0
 */

class CustomerNoticesManager {
  public static function run() {
    global $smarty, $category_depth;
    $startNullDate = '1000-01-01 00:00:00';
    $endNullDate = '9999-12-31 23:59:59';

    //p3e BOF 20180305 Kunde ist Newsletterempfänger?
    $newletter = true ;
    //If ($_SESSION['customers_status']['customers_status_id'] != 1) {
    if($_SESSION['customers_status']['customers_status_id'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) { //use configuration constant instead of hard coded 1, noRiddle
      if(isset($_SESSION['customer_id'])) { //if not set it will be 0 for some reason and will throw "Got an error reading communication packets", 02-2021, noRiddle 
        $account_query = xtc_db_query("SELECT customers_id, 
                                              customers_email_address 
                                         FROM ".TABLE_CUSTOMERS." 
                                        WHERE customers_id = ".(int)$_SESSION['customer_id']);
        $account_mail = xtc_db_fetch_array($account_query);
        //added missing single quotes with $account_mail[customers_email_address] below, noRiddle
        $check_mail_query = xtc_db_query("SELECT customers_email_address,
                                                 customers_id
                                            FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                           WHERE customers_email_address = '".$account_mail['customers_email_address']."'
                                             AND mail_status = 1
                                       ");
        if (xtc_db_num_rows($check_mail_query) == 0) $newletter = false;
      }
    }
    //p3e EOF 20180305 Kunde ist Newsletterempfänger?

    $cs = (int)$_SESSION['customers_status']['customers_status_id']; //cast to int for secure sql, noRiddle

    $script = basename($_SERVER['SCRIPT_NAME']);
    $script = substr($script, 0, strripos($script, '.'));
    if ('index' == $script && xtc_not_null($category_depth) && 'top' != $category_depth) {
      $script = 'category';
    }
    if(preg_match('#^(account|address)_#', $script)) {
      $script = 'account';
    }
    if(preg_match('#^(checkout|checkout)_#', $script)) {
      $script = 'checkout';
    }

    //secure sql, added int cast to $_SESSION['languages_id'] below, noRiddle
        $stmt = "SELECT cn.*, cnd.title, cnd.description
          FROM ".TABLE_BX_CUSTOMER_NOTICES." AS cn
        LEFT JOIN ".TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION." cnd
                 ON cn.customer_notice_id = cnd.customer_notice_id
                AND cnd.languages_id = ".(int)$_SESSION['languages_id']."
              WHERE cn.status = 1";

    if((isset($_SESSION['cs_popup']) && $_SESSION['cs_popup'] == 'popup') OR ($newletter == true )) $stmt .= " AND cn.template <> 'newsletter.html'"; // p3e 20180302 PopUps nur einmal pro Session und nur wenn kein Newsletterempfänger
    
    //BOC reworked for new feature to filter by customers_id, 01-2022, noRiddle
    //secure sql, added xtc_db_input() to $script below, noRiddle
    /*
    $stmt .=  " AND (cn.startdate = '".$nullDate."' OR cn.startdate <= now())" . // < '".date('Y-m-d H:i:s')."'
              " AND (cn.enddate = '".$nullDate."' OR cn.enddate > now())" . // > '".date('Y-m-d H:i:s')."'
              //BOC use FIND_IN_SET instead of complicated concatenated LIKE searches, noRiddle
              //'AND (cn.customers_status = "" OR cn.customers_status LIKE "' . $cs . '" OR cn.customers_status LIKE "' . $cs . ',%" OR cn.customers_status LIKE "%,' . $cs . '" OR cn.customers_status LIKE "%,' . $cs . ',%") ' .
              " AND (cn.customers_status = '' OR FIND_IN_SET(".$cs.", cn.customers_status) > 0)" .
              //'AND (cn.pages = "" OR cn.pages LIKE "' . $script . '" OR cn.pages LIKE "' . $script . ',%" OR cn.pages LIKE "%,' . $script . '" OR cn.pages LIKE "%,' . $script . ',%") ' .
              " AND (cn.pages = '' OR FIND_IN_SET('".xtc_db_input($script)."', cn.pages) > 0)";
              //EOC use FIND_IN_SET instead of complicated concatenated LIKE searches, noRiddle
              //BOC new feature "restrict to customer country", 10-2021, noRiddle
              $csn_sess_cntr_id = isset($_SESSION['customer_country_id']) ? (int)$_SESSION['customer_country_id'] : 0;
              $stmt .= " AND (IF(cn.countries = '', 1 = 1, FIND_IN_SET(".$csn_sess_cntr_id.", cn.countries) > 0) )";
              //EOC new feature "restrict to customer country", 10-2021, noRiddle
    */
    $csn_sess_cntr_id = isset($_SESSION['customer_country_id']) ? (int)$_SESSION['customer_country_id'] : 0;
    $csn_is_customers_id = isset($_SESSION['customer_id']) && is_numeric($_SESSION['customer_id']) ? true : false;

    $stmt .=  " AND (cn.startdate = '".$startNullDate."' OR cn.startdate <= now())"
         ." AND (cn.enddate = '".$endNullDate."' OR cn.enddate > now())"
             ." AND (cn.pages = '' OR FIND_IN_SET('".xtc_db_input($script)."', cn.pages) > 0)";
             
    $stmt .= " AND (IF(cn.customers_id IS NULL OR cn.customers_id = 0, 
                        (cn.customers_status = '' OR FIND_IN_SET(".$cs.", cn.customers_status) > 0) AND (IF(cn.countries = '', 1 = 1, FIND_IN_SET(".$csn_sess_cntr_id.", cn.countries) > 0)), 
                        cn.customers_id = ".($csn_is_customers_id === true ? (int)$_SESSION['customer_id'] : '\'X\'')."
                      )
                    )";
    //EOC reworked for new feature to filter by customers_id, 01-2022, noRiddle

    $stmt .= " ORDER BY position";

//echo '<br><br><br><pre>'.$stmt.'</pre>'; //debug

    $query = xtc_db_query($stmt);

    $str = '';
    if(xtc_db_num_rows($query) > 0) { //do we have results at all ?, 10-2021, noRiddle
      while($row = xtc_db_fetch_array($query)) {
        $s = new Smarty();
        $s->assign('language', $_SESSION['language']);
        $s->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
        $s->caching = 0;
        foreach ($row as $k => $v) {
          if($k == 'enddate') $v = strtotime($v); //generate timestamp here, not in template, noRiddle
            $s->assign($k, $v);
        }
        $s->assign('cs_timenow', time()); //added present time here instead of using $smarty.now in template, noRiddle
        $str .= $s->fetch(CURRENT_TEMPLATE . '/module/customer_notices/' . $row['template']);
        if ($row['template'] == 'newsletter.html') $_SESSION['cs_popup'] = 'popup'; // p3e 20180302 PopUps nur einmal pro Session
      }
    }

    $smarty->assign('CUSTOMER_NOTICES', $str);
  } // end of static method run()
} // end of class CustomerNoticesManager
?>