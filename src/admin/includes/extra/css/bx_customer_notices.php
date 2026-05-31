<?php 
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  $customerNoticesIsEdit = isset($action) && in_array($action, array('new', 'edit'));
  $customerNoticesUseLangTabs = $customerNoticesIsEdit && (!defined('USE_ADMIN_LANG_TABS') || USE_ADMIN_LANG_TABS != 'false');

  if (basename($_SERVER['PHP_SELF']) == 'bx_customer_notices.php') {
?>
<link rel="stylesheet" href="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.css">
<?php if ($customerNoticesUseLangTabs) { ?>
<link rel="stylesheet" href="includes/lang_tabs_menu/lang_tabs_menu.css">
<?php } ?>
<style>
  #headboard {
    display: flex; 
    flex-direction: row; 
    justify-content: flex-end;
    width: 100%;
    align-items: center; 
    background: #AF417E; 
    color: #ffffff; 
    border-radius: 4px; 
    margin-bottom: 10px; 
    padding: 4px 0 2px 0;
    line-height: 30px;
  }

  #headboard .main {
    margin: 5px 10px;
  }
  
  #headboard .SumoSelect {
    color: #000;
  }
  
  .select2-dropdown {
    font-family: Verdana, Arial, sans-serif;
    font-size: 12px;
  }

  .dataTableHeadingContent:first-child {
    border-left: 1px solid #aaa;
  }

  .dataTableContent:first-child {
    border-left: 1px solid #ddd;
  }
  .dataTableContent:last-child {
    border-right: 1px solid #ddd;
  }

 .tableConfig.bx-table-config-3cols .col-left {
    width: 30%;
    vertical-align: top;
    font-weight: normal;
  }

  .tableConfig.bx-table-config-3cols .col-middle {
    width: 30%;
    vertical-align: top;
  }
  .tableConfig.bx-table-config-3cols .col-right {
    width: 40%;
    vertical-align: top;
  }

  .tableConfig.bx-table-config-3cols h4 {
    display: flex; 
    align-items: baseline; 
    justify-content: flex-start; 
    gap: 8px; 
    margin: 0 0 12px;
  }

  .tableConfig.bx-table-config-3cols h4 > .badge {
    display: inline-block;
    font-size: 11px; 
    font-weight: 400;
    line-height: 1.2;
  }

  .dataTableContent {
    padding: 8px 2px;
  }
  .dataTableContent.vat {
    vertical-align: top !important;
  }
  small {
    font-size: 75%;
  }
  .customer-notices-form-shell {
    width: 100%;
    margin-top: 15px;
    border-collapse: collapse;
  }

  .customer-notices-form-layout {
    width: 100%;
    border-collapse: collapse;
  }

  .customer-notices-main-table {
    width: 860px;
    border-collapse: separate;
    border-spacing: 2px;
  }

  .customer-notices-langtabs {
    width: 860px;
    padding: 5px;
  }

  .customer-notices-third {
    width: 33.333%;
  }

  .customer-notices-actions {
    text-align: center;
  }

  .customer-notices-country-filter,
  .customer-notices-country-select {
    width: 100%;
    box-sizing: border-box;
  }

  .customer-notices-country-filter {
    display: block;
    padding-left: 8px;
    padding-right: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 2.0;
    margin-bottom: 6px;
    border-radius: 2px;
  }

  .customer-notices-country-links {
    margin-bottom: 8px;
    font-size: 11px;
  }

  .customer-notices-country-links a {
    text-decoration: none;
  }

  .customer-notices-country-select {
    min-height: 220px;
  }

  td.dataTableConfig.col-left a.button {
    text-decoration: none;
    font-size: 10px;
  } 

<?php echo isset($langtabsCss) ? $langtabsCss : ''; ?>
</style>
<link rel="stylesheet" href="includes/extra/css/select2.min.css">
<?php
  }
?>