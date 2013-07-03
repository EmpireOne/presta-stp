ALTER TABLE `PREFIX_fpp_searcher` ADD COLUMN `hide_filter_manufacturer` tinyint(1) NOT NULL AFTER `hide_filter_category`;
ALTER TABLE `PREFIX_fpp_searcher` ADD COLUMN `hide_filter_supplier` tinyint(1) NOT NULL AFTER `hide_filter_manufacturer`;
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('FPP_ONLY_PRODUCTS_STOCK', '0', NOW(), NOW());