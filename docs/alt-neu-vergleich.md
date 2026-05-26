# Vergleich: Ursprungsmodul vs. neues Modul

Stand: 25.05.2026
Version: 1.0.0

Verglichen wurden diese beiden Stände:

- Alt: ModifiedModuleLoaderClient/Modules/bx-codemaster/nr-customers-notice/nr_csn_reworked_0.2.4/src
- Neu: ModifiedModuleLoaderClient/Modules/bx-codemaster/nr-customers-notice/src

Wichtig: Das alte Ursprungsmodul enthält bereits die historischen Erweiterungen von noRiddle bis Version 0.2.4. Dieses Dokument beschreibt daher die Unterschiede zwischen diesem alten Stand und dem daraus weiterentwickelten neuen Modul, nicht die komplette Historie seit 2014.

## Kurzfazit

- Das neue Modul ist nicht nur eine umbenannte Kopie, sondern eine strukturell modernisierte Modulvariante.
- Innerhalb von src wurden 92 Dateien geändert, 1745 Zeilen eingefügt und 527 Zeilen entfernt.
- Der größte technische Schritt ist die saubere Migration von customers_notice nach customer_notices inklusive Dateinamen, Tabellen, Klassen, Konstanten, Template-Pfaden und Hook-Dateien.
- Der Admin-Einstieg wurde zusätzlich fachlich neu einsortiert: Der Menüpunkt liegt jetzt unter Kunden statt unter Hilfsprogramme.
- Dazu kommen echte funktionale Erweiterungen im Adminbereich: Select2-Kundensuche, AJAX-Backend, CSRF-sicherer Request-Fluss, bessere Länder-/Kundenauswahl und konsistentere Admin-Anzeige.
- Auch die Installation wurde modernisiert: weg von SQL-Datei und manuellen Einbauten, hin zu einem installierbaren Systemmodul mit automatischer Einrichtung.

## 1. Paket- und Auslieferungsstruktur

### Alter Stand

Der alte Modulordner nr_csn_reworked_0.2.4 bestand im Wesentlichen aus:

- changelog.txt
- customers_notice.sql
- install.txt
- src

Das ist der klassische Aufbau eines älteren Modified-Modulpakets mit separater SQL-Datei und manueller Installationsanleitung.

### Neuer Stand

Der neue Modulordner nr-customers-notice enthält zusätzlich eine moderne Paketstruktur:

- docs
- icon.png
- moduleinfo.json
- modulehash.json
- src
- den alten Referenzordner nr_csn_reworked_0.2.4 weiterhin im Repository

### Konkrete Verbesserungen

- Einführung von moduleinfo.json für MMLC-/Modul-Export-Metadaten.
- Einführung von modulehash.json zur dateibasierten Paketverfolgung.
- Auslagerung der Installationsdokumentation nach docs/install.txt.
- Vorbereitung für eine saubere paketierte Auslieferung statt reiner Dateiablage mit SQL-Skript.
- Ergänzung visueller Modulressourcen wie icon.png.

## 2. Durchgängige Umbenennung und Normalisierung

Eine der größten Änderungen ist die konsequente Umstellung des Moduls von customers_notice auf customer_notices.

### Betroffene Bereiche

- Admin-Datei: admin/customers_notice.php wurde zu admin/customer_notices.php
- Tabellenkonstanten: TABLE_CUSTOMERS_NOTICE und TABLE_CUSTOMERS_NOTICE_DESCRIPTION wurden zu TABLE_BX_CUSTOMER_NOTICES und TABLE_BX_CUSTOMER_NOTICES_DESCRIPTION
- Dateinamenkonstanten: FILENAME_CUSTOMERS_NOTICE wurde zu FILENAME_CUSTOMER_NOTICES
- Frontend-Klasse: CustomersNoticeManager wurde zu CustomerNoticesManager
- Klassenpfad: includes/external/customers_notice/... wurde zu includes/external/customer_notices/...
- Template-Pfade: templates/.../module/customers_notice wurde zu templates/.../module/customer_notices
- Frontend-Smarty-Variable: CUSTOMERS_NOTICE wurde zu CUSTOMER_NOTICES
- Admin-Menüdatei: customers_notice.php wurde durch customer_notices.php ersetzt
- Der Admin-Menüeintrag wurde zusätzlich aus der Box Hilfsprogramme in die Box Kunden verschoben.

### Warum das relevant ist

- Die neue Benennung ist konsistenter.
- Singular/Plural-Mischformen wurden bereinigt.
- Die Struktur ist jetzt klarer und besser wartbar.
- Die Pfade und Konstanten passen endlich zum tatsächlichen Modulnamen NR Customers Notice.

## 3. Installation und Modul-Lifecycle wurden modernisiert

### Alter Stand

Im alten Modul war die Installation stark manuell:

- SQL-Datei customers_notice.sql musste importiert werden.
- Die Tabellen wurden außerhalb des Moduls per SQL angelegt.
- Admin-Rechte mussten über das SQL-Skript vorbereitet werden.
- Countdown-Sprachvariablen mussten manuell in die Template-Sprachdateien eingetragen werden.
- Die CSS-Einbindung in general_bottom.css.php musste manuell ergänzt werden.

### Neuer Stand

Im neuen Modul übernimmt die Datei src/admin/includes/modules/system/customer_notices.php die Installation und Deinstallation direkt als Systemmodul.

### Neue Fähigkeiten des Systemmoduls

- Erstellt die Konfigurationsgruppe in der Modified-Konfiguration.
- Legt die Konfigurationswerte MODULE_CUSTOMER_NOTICES_STATUS, MODULE_CUSTOMER_NOTICES_VERSION und MODULE_CUSTOMER_NOTICES_CONFIG_ID an.
- Erweitert die Tabelle admin_access um die Spalte customer_notices.
- Aktiviert das Modulrecht initial für bestehende Administratoren.
- Erstellt die Tabellen customer_notices und customer_notices_description automatisch.
- Entfernt bei der Deinstallation die Konfigurationswerte und die Konfigurationsgruppe wieder.
- Entfernt die Admin-Rechtespalte bei der Deinstallation wieder.
- Löscht die Modultabellen bei der Deinstallation wieder.

### Automatisierte Sprachblock-Verwaltung

Das neue Systemmodul verwaltet zusätzlich die Countdown-Sprachvariablen automatisch:

- Einfügen der Sprachblöcke in lang_german.custom und lang_english.custom des aktiven Templates.
- Verwendung klarer Marker:
  - #BOC customer_notices countdown
  - #EOC customer_notices countdown
- Entfernen genau dieses Blocks bei der Deinstallation.

### Praktischer Effekt

- Kein separates SQL-Import-Skript mehr nötig.
- Weniger manuelle Installationsschritte.
- Weniger Fehlerquellen bei Updates und Neuinstallationen.
- Besser kompatibel mit einer MMLC-/Modul-Export-basierten Auslieferung.

## 4. Änderungen im Adminbereich

Die größte funktionale Weiterentwicklung liegt im Adminbereich.

### 4.1 Admin-Datei fachlich erweitert und bereinigt

Die neue Datei src/admin/customer_notices.php basiert auf dem alten Adminskript, wurde aber deutlich erweitert.

Wesentliche Änderungen:

- Einführung eines AJAX-Endpoints action=ajax_customer_search für die Kundensuche.
- Entkopplung von Hilfsfunktionen in eine eigene Funktionsdatei.
- Modernisierung der Admin-Oberfläche durch externe CSS- und JS-Hooks.
- Verbesserung der Anzeige- und Bearbeitungslogik für customers_id.
- Bessere Darstellung des Override-Verhaltens von Kundengruppen und Ländern.

### 4.2 Hilfsfunktionen ausgelagert

Neu hinzugekommen ist:

- src/admin/includes/extra/functions/customer_notices.php

Dorthin wurden unter anderem ausgelagert:

- formatCustomerNoticeDate
- getCustomerNoticeEmptyStartDate
- getCustomerNoticeEmptyEndDate
- isCustomerNoticeEmptyDate
- getCustomerNoticeLocale
- getCustomerNoticeCountryDisplayName
- getCustomerNoticeCountryNameById
- getCustomerNoticeCustomerLabel
- getCustomerNoticeCustomerById
- getCustomerNoticeCustomerAdminDisplay
- getCustomerNoticeCustomerSearchResults

### Vorteil der Auslagerung

- Weniger Logik direkt in der Admin-Hauptdatei.
- Wiederverwendbare, klar abgegrenzte Hilfsfunktionen.
- Bessere Lesbarkeit.
- Bessere Erweiterbarkeit für spätere Admin-Funktionen.

### 4.3 Select2-basierte Kundensuche statt bloßer ID-Eingabe

Die neue Admin-Version hat die Kundenwahl erheblich verbessert.

#### Alter Stand

- customers_id war funktional vorhanden, aber im Kern eine einfache numerische Zuordnung.
- Kein komfortables Suchfeld für Kundenname oder E-Mail.

#### Neuer Stand

- Kunden können per Select2 gesucht werden.
- Die Suche funktioniert über Vorname, Nachname, E-Mail, zusammengesetzten Namen und numerische Kunden-ID.
- Das Select2-Feld schreibt die ausgewählte Kunden-ID in ein echtes Hidden-Feld.
- Bereits gesetzte Kunden werden mit sprechendem Label angezeigt, nicht nur mit der nackten ID.
- Das Feld ist löschbar.

### 4.4 Neues AJAX-Backend für Kundensuche

Das neue Modul enthält einen echten JSON-Endpunkt:

- action=ajax_customer_search
- Rückgabeformat: results für Select2
- Anfrageauswertung über term aus dem Request

Die Suchlogik erzeugt Labels wie:

- Vorname Nachname
- optional E-Mail in spitzen Klammern
- zusätzlich [ID X]

### 4.5 CSRF-sichere Select2-Integration

Die neue Kundensuche wurde explizit an Modifieds CSRF-Mechanik angepasst.

Verbesserungen:

- Die Select2-Suche sendet per POST statt per GET.
- Der aktuelle CSRF-Tokenname und -wert werden dynamisch aus der Session in den AJAX-Request aufgenommen.
- Damit wird verhindert, dass ein GET-Request während offener Bearbeitung den CSRF-Status des Formulars verändert.

Das ist eine echte funktionale Härtung gegenüber einer einfachen, ungeschützten AJAX-Integration.

### 4.6 Verbesserte Darstellung von customers_id im Admin

Im neuen Modul wurde nicht nur die Auswahl verbessert, sondern auch die Anzeige.

Verbesserungen:

- In der Bearbeitung wird ein bereits ausgewählter Kunde mit sprechendem Label angezeigt.
- In der rechten Info-Box wird statt einer nackten ID nun der Kundenname mit [ID X] dargestellt.
- Falls kein Name ermittelbar ist, bleibt die ID als Fallback erhalten.

### 4.7 Korrekte Override-Semantik für Kundengruppen und Länder

Im alten Stand war customers_id zwar fachlich vorhanden, aber die Admin-Anzeige war nicht darauf optimiert.

Im neuen Modul gilt:

- Wenn customers_id gesetzt ist, werden Kundengruppen und Länder fachlich übersteuert.
- Die rechte Info-Box zeigt das jetzt explizit an.
- Dadurch wird im Backend sichtbar, dass diese Filter in diesem Fall nicht mehr wirksam sind.

Das reduziert Fehlinterpretationen bei der Pflege von Hinweisen erheblich.

### 4.8 Verbesserte Länder-Auswahl im Admin

Die Länder-Auswahl wurde im neuen Modul spürbar verbessert.

Neu hinzugekommen sind:

- Suchfeld zum Filtern der Länderliste.
- Link Alle sichtbaren wählen.
- Link Auswahl leeren.
- Verhalten, das bei Filterung selektierte Einträge sichtbar hält.
- Interaktive Mehrfachauswahl per Maus ohne sperrige Standard-Mehrfachselect-Bedienung.

### 4.9 Trennung von Markup, CSS und JavaScript im Admin

Im alten Modul gab es keine vergleichbare Hook-Struktur für die neue Admin-Oberfläche.

Im neuen Modul wurden eigene Hook-Dateien ergänzt:

- src/admin/includes/extra/css/customer_notices.php
- src/admin/includes/extra/javascript/customer_notices.php

Dadurch werden folgende Dinge sauber ausgelagert:

- DateTimePicker-Einbindung
- Langtab-Menü-Einbindung
- Select2-Einbindung
- Admin-spezifische Layout- und Formular-CSS
- Kundensuche, Länderfilter und Checkbox-Komfortfunktionen

### 4.10 Select2-Assets und Sprachdateien neu im Modul

Neu hinzugekommen sind:

- src/admin/includes/extra/javascript/select2.full.min.js
- src/admin/includes/extra/css/select2.min.css
- src/admin/includes/extra/javascript/i18n/*.js

Das ist eine klare funktionale Erweiterung, weil das Ursprungsmodul keine eigene Select2-Infrastruktur mitbrachte.

## 5. Sprachdateien und Textsystem

### Alter Stand

Die alten Admin-Sprachdateien lagen direkt unter:

- src/lang/german/admin/customers_notice.php
- src/lang/english/admin/customers_notice.php

### Neuer Stand

Die neuen Admin-Sprachdateien liegen im aktuellen Modified-Schema unter:

- src/lang/german/extra/admin/customer_notices.php
- src/lang/english/extra/admin/customer_notices.php

Zusätzlich wurden neue Systemmodul-Sprachdateien ergänzt:

- src/lang/german/modules/system/customer_notices.php
- src/lang/english/modules/system/customer_notices.php

### Konkrete Verbesserungen in den Sprachdateien

- Umstellung auf neue Konstantennamen mit konsistenter customer_notices-Benennung.
- Einführung neuer Texte für Select2:
  - TEXT_CUSTOMER_NOTICES_CUSTOMER_SEARCH_PLACEHOLDER
  - TEXT_CUSTOMER_NOTICES_MIN_2_CHARS
  - TEXT_CUSTOMER_NOTICES_SEARCHING
  - TEXT_CUSTOMER_NOTICES_NO_RESULTS
  - TEXT_CUSTOMER_NOTICES_LOADING_MORE
- Einführung von TEXT_CUSTOMER_NOTICES_OVERRIDDEN_BY_CUSTOMER_ID.
- Ergänzung von TEXT_COUNTRIES_FILTER, TEXT_COUNTRIES_SELECT_ALL und TEXT_COUNTRIES_CLEAR.
- Teilweise Umstellung von HTML-Entities auf echte UTF-8-Zeichen in den neuen Sprachdateien.

### Bedeutung

- Die Texte sind genauer auf die neue Admin-Oberfläche abgestimmt.
- Kollisionen mit generischen Konstantennamen werden vermieden.
- Die neue Struktur passt zur heutigen Modified-Konvention extra/admin.

## 6. Frontend-Integration und Runtime-Verhalten

### 6.1 Frontend-Hook modernisiert

Die Frontend-Ausführung wurde auf die neue customer_notices-Struktur migriert.

Alter Pfad:

- src/includes/extra/header/header_body/customers_notice.php

Neuer Pfad:

- src/includes/extra/header/header_body/customer_notices.php

Zusätzlich neu:

- src/includes/extra/header/header_head/customer_notices.php

### Neuer Nutzen von header_head

Die neue header_head-Datei bindet die Frontend-CSS automatisch ein, wenn das Modul aktiv ist:

- templates/CURRENT_TEMPLATE/css/customer_notices.css

Dadurch entfällt der frühere manuelle Eingriff in general_bottom.css.php.

### 6.2 Frontend-Manager umbenannt und angepasst

Alter Stand:

- includes/external/customers_notice/classes/CustomersNoticeManager.class.php

Neuer Stand:

- includes/external/customer_notices/classes/CustomerNoticesManager.class.php

### Inhaltliche Änderungen im Frontend-Manager

- Umstellung auf die neuen Tabellenkonstanten.
- Umstellung auf die neuen Template- und Klassenpfade.
- Umstellung auf die neuen Null-/Grenzdatumswerte.
- Umstellung auf CUSTOMER_NOTICES als Smarty-Zuweisung.

### Datumslogik geändert

Im alten Stand wurden als Leerwerte verwendet:

- 0000-00-00 00:00:00

Im neuen Stand werden stattdessen verwendet:

- 1000-01-01 00:00:00 als leerer Startwert
- 9999-12-31 23:59:59 als leerer Endwert

### Vorteil

- Robuster gegenüber moderneren MySQL-/MariaDB-Konfigurationen.
- Vermeidet problematische Zero-Dates.
- Besser kompatibel mit strengerem SQL-Mode.

## 7. Templates und Styles im Frontend

### Alte Struktur

- templates/YOUR_TEMPLATE/css/customers_notice.css
- templates/YOUR_TEMPLATE/module/customers_notice/default.html
- templates/YOUR_TEMPLATE/module/customers_notice/newsletter.html
- templates/YOUR_TEMPLATE/module/customers_notice/countdown.html

### Neue Struktur

- templates/tpl_modified_nova/css/customer_notices.css
- templates/tpl_modified_nova/module/customer_notices/default.html
- templates/tpl_modified_nova/module/customer_notices/newsletter.html
- templates/tpl_modified_nova/module/customer_notices/countdown.html

### Konkrete Verbesserungen

- Konsistente customer_notices-Benennung.
- Integration in eine konkrete Template-Struktur statt Platzhalter YOUR_TEMPLATE.
- Neues countdown.html im neuen Zielpfad.
- CSS-Datei ebenfalls umbenannt und angepasst.

### Praktischer Effekt

- Die Beispielintegration ist konkreter und näher an einer realen Modified-Installation.
- Template- und CSS-Pfade passen zur neuen Hook-Logik.

## 8. Menü-, Hook- und Autoload-Integration

### Neu hinzugekommen

- src/admin/includes/extra/menu/customer_notices.php
- src/admin/includes/extra/filenames/customer_notices.php
- src/includes/extra/filenames/customer_notices.php
- src/includes/extra/database_tables/customer_notices.php
- src/includes/extra/wysiwyg/customer_notices.php

### Menüverschiebung im Admin

Der Menüeintrag wurde im neuen Modul nicht nur umbenannt, sondern auch in eine fachlich passendere Admin-Box verschoben.

- Alter Stand: Einordnung unter Hilfsprogramme
- Neuer Stand: Einordnung unter Kunden

In der aktuellen Menüdatei wird das über BOX_HEADING_CUSTOMERS umgesetzt. Dadurch ist der Einstiegspunkt im Backend näher an seinem eigentlichen fachlichen Kontext verortet.

### Bedeutende Unterschiede zum alten Stand

- Die neue Struktur orientiert sich sauber an Modified-Hooks und Extra-Dateien.
- Admin- und Frontend-Autoloading sind klarer voneinander getrennt.
- Das Modul fügt sich sauberer in die Standardmechanik von Modified ein.
- Der Menüpunkt ist im Backend fachlich sinnvoller einsortiert und für Redakteure/Admins leichter auffindbar.

## 9. Dokumentation wurde überarbeitet

### Alter Stand

Die alte install.txt beschrieb:

- SQL-Import
- manuelle CSS-Einbindung
- manuelles Ergänzen der Countdown-Sprachvariablen

### Neuer Stand in docs/install.txt

Die neue Installationsdoku beschreibt:

- Kopieren der Dateien
- weiterhin den nötigen manuellen Einbau in index.html
- automatische Verwaltung der Countdown-Sprachvariablen durch das Systemmodul
- Wegfall des SQL-Imports als manueller Pflichtschritt
- Wegfall der manuellen CSS-Einbindung in general_bottom.css.php

### Dokumentationsverbesserung

- Die Anleitung passt zur neuen Modularchitektur.
- Die früheren manuellen Fehlerquellen wurden reduziert.
- Der Installationsablauf ist klarer und zeitgemäßer.

## 10. Wichtige Datei-Zuordnungen Alt -> Neu

| Alter Pfad | Neuer Pfad | Bedeutung |
| --- | --- | --- |
| src/admin/customers_notice.php | src/admin/customer_notices.php | zentrale Admin-Datei umbenannt und stark erweitert |
| src/includes/external/customers_notice/classes/CustomersNoticeManager.class.php | src/includes/external/customer_notices/classes/CustomerNoticesManager.class.php | Frontend-Manager umbenannt und angepasst |
| src/includes/extra/database_tables/customers_notice.php | src/includes/extra/database_tables/customer_notices.php | Tabellenkonstanten auf neuen Modulnamen umgestellt |
| src/includes/extra/filenames/customers_notice.php | src/includes/extra/filenames/customer_notices.php | Dateinamenkonstanten umgestellt |
| src/includes/extra/header/header_body/customers_notice.php | src/includes/extra/header/header_body/customer_notices.php | Frontend-Hook umgestellt |
| nicht vorhanden | src/includes/extra/header/header_head/customer_notices.php | automatische CSS-Einbindung neu |
| src/includes/extra/wysiwyg/customers_notice.php | src/includes/extra/wysiwyg/customer_notices.php | WYSIWYG-Hook umbenannt |
| alter Menüeintrag unter Hilfsprogramme | src/admin/includes/extra/menu/customer_notices.php unter Kunden | Menüpunkt umbenannt und fachlich neu einsortiert |
| src/lang/german/admin/customers_notice.php | src/lang/german/extra/admin/customer_notices.php | Admin-Sprachdatei in neue Struktur überführt |
| src/lang/english/admin/customers_notice.php | src/lang/english/extra/admin/customer_notices.php | Admin-Sprachdatei in neue Struktur überführt |
| nicht vorhanden | src/lang/german/modules/system/customer_notices.php | Systemmodul-Texte neu |
| nicht vorhanden | src/lang/english/modules/system/customer_notices.php | Systemmodul-Texte neu |
| src/templates/YOUR_TEMPLATE/css/customers_notice.css | src/templates/tpl_modified_nova/css/customer_notices.css | Frontend-CSS umbenannt und konkretisiert |
| src/templates/YOUR_TEMPLATE/module/customers_notice/default.html | src/templates/tpl_modified_nova/module/customer_notices/default.html | Template-Pfad angepasst |
| src/templates/YOUR_TEMPLATE/module/customers_notice/newsletter.html | src/templates/tpl_modified_nova/module/customer_notices/newsletter.html | Template-Pfad angepasst |
| src/templates/YOUR_TEMPLATE/module/customers_notice/countdown.html | src/templates/tpl_modified_nova/module/customer_notices/countdown.html | Template neu angelegt bzw. neu im Zielpfad übernommen |
| nicht vorhanden | src/admin/includes/extra/javascript/customer_notices.php | neue Admin-JS-Steuerung |
| nicht vorhanden | src/admin/includes/extra/css/customer_notices.php | neue Admin-CSS-Steuerung |
| nicht vorhanden | src/admin/includes/extra/functions/customer_notices.php | ausgelagerte Hilfsfunktionen |
| nicht vorhanden | src/admin/includes/modules/system/customer_notices.php | vollwertiges Systemmodul neu |

## 11. Zusammenfassung der wichtigsten Verbesserungen

Die wichtigsten tatsächlichen Verbesserungen des neuen Moduls gegenüber dem alten Ursprungsstand sind:

- vollständige technische Migration auf customer_notices statt customers_notice
- modernes installierbares Systemmodul statt SQL-Datei und vieler manueller Schritte
- automatische Anlage und Entfernung der Countdown-Sprachvariablen
- automatische Frontend-CSS-Einbindung per Hook
- neue Admin-Asset-Struktur für CSS und JavaScript
- Select2-Kundensuche mit AJAX und CSRF-sicherem POST-Handling
- bessere Anzeige und Pflege von customers_id
- fachlich korrekte Override-Anzeige für Kundengruppen und Länder
- deutlich verbesserte Länder-Auswahl mit Filter- und Massenaktionen
- Auslagerung der Hilfsfunktionen aus der Admin-Hauptdatei
- modernisierte Sprachdateistruktur und zusätzliche Systemmodul-Sprachdateien
- bessere Paketierbarkeit durch moduleinfo.json und modulehash.json

## 12. Gesamtbewertung

Das neue Modul ist eine echte Weiterentwicklung des alten Ursprungsmoduls und keine bloße Umbenennung.

Technisch wurde es in vier Richtungen verbessert:

- strukturierter: klarere Dateinamen, Pfade, Hooks und Hilfsfunktionen
- installierbarer: Systemmodul statt SQL-lastiger Handarbeit
- benutzerfreundlicher: bessere Admin-Bedienung, vor allem bei Kunden- und Länderwahl
- wartbarer: konsistentere Benennung, getrennte Assets, sauberere Integration in Modified

Gerade die Kombination aus Namensbereinigung, Installationsmodernisierung und echter Admin-Funktionsverbesserung macht den neuen Stand deutlich robuster als das alte Ursprungsmodul.