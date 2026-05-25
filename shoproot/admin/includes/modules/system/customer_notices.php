<?php
/* -----------------------------------------------------------------------------------------
	$Id: admin/includes/modules/system/customer_notices.php 1000 2026-05-25 12:00:00Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
	class customer_notices {
	public string $code;
	public string $version;
	public string $title;
	public string $description;
	public int $sort_order;
	public bool $enabled;
	private bool $_check;
	public string $development_status; // 'p' = production ready, 'd' = in development

	public function __construct() {
	  $this->code        = 'customer_notices';
	  $this->version     = '0.3.0';
	  $this->title       = MODULE_CUSTOMER_NOTICES_TITLE;
	  $this->description = MODULE_CUSTOMER_NOTICES_DESC;
	  $this->sort_order  = defined('MODULE_CUSTOMER_NOTICES_SORT_ORDER') ? MODULE_CUSTOMER_NOTICES_SORT_ORDER : 0;
	  $this->enabled     = ((defined('MODULE_CUSTOMER_NOTICES_STATUS') && MODULE_CUSTOMER_NOTICES_STATUS == 'True') ? true : false);
		$this->development_status = 'd';
  }

	/**
     * Returns whether the module is installed.
     * @return bool
     * 
     * */
	public function check(): bool {
      if (!isset($this->_check)) {
				if (defined('MODULE_CUSTOMER_NOTICES_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
																				WHERE configuration_key = 'MODULE_CUSTOMER_NOTICES_STATUS'");
          $this->_check = (xtc_db_num_rows($check_query) > 0);
        }
      }
      return $this->_check;
    }

	  
	/**
	  * Actions performed when the user clicks the install button.
	  *
	  * @return void
	  */
	public function install(): void {
	  $freeId_query = xtc_db_query("SELECT MIN(configuration_group_id+1) AS id 
			                          					FROM ".TABLE_CONFIGURATION_GROUP." 
									  						 WHERE (configuration_group_id+1) NOT IN 
															    (SELECT configuration_group_id FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id IS NOT NULL);");
	  $freeId = xtc_db_fetch_array($freeId_query);

 	  $freeSort_query = xtc_db_query("SELECT MIN(sort_order+1) AS sort_order 
	                                          FROM ".TABLE_CONFIGURATION_GROUP." 
                                         	 WHERE (sort_order+1) NOT IN (SELECT sort_order FROM ".TABLE_CONFIGURATION_GROUP." WHERE sort_order IS NOT NULL)");
																	 
		$freeSort = xtc_db_fetch_array($freeSort_query);

	  xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD ".$this->code." INTEGER(1) DEFAULT 0");
	  xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$this->code." = 1");
 
		  
	  xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION_GROUP." ( 
														configuration_group_id, 
														configuration_group_title, 
														configuration_group_description, 
														sort_order, 
														visible) 
													VALUES ( ".$freeId["id"].", 
														'NR Customers Notice Konfiguration', 
														'Modul einstellen und konfigurieren',
														".$freeSort["sort_order"].", 
														1)");

	  xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, 
																														 configuration_value, 
																														 configuration_group_id, 
																														 sort_order, 
																														 date_added, 
																														 use_function, 
																														 set_function) 
	                											VALUES ('MODULE_CUSTOMER_NOTICES_STATUS', 
																														'True', 
																															'".$freeId["id"]."', 
																														'1', 
																														NOW(), 
																														'', 
																														'xtc_cfg_select_option(array(\'True\', \'False\'), ')");
	  
	  xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, 
																														 configuration_value, 
																														 configuration_group_id, 
																														 sort_order, 
																														 date_added, 
																														 use_function, 
																														 set_function) 
													                VALUES ('MODULE_CUSTOMER_NOTICES_VERSION', 
																														'".$this->version."', 
																															'".$freeId["id"]."', 
																														'2', 
																														NOW(), 
																														'', 
																														'')");
	  
	  xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, 
																														 configuration_value, 
																														 configuration_group_id, 
																														 sort_order, 
																														 date_added, 
																														 use_function, 
																														 set_function) 
	                											VALUES ('MODULE_CUSTOMER_NOTICES_CONFIG_ID', 
																															'".$freeId["id"]."', 
																															'".$freeId["id"]."', 
																														'3', 
																														NOW(), 
																														'', 
																														'')");
	
		xtc_db_query ("CREATE TABLE IF NOT EXISTS ".TABLE_CUSTOMER_NOTICES." (
				customer_notice_id int(10) unsigned NOT NULL AUTO_INCREMENT,
				customers_id INT(11) DEFAULT NULL,
				status smallint(1) NOT NULL DEFAULT '0',
				position int(3) NOT NULL DEFAULT '1',
				startdate datetime DEFAULT '1000-01-01 00:00:00',
				enddate datetime DEFAULT '9999-12-31 23:59:59',
				template varchar(100) DEFAULT '',
				customers_status varchar(255) DEFAULT '',
				countries mediumtext DEFAULT NULL,
				pages text,
				PRIMARY KEY (customer_notice_id));"
		);
	
		xtc_db_query ("CREATE TABLE IF NOT EXISTS ".TABLE_CUSTOMER_NOTICES_DESCRIPTION." (
				customer_notice_id int(10) unsigned NOT NULL,
				languages_id int(10) unsigned NOT NULL,
				title varchar(255)DEFAULT NULL,
				description text,
				PRIMARY KEY (customer_notice_id, languages_id));"
		);

		$this->installCountdownLanguageVariables();
	}

	public function update(): void {}

	private function installCountdownLanguageVariables(): void {
		foreach ($this->getCountdownLanguageFiles() as $file => $block) {
			$contents = '';

			if (is_file($file)) {
				if (!is_readable($file) || !is_writable($file)) {
					continue;
				}

				$contents = file_get_contents($file);
				if ($contents === false || strpos($contents, $this->getCountdownLanguageMarkerStart()) !== false) {
					continue;
				}
			} elseif (!is_writable(dirname($file))) {
				continue;
			}

			$contents = rtrim((string)$contents);
			if ($contents !== '') {
				$contents .= PHP_EOL . PHP_EOL;
			}

			file_put_contents($file, $contents . $block . PHP_EOL, LOCK_EX);
		}
	}

	private function removeCountdownLanguageVariables(): void {
		$pattern = '/\R?' . preg_quote($this->getCountdownLanguageMarkerStart(), '/') . '\R.*?' . preg_quote($this->getCountdownLanguageMarkerEnd(), '/') . '\R?/s';

		foreach (array_keys($this->getCountdownLanguageFiles()) as $file) {
			if (!is_file($file) || !is_readable($file) || !is_writable($file)) {
				continue;
			}

			$contents = file_get_contents($file);
			if ($contents === false || strpos($contents, $this->getCountdownLanguageMarkerStart()) === false) {
				continue;
			}

			$updatedContents = preg_replace($pattern, PHP_EOL, $contents, 1);
			if ($updatedContents === null) {
				continue;
			}

			$updatedContents = trim($updatedContents);
			if ($updatedContents !== '') {
				$updatedContents .= PHP_EOL;
			}

			file_put_contents($file, $updatedContents, LOCK_EX);
		}
	}

	private function getCountdownLanguageFiles(): array {
		$template = $this->getCurrentTemplate();

		if ($template === '') {
			return array();
		}

		return array(
			DIR_FS_CATALOG . 'templates/' . $template . '/lang/lang_german.custom' => $this->getCountdownLanguageBlock('german'),
			DIR_FS_CATALOG . 'templates/' . $template . '/lang/lang_english.custom' => $this->getCountdownLanguageBlock('english'),
		);
	}

	private function getCurrentTemplate(): string {
		if (defined('CURRENT_TEMPLATE') && CURRENT_TEMPLATE !== '') {
			return CURRENT_TEMPLATE;
		}

		$templateQuery = xtc_db_query("SELECT configuration_value
		                                FROM " . TABLE_CONFIGURATION . "
		                               WHERE configuration_key = 'CURRENT_TEMPLATE'
		                               LIMIT 1");

		if (xtc_db_num_rows($templateQuery) === 0) {
			return '';
		}

		$template = xtc_db_fetch_array($templateQuery);

		return isset($template['configuration_value']) ? (string)$template['configuration_value'] : '';
	}

	private function getCountdownLanguageBlock(string $language): string {
		$entries = array(
			'german' => array(
				"csn_days = 'Tage'",
				"csn_std = 'Stunden'",
				"csn_min = 'Minuten'",
				"csn_sec = 'Sekunden'",
			),
			'english' => array(
				"csn_days = 'days'",
				"csn_std = 'hours'",
				"csn_min = 'minutes'",
				"csn_sec = 'seconds'",
			),
		);

		if (!isset($entries[$language])) {
			return '';
		}

		return $this->getCountdownLanguageMarkerStart()
			. PHP_EOL
			. implode(PHP_EOL, $entries[$language])
			. PHP_EOL
			. $this->getCountdownLanguageMarkerEnd();
	}

	private function getCountdownLanguageMarkerStart(): string {
		return '#BOC customer_notices countdown';
	}

	private function getCountdownLanguageMarkerEnd(): string {
		return '#EOC customer_notices countdown';
	}
	  
	/**
	  * Actions performed when the user clicks the uninstall button.
	  *
	  * @return void
	  */
	  
	public function remove(): void {
		$this->removeCountdownLanguageVariables();

	  xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key in ('".implode("', '", $this->keys())."')");
		xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_title = 'NR Customers Notice Konfiguration'");
	  xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP ".$this->code);
	  xtc_db_query("DROP TABLE IF EXISTS ".TABLE_CUSTOMER_NOTICES);
	  xtc_db_query("DROP TABLE IF EXISTS ".TABLE_CUSTOMER_NOTICES_DESCRIPTION);
  }

	public function process(): void { }
	  
	/**
	  * Additional HTML to show during module configuration.
	  *
	  * @return array
	  */
	  
	public function display() {
		return array('text' => '<div style="text-align: center;">'.xtc_button(BUTTON_SAVE).xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set='.$_GET['set'].'&module='.$this->code))."</div>");
	}

	/**
	  * Configuration keys used by the module. Used when installing and removing the module.
	  *
	  * @return array
	  */
	public function keys(): array {
		$keys = array(
			'MODULE_CUSTOMER_NOTICES_STATUS',
			'MODULE_CUSTOMER_NOTICES_CONFIG_ID',
			'MODULE_CUSTOMER_NOTICES_VERSION',
			);
		return $keys;
  }	  
}
  