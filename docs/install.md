# Installation des Moduls Customer Notices

## Historie

- Original Extension by TimoPaul
- Reworked by noRiddle, 03-2020, Version nr_csn_reworked_0.2
- Ergänzung einer Query-Bedingung und data-nosnippet in Template-Dateien, Version 0.2.1, 02-2021, noRiddle
- Möglichkeit ergänzt, Hinweise nach Kundenland zu filtern, Version 0.2.2, 10-2021, noRiddle
- Möglichkeit ergänzt, Hinweise nach customers_id zu filtern, Version 0.2.3, 01-2022, noRiddle
- Warnings und Notices bereinigt, Version 0.2.4, 01-2022, noRiddle
- Überarbeitete benax-Version 0.3.0, 05-2026

## Zielsystem

- Modified Shop 3.+
- Template tpl_modified_nova

## Installation

1. Backup von Datenbank und Shop erstellen.

2. Den vollständigen Inhalt des Ordners src in den Shoproot Ihres Servers kopieren. Es werden keine Dateien überschrieben.

   Hinweis:
   Den Ordner admin manuell zuweisen, falls der Name des Adminverzeichnisses geändert wurde.

3. Den Inhalt des Ordners templates gemäß Ordnerstruktur in das eigene Template kopieren.

4. An der gewünschten Stelle in der Template-Datei /templates/[DEIN_TEMPLATE]/index.html folgenden Block einfügen.

   Kommentar noRiddle:
   Am besten jeweils über `{if isset($main_content)}{$main_content}{/if}`, da dieser Block mehrfach vorkommen kann.

   ```smarty
   {* BOF - Timo Paul (mail[at]timopaul[dot]biz) - 2014-06-22 - customerNotices *}
   {if isset($CUSTOMER_NOTICES)}{$CUSTOMER_NOTICES}{/if}
   {* EOF - Timo Paul (mail[at]timopaul[dot]biz) - 2014-06-22 - customerNotices *}
   ```

   Ein Beispiel finden Sie in `/docs/templates/tpl_modified_nova/index.html`.

5. Die Countdown-Sprachvariablen werden bei der Installation automatisch in die Dateien

   - /templates/DEIN_TEMPLATE/lang/lang_german.custom
   - /templates/DEIN_TEMPLATE/lang/lang_english.custom

   des aktuell aktiven Templates eingefügt.

   Bei der Deinstallation wird genau dieser automatisch eingefügte Block wieder entfernt.

   Marker des automatisch verwalteten Blocks:

   ```text
   #BOC customer_notices countdown
   ...
   #EOC customer_notices countdown
   ```

   Hinweis:
   Bei bereits vorhandenen Installationen ohne Neuinstallation müssen die Sprachvariablen entweder einmalig manuell ergänzt oder das Modul einmal deinstalliert und erneut installiert werden.

6. Fertig.

## Beschreibung

Mit dieser Erweiterung besteht die Möglichkeit, Kunden im Shop zu informieren. Der Hinweis kann auf einen bestimmten Zeitraum begrenzt werden und oder nur für bestimmte Kundengruppen und oder nur auf bestimmten Seiten und oder nur für Kunden angezeigt werden, die noch kein Newsletterempfänger sind. Mit Hilfe verschiedener Templates können die Hinweise unterschiedlich dargestellt werden.