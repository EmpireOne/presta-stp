ALTER TABLE `PREFIX_fpp_index_product` ADD `id_dependency_option` INT( 10 ) NULL AFTER `id_searcher`;
ALTER TABLE `PREFIX_fpp_dependency_option` DROP `id_option_parent`;
ALTER TABLE `PREFIX_fpp_dependency_option` CHANGE `id_option` `ids_option` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `PREFIX_fpp_dependency_option` ADD `id_dependency_option` INT( 10 ) NOT NULL AUTO_INCREMENT FIRST ,
ADD PRIMARY KEY ( `id_dependency_option` );
TRUNCATE TABLE `PREFIX_fpp_dependency_option`;
TRUNCATE TABLE `PREFIX_fpp_index_product`;