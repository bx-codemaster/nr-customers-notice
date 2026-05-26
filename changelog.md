# Changelog

## v0.1 BETA, 22. June 2014

- [NEW] Administration
- [NEW] Display Messages from Templates on each Page

## v0.2 BETA, 23. June 2014

- [FIX] remove not used file from installation package
- [FIX] adjust installation SQL-Statement to use with diffrent charsets
- [ADD] Project Version controller to keep the latest one

## v0.3 BETA, 06. July 2014

- [FIX] replace Javascript displays the Countdown without errors
- [FIX] correct link target of the pointer on each row of notice administration
- [NEW] Page-Type account to display notice in Customers Account

## v0.4 BETA, 10. April 2015

- [FIX] set $tpl_path for notice Templates
- [FIX] set correct status value for editing one Notice
- [FIX] do not transform HTML-Tags from the Description Text
- [ADD] add update-Button to Save one Notice an keep in the Form

## v0.5 BETA, 05. March 2018

- [ADD] add Newsletter PopUp

## v nr_customers_notice_reworked_0.1 by noRiddle

- fixed some minor issues
- use modified standard objectInfo in /admin/customer_notices.php (fixed not showing info box on the right with first call of script)
- changed to datetimepicker instead of not used anymore spiffyCal
- fixed some potential mySQL injection dangers
- added one-click customer groups selction
- optimized and simplified query in /includes/external/customer_notices/classes/CustomerNoticesManager.class.php
- use configuration constant DEFAULT_CUSTOMERS_STATUS_ID_GUEST instead of hard coded 1 in /includes/external/customer_notices/classes/CustomerNoticesManager.class.php
- gave messages in frontend a container
- adapted CSS for frontend
- translated english language file (was in German before)
- make time appellations in countdown template multi language compatible

## v nr_customers_notice_reworked_0.2 by noRiddle

- korrigierte Anleitung

## v nr_customers_notice_reworked_0.2.1 by noRiddle

- Query auf u.U. nicht gesetzte $_SESSION['customer_id'] in Condition gesetzt
- data-nosnippet in Template Dateien hinzugefügt (soll auftauchen in den SERPS verhindern nach Google)

## v nr_customers_notice_reworked_0.2.2 by noRiddle

- added possibillity to filter by customers country
- fixed issues (e.g. no DB entry for new language in case new language is installed in shop)

## v nr_customers_notice_reworked_0.2.3 by noRiddle

- added feature to filter by customers_id
- fixed missing closing tags for small-tag

## v nr_customers_notice_reworked_0.2.4 by noRiddle

- fix warnings and notices

## v1.0.0 by benax

- migrated the module consistently from customers_notice to customer_notices across files, constants, classes, hooks and template paths
- converted the package to a modern MMLC-ready module structure with docs, moduleinfo.json, modulehash.json and module assets
- added an installable system module for automatic setup and removal of configuration, admin permissions and database tables
- automated installation and cleanup of countdown language variables in the active template language custom files
- added automatic frontend CSS loading via header hook instead of relying on manual CSS integration steps
- moved the admin menu entry from helper programs to the customers menu section
- refactored the admin area into dedicated extra CSS, JavaScript and helper function files
- added Select2-based customer search with AJAX backend, readable customer labels and clearable selection
- secured the customer AJAX search with CSRF-safe POST requests compatible with modified admin token handling
- improved admin usability for countries with filter field, select-all and clear actions
- improved customers_id handling and admin display, including visible override hints for customer groups and countries
- added dedicated admin and system language files for German and English, including new texts for customer search and override states
- replaced problematic zero-date handling with robust empty start and end sentinel dates for better compatibility