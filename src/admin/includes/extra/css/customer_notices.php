<?php 
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  $customerNoticesIsEdit = isset($action) && in_array($action, array('new', 'edit'));
  $customerNoticesUseLangTabs = $customerNoticesIsEdit && (!defined('USE_ADMIN_LANG_TABS') || USE_ADMIN_LANG_TABS != 'false');

  if (basename($_SERVER['PHP_SELF']) == 'customer_notices.php') {
?>
<link rel="stylesheet" href="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.css">
<?php if ($customerNoticesUseLangTabs) { ?>
<link rel="stylesheet" href="includes/lang_tabs_menu/lang_tabs_menu.css">
<?php } ?>
<style>
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

  .customer-notices-form-layout,
  .customer-notices-list-inner {
    width: 100%;
    border-collapse: collapse;
  }

  .customer-notices-main-table {
    width: 860px;
    border-collapse: separate;
    border-spacing: 2px;
  }

  .customer-notices-list-table {
    width: 100%;
    margin-top: 15px;
    border-collapse: collapse;
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

  .customer-notices-top {
    vertical-align: top;
  }

  .customer-notices-col-id,
  .customer-notices-col-position {
    width: 40px;
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

<?php echo isset($langtabsCss) ? $langtabsCss : ''; ?>
</style>
<link rel="stylesheet" href="includes/extra/css/select2.min.css">
<?php
  }
?>