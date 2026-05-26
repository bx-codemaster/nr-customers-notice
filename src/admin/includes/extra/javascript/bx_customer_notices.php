<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  $customerNoticesIsEdit      = isset($action) && in_array($action, array('new', 'edit'));
  $customerNoticesUseLangTabs = $customerNoticesIsEdit && (!defined('USE_ADMIN_LANG_TABS') || USE_ADMIN_LANG_TABS != 'false');

  if (basename($_SERVER['PHP_SELF']) == 'bx_customer_notices.php') {
?>
<script src="includes/extra/javascript/select2.full.min.js"></script>

<?php if ($customerNoticesUseLangTabs) { ?>
<script>document.documentElement.className += (document.documentElement.className ? ' js' : 'js');</script>
<script src="includes/lang_tabs_menu/lang_tabs_menu.js"></script>
<?php } ?>

<?php if ($customerNoticesIsEdit) { ?>
<script src="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.full.min.js"></script>
<?php } ?>

<script>
  $(function() {

    if ($.datetimepicker && $('#csn-startdate').length) {
      $.datetimepicker.setLocale('<?php echo $_SESSION["language_code"]; ?>');
      $('#csn-startdate').datetimepicker({
          dayOfWeekStart:1,
          format:'Y-m-d H:i',
          scrollInput:false
      });
      $('#csn-enddate').datetimepicker({
          dayOfWeekStart:1,
          format:'Y-m-d H:i',
          scrollInput:false
      });
    }

    var $cust_statuses = $('input[name="customers_status[]"]'),
        $pages = $('input[name="pages[]"]'),
        $customerSearch = $('#customer-notices-customer-search'),
        $customerId = $('#customer-notices-customer-id'),
        $countryFilter = $('#customer-notices-country-filter'),
        $countrySelect = $('#customer-notices-countries');

    if ($customerSearch.length && $.fn.select2) {
      var tokenName = '<?php echo isset($_SESSION['CSRFName']) ? addslashes((string) $_SESSION['CSRFName']) : ''; ?>';
      var tokenValue = '<?php echo isset($_SESSION['CSRFToken']) ? addslashes((string) $_SESSION['CSRFToken']) : ''; ?>';

      $customerSearch.select2({
        ajax: {
          url: '<?php echo basename($_SERVER['PHP_SELF']); ?>?action=ajax_customer_search',
          type: 'POST',
          dataType: 'json',
          delay: 300,
          data: function(params) {
            var requestData = {
              term: params.term || ''
            };

            if (tokenName !== '' && tokenValue !== '') {
              requestData[tokenName] = tokenValue;
            }

            return requestData;
          },
          processResults: function(data) {
            return {
              results: data.results || []
            };
          },
          cache: true
        },
        minimumInputLength: 2,
        placeholder: '<?php echo addslashes(TEXT_CUSTOMER_NOTICES_CUSTOMER_SEARCH_PLACEHOLDER); ?>',
        allowClear: true,
        width: '100%',
        language: {
          inputTooShort: function() {
            return '<?php echo addslashes(TEXT_CUSTOMER_NOTICES_MIN_2_CHARS); ?>';
          },
          searching: function() {
            return '<?php echo addslashes(TEXT_CUSTOMER_NOTICES_SEARCHING); ?>';
          },
          noResults: function() {
            return '<?php echo addslashes(TEXT_CUSTOMER_NOTICES_NO_RESULTS); ?>';
          },
          loadingMore: function() {
            return '<?php echo addslashes(TEXT_CUSTOMER_NOTICES_LOADING_MORE); ?>';
          }
        }
      });

      $customerSearch.on('select2:select.customerNotices', function(event) {
        $customerId.val(event.params.data.id || '');
      });

      $customerSearch.on('select2:clear.customerNotices change.customerNotices', function() {
        if (!$customerSearch.val()) {
          $customerId.val('');
        }
      });
    }

    function customerNoticesFilterCountries() {
      var filterValue = $.trim($countryFilter.val()).toLowerCase(),
          hasFilter = filterValue !== '',
          hasSelection = $countrySelect.find('option:selected').length > 0;

      $countrySelect.find('option').each(function() {
        var optionVisible;

        if (hasFilter) {
          optionVisible = $(this).text().toLowerCase().indexOf(filterValue) !== -1 || this.selected;
        } else if (hasSelection) {
          optionVisible = this.selected;
        } else {
          optionVisible = true;
        }

        this.hidden = !optionVisible;
      });
    }

    $('#chck-all-cst').click(function() {
        $cust_statuses.prop('checked', $(this).is(':checked') );
    });
    $('#chck-all-pgs').click(function() {
        $pages.prop('checked', $(this).is(':checked') );
    });

    if ($countryFilter.length && $countrySelect.length) {
      $countryFilter.on('input.customerNotices', customerNoticesFilterCountries);
      $countrySelect.on('mousedown.customerNotices', 'option', function(event) {
        event.preventDefault();
        this.selected = !this.selected;

        $countryFilter.val('');
        customerNoticesFilterCountries();

        window.setTimeout(function() {
          $countryFilter.trigger('focus');
        }, 0);

        $countrySelect.trigger('change');
      });
      $('#customer-notices-country-select-all').on('click.customerNotices', function(event) {
        event.preventDefault();
        customerNoticesFilterCountries();
        $countrySelect.find('option').filter(function() {
          return !this.hidden;
        }).prop('selected', true);
      });
      $('#customer-notices-country-clear').on('click.customerNotices', function(event) {
        event.preventDefault();
        $countryFilter.val('');
        $countrySelect.find('option').prop('selected', false).prop('hidden', false);
      });
      $countrySelect.on('change.customerNotices', customerNoticesFilterCountries);
      customerNoticesFilterCountries();
    }

  });
</script>
<?php
  }
?>