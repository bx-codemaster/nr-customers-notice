<?php
# customer_notices - Menüeintrag

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

switch ($_SESSION['language_code']) {
  case 'de':
    defined('MODULE_BX_CUSTOMER_NOTICES_TITLE') or define('MODULE_BX_CUSTOMER_NOTICES_TITLE','BX Kundenhinweise');
    break;
  default:
    defined('MODULE_BX_CUSTOMER_NOTICES_TITLE') or define('MODULE_BX_CUSTOMER_NOTICES_TITLE','BX Customer Notices');
    break;
}

$add_contents[BOX_HEADING_CUSTOMERS][] = array( 
    'admin_access_name' => 'bx_customer_notices', 
    'filename' 				  => 'bx_customer_notices.php', 
    'boxname' 				  => MODULE_BX_CUSTOMER_NOTICES_TITLE,
    'parameters' 			  => '', 
    'ssl' 					    => ''
  );
