<?php
//Version 0.3.0

define('HEADING_TITLE', 'Kunden Hinweise');
define('HEADING_SUBTITLE', 'Zeitgesteuerte Hinweise, für alle, oder nur für einige Kunden-Gruppen, oder für einzelnen Kunden.');
define('HEADING_SUBTITLE_NEW_NOTICE', 'Neuen Hinweis erstellen');
define('HEADING_SUBTITLE_EDIT_NOTICE', 'Hinweis "%s" bearbeiten');
define('HEADING_TITLE_SEARCH', 'Suchen');
define('HEADING_TITLE_STATUS', 'Status');
define('HEADING_BOX_TITLE_DEFAULT', 'Hinweis "%s"');
define('HEADING_BOX_TITLE_DELETE', 'Hinweis löschen');

define('BUTTON_CREATE_NOTICE', 'Neuen Hinweis erstellen');
define('BUTTON_EDIT_NOTICE', 'Hinweis bearbeiten');
define('BUTTON_DELETE_NOTICE', 'Hinweis löschen');
define('BUTTON_DELETE_NOTICE_CONFIRMATION', 'Löschen bestätigen!');

define('LABEL_TXT_TITLE', 'Titel:');
define('LABEL_TXT_DESCRIPTION', 'Text:');
define('LABEL_TXT_STATUS', 'Hinweis aktiv ?');
define('LABEL_TXT_POSITION', 'Position:');
define('LABEL_TXT_CUSTOMERS_ID', 'Kunde:'); //for new feature "restrict to customers_id", 01-2022, noRiddle
define('TEXT_EXPL_CUSTOMERS_ID', 'Wenn hier ein Eintrag vorhanden ist greifen die Features "Kundengruppe" und "Land des Kunden" unten nicht !!'); //for new feature "restrict to customers_id", 01-2022, noRiddle
define('TEXT_CUSTOMER_NOTICES_CUSTOMER_SEARCH_PLACEHOLDER', 'Kunden suchen');
define('TEXT_CUSTOMER_NOTICES_MIN_2_CHARS', 'Bitte mindestens 2 Zeichen eingeben.');
define('TEXT_CUSTOMER_NOTICES_SEARCHING', 'Suche läuft ...');
define('TEXT_CUSTOMER_NOTICES_NO_RESULTS', 'Keine Kunden gefunden.');
define('TEXT_CUSTOMER_NOTICES_LOADING_MORE', 'Weitere Treffer werden geladen ...');
define('LABEL_TXT_STARTDATE', 'von:');
define('LABEL_TXT_ENDDATE', 'bis:');
define('LABEL_TXT_TEMPLATE', 'Template:');
define('LABEL_TXT_TEMPLATE_HINT', '(Das Template newsletter.html wird bei nicht aktiviertem Newsletter als PopUp angezeigt, wenn der Kunde noch kein Newsletterempfänger ist.)');
define('LABEL_TXT_CUSTOMERS_GROUP', 'Kundengruppe:');
define('LABEL_TXT_CUSTOMERS_GROUPS', 'Kundengruppen:<br>');
define('LABEL_TXT_PAGES', 'Seiten:');
define('LABEL_TXT_COUNTRIES', 'Anzeige begrenzen auf Land des Kunden:<br>'); //for new feature "restrict to customer country", 10-2021, noRiddle

define('TABLE_HEADING_TXT_ID', 'ID');
define('TABLE_HEADING_TXT_TITLE', 'Titel');
define('TABLE_HEADING_TXT_STATUS', 'Status');
define('TABLE_HEADING_TXT_POSITION', 'Pos.');
define('TABLE_HEADING_TXT_STARTDATE', 'Start');
define('TABLE_HEADING_TXT_ENDDATE', 'Ende');
define('TABLE_HEADING_TXT_TEMPLATE', 'Template');
define('TABLE_HEADING_TXT_CUSTOMERS_STATUS', 'Kundengruppen');
define('TABLE_HEADING_TXT_PAGES', 'Seiten');
define('TABLE_HEADING_TXT_COUNTRIES', 'Länder'); //for new feature "restrict to customer country", 10-2021, noRiddle

define('ERROR_MISSING_TITLE', 'Bitte gib deinem Hinweis eine Überschrift.');
define('ERROR_MISSING_DESCRIPTION', 'Bitte gib etwas Text für den Hinweis ein.');
define('ERROR_INVALID_STARTDATE', 'Das Start-Datum ist ungültig, bitte achte auf das Format.');
define('ERROR_INVALID_ENDDATE', 'Das End-Ddatum ist ungültig, bitte achte auf das Format.');

define('DATETIME_FORMAT', 'YYYY-MM-DD HH:MM'); //took of seconds, we use datetimepicker without seconds, noRiddle

define('TEXT_ACTIVE', 'aktiv');
define('TEXT_INACTIVE', 'inaktiv');
define('TEXT_DELETE_NOTICE_CONFIRM', 'Hinweis "%s" wirklich löschen?');
define('TEXT_NO_CUSTOMER_NOTICES', 'Keine Kunden-Hinweise vorhanden.');
define('TEXT_CUSTOMER_NOTICES_OVERRIDDEN_BY_CUSTOMER_ID', '- durch Kunde [customers_id] übersteuert');
define('TEXT_ALL', 'alle');
define('TEXT_OPTIONAL', 'optional');

define('FIELD_VALUE_PAGES_INDEX', 'Startseite');
define('FIELD_VALUE_PAGES_CATEGORY', 'Kategorie');
define('FIELD_VALUE_PAGES_PRODUCT_INFO', 'Produktdetails');
define('FIELD_VALUE_PAGES_SHOP_CONTENT', 'Content-Seiten');
define('FIELD_VALUE_PAGES_SHOPPING_CART', 'Warenkorb');
define('FIELD_VALUE_PAGES_ACCOUNT', 'Kontobereich');
define('FIELD_VALUE_PAGES_CHECKOUT', 'Checkoutbereich');
define('FIELD_VALUE_SEL_COUNTRY', 'Land wählen'); //for new feature "restrict to customer country", 10-2021, noRiddle
define('TEXT_COUNTRIES_FILTER', 'Länder filtern');
define('TEXT_COUNTRIES_SELECT_ALL', 'Alle sichtbaren wählen');
define('TEXT_COUNTRIES_CLEAR', 'Auswahl leeren');
