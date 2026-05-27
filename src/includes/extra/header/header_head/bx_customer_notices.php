<?php
if (defined('MODULE_BX_CUSTOMER_NOTICES_STATUS') && 'True' == MODULE_BX_CUSTOMER_NOTICES_STATUS) {
  echo '<link rel="stylesheet" type="text/css" href="' . DIR_WS_BASE . 'templates/' . CURRENT_TEMPLATE . '/css/customer_notices.css">' . PHP_EOL;
  echo '<link rel="stylesheet" type="text/css" href="' . DIR_WS_BASE . DIR_WS_EXTERNAL . 'FlipClock/flip.min.css">' . PHP_EOL;
  echo '<script src="' . DIR_WS_BASE . DIR_WS_EXTERNAL . 'FlipClock/flip.min.js"></script>' . PHP_EOL;
  ?>
<script>
  'use strict';

  function csnCustomerNoticeFlipInit(tick) {
    var root = tick && tick.root ? tick.root : null,
        remainingSeconds,
        dueDate,
        labelDays,
        labelHours,
        labelMinutes,
        labelSeconds,
        counter;

    if (!root || root.getAttribute('data-csn-flip-ready') === '1') {
      return;
    }

    if (typeof Tick === 'undefined' || !Tick.count || typeof Tick.count.down !== 'function') {
      return;
    }

    root.setAttribute('data-csn-flip-ready', '1');

    remainingSeconds = parseInt(root.getAttribute('data-remaining-seconds'), 10);
    if (isNaN(remainingSeconds) || remainingSeconds < 0) {
      remainingSeconds = 0;
    }

    labelDays    = root.getAttribute('data-label-days') || 'Days';
    labelHours   = root.getAttribute('data-label-hours') || 'Hours';
    labelMinutes = root.getAttribute('data-label-minutes') || 'Minutes';
    labelSeconds = root.getAttribute('data-label-seconds') || 'Seconds';

    tick.setConstant('DAY_SINGULAR', labelDays);
    tick.setConstant('DAY_PLURAL', labelDays);
    tick.setConstant('HOUR_SINGULAR', labelHours);
    tick.setConstant('HOUR_PLURAL', labelHours);
    tick.setConstant('MINUTE_SINGULAR', labelMinutes);
    tick.setConstant('MINUTE_PLURAL', labelMinutes);
    tick.setConstant('SECOND_SINGULAR', labelSeconds);
    tick.setConstant('SECOND_PLURAL', labelSeconds);

    dueDate = new Date(Date.now() + (remainingSeconds * 1000));
    counter = Tick.count.down(dueDate, {
      format: ['d', 'h', 'm', 's'],
      cascade: true,
      interval: 1000
    });

    counter.onupdate = function (value) {
      tick.value = value;
    };
  }
</script>
  <?php
}
