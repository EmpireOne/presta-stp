ALTER TABLE `PREFIX_fpp_searcher` ADD `hide_filter_category` TINYINT(1) NOT NULL AFTER `instant_search`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('FPP_DISPLAY_BACK_BUTTON_FILTERS', '0', NOW(), NOW());
    
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('FPP_DISPLAY_EXPAND_BUTTON_OPTION', '0', NOW(), NOW());
    
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('FPP_ID_CONTENT_RESULTS', '#center_column', NOW(), NOW());