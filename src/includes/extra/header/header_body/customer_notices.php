<?php
// BOF - Timo Paul (mail[at]timopaul[dot]biz) - 2014-06-22 - customersNotice
$customerNoticesManagerFile = DIR_FS_EXTERNAL . 'customer_notices/classes/CustomerNoticesManager.class.php';
if (!class_exists('CustomerNoticesManager') && file_exists($customerNoticesManagerFile)) {
  require_once $customerNoticesManagerFile;
  CustomerNoticesManager::run();
}
// EOF - Timo Paul (mail[at]timopaul[dot]biz) - 2014-06-22 - customersNotice
?>