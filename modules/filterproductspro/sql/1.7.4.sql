ALTER TABLE `PREFIX_fpp_searcher` ADD `type_filter_category` TINYINT( 1 ) NOT NULL AFTER `hide_filter_category` ,
ADD `filter_categories` VARCHAR( 500 ) NULL AFTER `type_filter_category`;