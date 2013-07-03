ALTER TABLE `PREFIX_fpp_column` CHANGE `position` `position` INT( 10 ) NOT NULL;
ALTER TABLE `PREFIX_fpp_column_option` CHANGE `id_option` `id_option` INT( 10 ) NOT NULL;
ALTER TABLE `PREFIX_fpp_column_option` CHANGE `position` `position` INT( 10 ) NOT NULL;

ALTER TABLE `PREFIX_fpp_index_product` ADD INDEX ( `id_option` );