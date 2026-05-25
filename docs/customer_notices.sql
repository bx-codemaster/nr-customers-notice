-- Version 0.2.4

CREATE TABLE IF NOT EXISTS customer_notices (
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
  PRIMARY KEY (customer_notice_id)
);

CREATE TABLE IF NOT EXISTS customer_notices_description (
  customer_notice_id int(10) unsigned NOT NULL,
  languages_id int(10) unsigned NOT NULL,
  title varchar(255)DEFAULT NULL,
  description text,
  PRIMARY KEY (customer_notice_id, languages_id)
);

-- new for version 0.2.2, 01-2022, noRiddle
ALTER TABLE customer_notices ADD countries mediumtext DEFAULT NULL;

-- new for version 0.2.3, 01-2022, noRiddle
ALTER TABLE customer_notices ADD customers_id INT(11) DEFAULT NULL AFTER position;

ALTER TABLE admin_access ADD customer_notices SMALLINT(1) NOT NULL DEFAULT 0;

UPDATE admin_access SET customer_notices = 1 WHERE customers_id = 1;

-- *** test by noRiddle ***
-- test query for performance
-- do we need KEYs on datetime fields as well as status and pages ? (we will never have so many entries fo customers notices so we can neglect this)
/*
EXPLAIN 
   SELECT cn.*, cnd.title, cnd.description
    FROM customer_notices AS cn
  LEFT JOIN customer_notices_description cnd
  ON cn.customer_notice_id = cnd.customer_notice_id
      AND cnd.languages_id = 2
    WHERE cn.status = 1
      AND (cn.startdate = '1000-01-01 00:00:00' OR cn.startdate <= 'now()')
      AND (cn.enddate = '9999-12-31 23:59:59' OR cn.enddate > 'now()')
      AND (cn.customers_status = '' OR FIND_IN_SET(0, cn.customers_status) > 0)
      AND (cn.pages = '' OR FIND_IN_SET('account', cn.pages) > 0)
 ORDER BY position;
*/