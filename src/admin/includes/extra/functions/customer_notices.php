<?php

if (!function_exists('formatCustomerNoticeDate')) {
	function formatCustomerNoticeDate(string $datetime): string
	{
		if (isCustomerNoticeEmptyDate($datetime)) {
			return '-';
		}

		return date('d.m.Y H:i:s', strtotime($datetime));
	}
}

if (!function_exists('getCustomerNoticeEmptyStartDate')) {
	function getCustomerNoticeEmptyStartDate(): string
	{
		return '1000-01-01 00:00:00';
	}
}

if (!function_exists('getCustomerNoticeEmptyEndDate')) {
	function getCustomerNoticeEmptyEndDate(): string
	{
		return '9999-12-31 23:59:59';
	}
}

if (!function_exists('isCustomerNoticeEmptyDate')) {
	function isCustomerNoticeEmptyDate(string $datetime): bool
	{
		return in_array($datetime, array(getCustomerNoticeEmptyStartDate(), getCustomerNoticeEmptyEndDate()), true);
	}
}

if (!function_exists('getCustomerNoticeLocale')) {
	function getCustomerNoticeLocale(): string
	{
		static $locale = null;

		if ($locale !== null) {
			return $locale;
		}

		if (defined('DATE_LOCALE') && DATE_LOCALE !== '') {
			$locale = (string) DATE_LOCALE;
			return $locale;
		}

		$languageCode = isset($_SESSION['language_code']) ? (string) $_SESSION['language_code'] : 'en';
		$languageCode = str_replace('-', '_', strtolower($languageCode));

		$localeMap = array(
			'de' => 'de_DE',
			'en' => 'en_US',
		);

		$locale = $localeMap[$languageCode] ?? $languageCode;

		return $locale;
	}
}

if (!function_exists('getCustomerNoticeCountryDisplayName')) {
	function getCustomerNoticeCountryDisplayName(array $countryData): string
	{
		$fallbackName = isset($countryData['countries_name']) ? (string) $countryData['countries_name'] : '';
		$countryCode = isset($countryData['countries_iso_code_2']) ? strtoupper(trim((string) $countryData['countries_iso_code_2'])) : '';

		if ($countryCode === '' || !class_exists('Locale')) {
			return $fallbackName;
		}

		$localizedName = Locale::getDisplayRegion('-' . $countryCode, getCustomerNoticeLocale());

		if (!is_string($localizedName) || $localizedName === '' || strtoupper($localizedName) === $countryCode) {
			return $fallbackName;
		}

		return $localizedName;
	}
}

if (!function_exists('getCustomerNoticeCountryNameById')) {
	function getCustomerNoticeCountryNameById(int $countryId): string
	{
		static $countriesById = null;

		if ($countriesById === null) {
			$countriesById = array();
			foreach (xtc_get_countriesList() as $countryData) {
				$countriesById[(int) $countryData['countries_id']] = $countryData;
			}
		}

		if (isset($countriesById[$countryId])) {
			return getCustomerNoticeCountryDisplayName($countriesById[$countryId]);
		}

		return xtc_get_country_name($countryId);
	}
}

if (!function_exists('getCustomerNoticeCustomerLabel')) {
	function getCustomerNoticeCustomerLabel(array $customer): string
	{
		$customerId = isset($customer['customers_id']) ? (int) $customer['customers_id'] : 0;
		$fullName = trim(
			(isset($customer['customers_firstname']) ? (string) $customer['customers_firstname'] : '') . ' ' .
			(isset($customer['customers_lastname']) ? (string) $customer['customers_lastname'] : '')
		);
		$email = isset($customer['customers_email_address']) ? trim((string) $customer['customers_email_address']) : '';

		$labelParts = array();

		if ($fullName !== '') {
			$labelParts[] = $fullName;
		}

		if ($email !== '') {
			$labelParts[] = '<' . $email . '>';
		}

		if (empty($labelParts)) {
			$labelParts[] = 'ID ' . $customerId;
		} else {
			$labelParts[] = '[ID ' . $customerId . ']';
		}

		return implode(' ', $labelParts);
	}
}

if (!function_exists('getCustomerNoticeCustomerById')) {
	function getCustomerNoticeCustomerById(int $customerId): array
	{
		static $customers = array();

		if ($customerId <= 0) {
			return array();
		}

		if (!isset($customers[$customerId])) {
			$customers[$customerId] = array();
			$customerQuery = xtc_db_query(
				'SELECT customers_id, customers_firstname, customers_lastname, customers_email_address '
				. 'FROM ' . TABLE_CUSTOMERS . ' '
				. 'WHERE customers_id = ' . $customerId . ' '
				. 'LIMIT 1'
			);

			if ($customerData = xtc_db_fetch_array($customerQuery)) {
				$customers[$customerId] = $customerData;
			}
		}

		return $customers[$customerId];
	}
}

if (!function_exists('getCustomerNoticeCustomerAdminDisplay')) {
	function getCustomerNoticeCustomerAdminDisplay(int $customerId): string
	{
		if ($customerId <= 0) {
			return '';
		}

		$customerData = getCustomerNoticeCustomerById($customerId);

		if (empty($customerData)) {
			return 'ID ' . $customerId;
		}

		$customerName = trim(
			(isset($customerData['customers_firstname']) ? (string) $customerData['customers_firstname'] : '') . ' ' .
			(isset($customerData['customers_lastname']) ? (string) $customerData['customers_lastname'] : '')
		);

		if ($customerName === '') {
			return 'ID ' . $customerId;
		}

		return $customerName . ' [ID ' . $customerId . ']';
	}
}

if (!function_exists('getCustomerNoticeCustomerSearchResults')) {
	function getCustomerNoticeCustomerSearchResults(string $term): array
	{
		$term = trim($term);

		if (mb_strlen($term) < 2) {
			return array();
		}

		$escapedTerm = xtc_db_input($term);
		$conditions = array(
			"customers_firstname LIKE '%" . $escapedTerm . "%'",
			"customers_lastname LIKE '%" . $escapedTerm . "%'",
			"customers_email_address LIKE '%" . $escapedTerm . "%'",
			"CONCAT(customers_firstname, ' ', customers_lastname) LIKE '%" . $escapedTerm . "%'",
		);

		if (ctype_digit($term)) {
			$conditions[] = 'customers_id = ' . (int) $term;
		}

		$results = array();
		$customersQuery = xtc_db_query(
			'SELECT customers_id, customers_firstname, customers_lastname, customers_email_address '
			. 'FROM ' . TABLE_CUSTOMERS . ' '
			. 'WHERE ' . implode(' OR ', $conditions) . ' '
			. 'ORDER BY customers_lastname ASC, customers_firstname ASC '
			. 'LIMIT 25'
		);

		while ($customerData = xtc_db_fetch_array($customersQuery)) {
			$results[] = array(
				'id' => (int) $customerData['customers_id'],
				'text' => getCustomerNoticeCustomerLabel($customerData),
			);
		}

		return $results;
	}
}
