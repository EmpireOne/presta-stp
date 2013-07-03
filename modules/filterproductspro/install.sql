CREATE TABLE IF NOT EXISTS `PREFIX_fpp_searcher` (
  `id_searcher` int(10)  NOT NULL AUTO_INCREMENT,
  `internal_name` varchar(100) NOT NULL,
  `position` varchar(50) NOT NULL,
  `instant_search` tinyint(1) NOT NULL,
  `hide_filter_category` tinyint(1) NOT NULL,
  `hide_filter_manufacturer` tinyint(1) NOT NULL,
  `hide_filter_supplier` tinyint(1) NOT NULL,
  `type_filter_category` tinyint(1) NOT NULL,
  `filter_categories` varchar(500) NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id_searcher`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_searcher_lang` (
  `id_searcher` INT(10)  NOT NULL,
  `id_lang` INT(10) NOT NULL,
  `name` VARCHAR(250) NOT NULL, 
  PRIMARY KEY (`id_searcher`, `id_lang`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_filter` (
  `id_filter` INT(10)  NOT NULL AUTO_INCREMENT,
  `id_searcher` INT(10) NOT NULL,
  `type` VARCHAR(20) NOT NULL,
  `multi_options` TINYINT(1) NOT NULL, 
  `position` INT(10) NOT NULL,
  `criterion` VARCHAR(50) NOT NULL,
  `level_depth` INT(10) NULL,
  `id_parent` INT(10) NOT NULL,
  `num_columns` INT(10) NOT NULL,
  `search_ps` TINYINT(1) NOT NULL,
  `active` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id_filter`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_filter_lang` (
  `id_filter` INT(10)  NOT NULL,
  `id_lang` INT(10) NOT NULL,
  `name` VARCHAR(250) NOT NULL,
  PRIMARY KEY (`id_filter`, `id_lang`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_option_criterion` (
  `id_option_criterion` INT(10)  NOT NULL AUTO_INCREMENT,
  `criterion` VARCHAR(50) NOT NULL,
  `level_depth` INT(10) NULL,  
  `id_table` INT(10) NOT NULL,
  PRIMARY KEY (`id_option_criterion`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_option_criterion_lang` (
  `id_option_criterion` INT(10)  NOT NULL,
  `id_lang` INT(10) NOT NULL,
  `value` VARCHAR(250) NOT NULL,  
  PRIMARY KEY (`id_option_criterion`, `id_lang`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_option` (
  `id_option` INT(10)  NOT NULL AUTO_INCREMENT,
  `id_filter` INT(10) NOT NULL,
  `position` INT(10) NOT NULL,
  `active` TINYINT(1) NOT NULL,
  `id_option_criterion` INT(10) NOT NULL,
  PRIMARY KEY (`id_option`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_column` (
  `id_column` INT(10)  NOT NULL AUTO_INCREMENT,
  `id_filter` INT(10) NOT NULL,
  `position` INT(10) NOT NULL,
  PRIMARY KEY (`id_column`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_column_lang` (
  `id_column` INT(10)  NOT NULL AUTO_INCREMENT,
  `id_lang` INT(10) NOT NULL,
  `value` VARCHAR(250) NOT NULL, 
  PRIMARY KEY (`id_column`,`id_lang`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_column_option` (
  `id_column_option` INT(10)  NOT NULL AUTO_INCREMENT,
  `id_column` INT(10)  NOT NULL,
  `id_option` INT(10) NOT NULL,
  `position` INT(10) NOT NULL,
  PRIMARY KEY (`id_column_option`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_index_product` (
  `id_option` int(10)  NOT NULL,
  `id_product` int(10)  NOT NULL,
  `id_filter` int(10) default NULL,
  `id_searcher` int(10) default NULL,
  `id_dependency_option` int(10) default NULL,
  KEY `id_option` (`id_option`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_dependency_option` (
  `id_dependency_option` int(10) NOT NULL auto_increment,
  `id_filter` int(10)  NOT NULL,
  `id_filter_parent` int(10)  NOT NULL,
  `ids_option` varchar(255) default NULL,
  PRIMARY KEY  (`id_dependency_option`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `PREFIX_fpp_filter_category` (
  `id_filter` INT(10)  NOT NULL,
  `id_category` INT(10) NOT NULL
) DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_hook` (`id_hook` ,`name` ,`title` ,`description` ,`position`) VALUES (NULL , 'filterProductsPro', 'Filter Products Pro', NULL , '1');