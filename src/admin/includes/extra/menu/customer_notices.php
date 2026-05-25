<?php
# customer_notices - Menüeintrag

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  //Sprachabhängiger Menüeintrag, kann für weitere Sprachen ergänzt werden
  switch ($_SESSION['language_code']) {
    case 'de':
      define('BOX_CUSTOMER_NOTICES','Kundenhinweise');
      break;
    case 'en':
      define('BOX_CUSTOMER_NOTICES','Customer Notices');
      break;  
    default:
      define('BOX_CUSTOMER_NOTICES','Customer Notices');
      break;
  }

  //BOX_HEADING_CUSTOMERS = Name der Box, in der der neue Menüeintrag erscheinen soll
  $add_contents[BOX_HEADING_CUSTOMERS][] = array( 
    'admin_access_name' => 'customer_notices',   //Eintrag fuer Adminrechte
    'filename' => 'customer_notices.php',        //Dateiname der neuen Admindatei
    'boxname' => BOX_CUSTOMER_NOTICES,     //Anzeigename im Menü
    'parameters' => '',                  //zusaetzliche Parameter z.B. 'set=export'
    'ssl' => ''                         //SSL oder NONSSL, kein Eintrag = NONSSL
    );
  
?>