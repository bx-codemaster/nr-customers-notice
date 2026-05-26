<?php
//Version 1.0.0

define('HEADING_BX_TITLE', 'BXCustomer Notices');
define('HEADING_BX_SUBTITLE', 'Time-controlled notices for all customers, selected customer groups, or individual customers.');
define('HEADING_BX_SUBTITLE_NEW_NOTICE', 'Create new notice');
define('HEADING_BX_SUBTITLE_EDIT_NOTICE', 'Edit notice "%s"');
define('HEADING_BX_TITLE_SEARCH', 'Search');
define('HEADING_BX_TITLE_STATUS', 'Status');
define('HEADING_BX_BOX_TITLE_DEFAULT', 'Notice "%s"');
define('HEADING_BX_BOX_TITLE_DELETE', 'Delete notice');

define('BUTTON_CREATE_NOTICE', 'Create new notice');
define('BUTTON_EDIT_NOTICE', 'Edit notice');
define('BUTTON_DELETE_NOTICE', 'Delete notice');
define('BUTTON_DELETE_NOTICE_CONFIRMATION', 'Confirm deletion!');

define('LABEL_TXT_TITLE', 'Title:');
define('LABEL_TXT_DESCRIPTION', 'Text:');
define('LABEL_TXT_STATUS', 'Notice active?');
define('LABEL_TXT_POSITION', 'Position:');
define('LABEL_TXT_CUSTOMERS_ID', 'Customer:'); //for new feature "restrict to customers_id", 01-2022, noRiddle
define('LABEL_TXT_STARTDATE', 'From:');
define('LABEL_TXT_ENDDATE', 'Until:');
define('LABEL_TXT_TEMPLATE', 'Template:');
define('LABEL_TXT_TEMPLATE_HINT', '(The template newsletter.html will be displayed as a popup if the customer is not subscribed to the newsletter.)');
define('LABEL_TXT_CUSTOMERS_GROUP', 'Customer group:');
define('LABEL_TXT_CUSTOMERS_GROUPS', 'Customer groups:<br>');
define('LABEL_TXT_PAGES', 'Pages:');
define('LABEL_TXT_COUNTRIES', 'Restrict notice to customer country:<br>'); //for new feature "restrict to customer country", 10-2021, noRiddle

define('TABLE_HEADING_TXT_ID', 'ID');
define('TABLE_HEADING_TXT_TITLE', 'Title');
define('TABLE_HEADING_TXT_STATUS', 'Status');
define('TABLE_HEADING_TXT_POSITION', 'Pos.');
define('TABLE_HEADING_TXT_STARTDATE', 'Start');
define('TABLE_HEADING_TXT_ENDDATE', 'End');
define('TABLE_HEADING_TXT_TEMPLATE', 'Template');
define('TABLE_HEADING_TXT_CUSTOMERS_STATUS', 'Customer groups');
define('TABLE_HEADING_TXT_PAGES', 'Pages');
define('TABLE_HEADING_TXT_COUNTRIES', 'Countries'); //for new feature "restrict to customer country", 10-2021, noRiddle

define('ERROR_MISSING_TITLE', 'Please add a title to your notice.');
define('ERROR_MISSING_DESCRIPTION', 'Please add some text for the notice.');
define('ERROR_INVALID_STARTDATE', 'The start date is invalid. Please observe the format.');
define('ERROR_INVALID_ENDDATE', 'The end date is invalid. Please observe the format.');

define('DATETIME_FORMAT', 'YYYY-MM-DD HH:MM'); //took of seconds, we use datetimepicker without seconds, noRiddle

define('TEXT_ACTIVE', 'active');
define('TEXT_INACTIVE', 'inactive');
define('TEXT_DELETE_NOTICE_CONFIRM', 'Are you sure you want to delete notice "%s"?');
define('TEXT_NO_CUSTOMER_NOTICES', 'No customer notices available.');
define('TEXT_CUSTOMER_NOTICES_OVERRIDDEN_BY_CUSTOMER_ID', '- overridden by customers_id');
define('TEXT_ALL', 'all');
define('TEXT_OPTIONAL', 'optional');
define('TEXT_COUNTRIES_FILTER', 'Filter countries');
define('TEXT_COUNTRIES_SELECT_ALL', 'Select all visible');
define('TEXT_COUNTRIES_CLEAR', 'Clear selection');
define('TEXT_EXPL_CUSTOMERS_ID', 'If a customer is selected here, the restrictions for "Customer groups" and "Customer country" below will not apply.'); //for new feature "restrict to customers_id", 01-2022, noRiddle
define('TEXT_CUSTOMER_NOTICES_CUSTOMER_SEARCH_PLACEHOLDER', 'Search customers');
define('TEXT_CUSTOMER_NOTICES_MIN_2_CHARS', 'Please enter at least 2 characters.');
define('TEXT_CUSTOMER_NOTICES_SEARCHING', 'Searching ...');
define('TEXT_CUSTOMER_NOTICES_NO_RESULTS', 'No customers found.');
define('TEXT_CUSTOMER_NOTICES_LOADING_MORE', 'Loading more results ...');
define('TEXT_SEARCH_FILTERS', 'Search filters:');

define('FIELD_VALUE_PAGES_INDEX', 'home page');
define('FIELD_VALUE_PAGES_CATEGORY', 'category');
define('FIELD_VALUE_PAGES_PRODUCT_INFO', 'product details');
define('FIELD_VALUE_PAGES_SHOP_CONTENT', 'content pages');
define('FIELD_VALUE_PAGES_SHOPPING_CART', 'shopping cart');
define('FIELD_VALUE_PAGES_ACCOUNT', 'account area');
define('FIELD_VALUE_PAGES_CHECKOUT', 'checkout area');
define('FIELD_VALUE_SEL_COUNTRY', 'Select country'); //for new feature "restrict to customer country", 10-2021, noRiddle
