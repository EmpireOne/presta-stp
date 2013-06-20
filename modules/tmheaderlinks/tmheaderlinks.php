<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;
class TMHeaderLinks extends Module
{
	function __construct()
	{
		$this->name = 'tmheaderlinks';
		$this->tab = 'front_office_features';
		$this->version = 1.0;
		$this->author = 'PrestaShop';
		parent::__construct();
		
		$this->displayName = $this->l('TM Headerlinks');
		$this->description = $this->l('Adds a block that displays permanent links such as sitemap, contact, etc.');
	}
	function install()
	{
			return (parent::install() AND $this->registerHook('top'));
	}
	/**
	* Returns module content for header
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookTop($params)
	{
		return $this->display(__FILE__, 'tmheaderlinks.tpl');
	}
}