<?php
/**
 * Customer notices runtime manager.
 *
 * Verantwortlich für:
 * - Aufbau des Laufzeitkontexts aus Session- und Seiteninformationen
 * - Ermittlung und Ausführung der Selektions-Query für aktive Notices
 * - Rendering der Notice-Templates und Übergabe an Smarty
 *
 * Contributors:
 * - Timo Paul
 * - noRiddle
 * - benax
 *
 * @copyright (c) 2014, Timo Paul Dienstleistungen
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 *            GNU General Public License (GPL), Version 2.0
 */

class CustomerNoticesManager {
  /**
   * Führt den kompletten Notice-Workflow aus und schreibt den Render-Output in Smarty.
   *
   * @return void
   */
  public static function run(): void {
    global $smarty, $category_depth;

    $context = self::buildContext($category_depth);
    $query   = xtc_db_query(self::buildQuery($context));
    
    $smarty->assign('CUSTOMER_NOTICES', self::renderNotices($query));
  } // end of static method run()

  /**
   * @param mixed $category_depth
   *
    * @return array{
    *   start_null_date: string,
    *   end_null_date: string,
    *   languages_id: int,
    *   script: string,
    *   customers_status_id: int,
    *   customer_country_id: int,
    *   customer_id: int|null,
    *   is_newsletter_recipient: bool,
    *   has_popup_session: bool
    * }
   */
  protected static function buildContext($category_depth): array {
    $customersStatusId = (int)($_SESSION['customers_status']['customers_status_id'] ?? DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
    $customerId        = (isset($_SESSION['customer_id']) && is_numeric($_SESSION['customer_id'])) ? (int)$_SESSION['customer_id'] : null;

    return array(
      'start_null_date'         => '1000-01-01 00:00:00',
      'end_null_date'           => '9999-12-31 23:59:59',
      'languages_id'            => isset($_SESSION['languages_id']) ? (int)$_SESSION['languages_id'] : 0,
      'script'                  => self::normalizeScriptName($category_depth),
      'customers_status_id'     => $customersStatusId,
      'customer_country_id'     => isset($_SESSION['customer_country_id']) ? (int)$_SESSION['customer_country_id'] : 0,
      'customer_id'             => $customerId,
      'is_newsletter_recipient' => self::isNewsletterRecipient($customersStatusId, $customerId),
      'has_popup_session'       => (isset($_SESSION['cs_popup']) && $_SESSION['cs_popup'] == 'popup')
    );
  }

  /**
   * @param mixed $category_depth
    *
    * @return string
   */
  protected static function normalizeScriptName($category_depth): string {
    $script = basename($_SERVER['SCRIPT_NAME']);
    $dotPos = strripos($script, '.');
    if ($dotPos !== false) {
      $script = substr($script, 0, $dotPos);
    }

    if ('index' == $script && xtc_not_null($category_depth) && 'top' != $category_depth) {
      $script = 'category';
    }
    if (preg_match('#^(account|address)_#', $script)) {
      $script = 'account';
    }
    if (preg_match('#^checkout_#', $script)) {
      $script = 'checkout';
    }

    return $script;
  }

  /**
   * @param int $customersStatusId
   * @param int|null $customerId
   *
   * @return bool
   */
  protected static function isNewsletterRecipient(int $customersStatusId, ?int $customerId): bool {
    $newsletterRecipient = true;

    if ($customersStatusId != (int)DEFAULT_CUSTOMERS_STATUS_ID_GUEST && $customerId !== null) {
      $check_mail_query = xtc_db_query("SELECT c.customers_id
                                          FROM " . TABLE_CUSTOMERS . " c
                                    INNER JOIN " . TABLE_NEWSLETTER_RECIPIENTS . " nr
                                            ON nr.customers_email_address = c.customers_email_address
                                           AND nr.mail_status = 1
                                         WHERE c.customers_id = " . (int)$customerId . "
                                         LIMIT 1");
      if (xtc_db_num_rows($check_mail_query) == 0) {
        $newsletterRecipient = false;
      }
    }

    return $newsletterRecipient;
  }

  /**
    * @param array{
    *   start_null_date: string,
    *   end_null_date: string,
    *   languages_id: int,
    *   script: string,
    *   customers_status_id: int,
    *   customer_country_id: int,
    *   customer_id: int|null,
    *   is_newsletter_recipient: bool,
    *   has_popup_session: bool
    * } $context
    *
    * @return string
   */
  protected static function buildQuery(array $context): string {
    $stmt = "SELECT cn.*, cnd.title, cnd.description
               FROM " . TABLE_BX_CUSTOMER_NOTICES . " AS cn
          LEFT JOIN " . TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION . " cnd
                 ON cn.customer_notice_id = cnd.customer_notice_id
                AND cnd.languages_id = " . $context['languages_id'] . "
              WHERE cn.status = 1";

    if ($context['has_popup_session'] || $context['is_newsletter_recipient']) {
      $stmt .= " AND cn.template <> 'newsletter.html'";
    }

    $countryFilterSql = "(IF(cn.countries = '' OR " . $context['customer_country_id'] . " = 0, 1 = 1, FIND_IN_SET(" . $context['customer_country_id'] . ", cn.countries) > 0))";
    $targetCustomerSql = ($context['customer_id'] !== null) ? (int)$context['customer_id'] : "'X'";

    $stmt .= " AND (cn.startdate = '" . $context['start_null_date'] . "' OR cn.startdate <= now())"
          .  " AND (cn.enddate = '" . $context['end_null_date'] . "' OR cn.enddate > now())"
          .  " AND (cn.pages = '' OR FIND_IN_SET('" . xtc_db_input($context['script']) . "', cn.pages) > 0)"
          .  " AND (IF(cn.customers_id IS NULL OR cn.customers_id = 0,
                      (cn.customers_status = '' OR FIND_IN_SET(" . $context['customers_status_id'] . ", cn.customers_status) > 0) AND " . $countryFilterSql . ",
                      cn.customers_id = " . $targetCustomerSql . "
                    ))"
          .  " ORDER BY position";

    return $stmt;
  }

  /**
   * @param mixed $query
   *
   * @return string
   */
  protected static function renderNotices($query): string {
    $str = '';
    if (xtc_db_num_rows($query) > 0) {
      $s = new Smarty();
      $s->caching = 0;

      while ($row = xtc_db_fetch_array($query)) {
        self::resetSmartyAssignments($s);
        $s->assign('language', $_SESSION['language'] ?? '');
        $s->assign('tpl_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/');
        $str .= self::renderNotice($row, $s);
      }
    }

    return $str;
  }

  /**
   * @param array<string, mixed> $row
   * @param Smarty $s
   *
   * @return string
   */
  protected static function renderNotice(array $row, Smarty $s): string {
    foreach ($row as $k => $v) {
      if ($k == 'enddate') {
        $v = strtotime($v);
      }
      $s->assign($k, $v);
    }

    $s->assign('cs_timenow', time());
    $output = $s->fetch(CURRENT_TEMPLATE . '/module/customer_notices/' . $row['template']);

    if (isset($row['template']) && $row['template'] == 'newsletter.html') {
      $_SESSION['cs_popup'] = 'popup';
    }

    return $output;
  }

  /**
   * @param Smarty $s
   *
   * @return void
   */
  protected static function resetSmartyAssignments(Smarty $s): void {
    if (method_exists($s, 'clearAllAssign')) {
      $s->clearAllAssign();
      return;
    }

    if (method_exists($s, 'clear_all_assign')) {
      $s->clear_all_assign();
    }
  }
} // end of class CustomerNoticesManager
?>