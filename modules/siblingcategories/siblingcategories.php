<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class SiblingCategories extends Module
{
	protected $category;
	protected $parent_category;
	
	public function __construct()
	{
		$this->name = 'siblingcategories';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'MZ';

		parent::__construct();

		$this->displayName = $this->l('Sibling Categories block');
		$this->description = $this->l('Adds a block featuring sibling product categories.');
	}

	public function install()
	{
		if (!parent::install() ||
			!$this->registerHook('displayMobileTopSiteMap') ||
			!$this->registerHook('leftColumn') ||
			!$this->registerHook('header') ||
			!$this->registerHook('categoryAddition') ||
			!$this->registerHook('categoryUpdate') ||
			!$this->registerHook('categoryDeletion') ||
			!$this->registerHook('actionAdminMetaControllerUpdate_optionsBefore') ||
			!$this->registerHook('actionAdminLanguagesControllerStatusBefore'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '<p>There is nothing to configure!';
	}

	public function hookLeftColumn($params)
	{	
			$id_category = (int)Tools::getValue('id_category');
			$no_category = 0;
			
		if (!$id_category || !Validate::isUnsignedId($id_category)) {
			$no_category = 1;
			$id_category = 2;
			}

		// Instantiate category
		$this->category = new Category($id_category, $this->context->language->id);

		//check if the category is active and return 404 error if is disable.
		if (!$this->category->active)
		{
			$no_category = 1;
			$id_category = 2;
		}
		
		//check if category can be accessible by current customer and return 403 if not
		if (!$this->category->checkAccess($this->context->customer->id))
		{
			$no_category = 1;
			$id_category = 2;
		}
		
		if ($no_category) {
			$this->parent_category = new Category($this->category->id);
			$top_category = 1;
		} else {
			$this->parent_category = new Category($this->category->id_parent);
			$top_category = 0;
		}
		
		if ($this->category->id_parent == 2) {
			$top_category = 1;
		}
		
		if (!$this->isCached('siblingcategories.tpl', $this->getCacheId()))
		{
			$children = $this->parent_category->getSubcategories($this->context->language->id);
		
			$this->context->smarty->assign(array(
				'sibling_categories' => $children,
				'siblings_nb_total' => count($children),
				'top_category' => $top_category));
		}
		$display = $this->display(__FILE__, 'siblingcategories.tpl', $this->getCacheId());
		return $display;
	}
	
	public function hookDisplayMobileTopSiteMap($params)
	{	
		return $this->hookLeftColumn($params);
	}

	public function hookHeader()
	{
		$this->context->controller->addCSS(($this->_path).'blockcategories.css', 'all');
	}
	
	protected function getCacheId($name = null)
	{
		parent::getCacheId($name);
		$parent_category_id = (int)$this->parent_category->id;
		$groups = implode(', ', Customer::getGroupsStatic((int)$this->context->customer->id));
		$id_lang = (int)$this->context->language->id;
		return 'siblingcategories|'.(int)Tools::usingSecureMode().'|'.$this->context->shop->id.'|'.$groups.'|'.$id_lang.'|'.$parent_category_id;
	}
	
	private function _clearSiblingcategoriesCache()
	{
		$this->_clearCache('siblingcategories.tpl');
		$categ_tree_path = _PS_ROOT_DIR_.'/cache/sitemap_categ_tree/categ_tree.obj';
		if(file_exists($categ_tree_path)) {
			unlink($categ_tree_path);
		}
	}
	
		public function hookCategoryAddition($params)
	{
		$this->_clearSiblingcategoriesCache();
	}

	public function hookCategoryUpdate($params)
	{
		$this->_clearSiblingcategoriesCache();
	}

	public function hookCategoryDeletion($params)
	{
		$this->_clearSiblingcategoriesCache();
	}

	public function hookActionAdminMetaControllerUpdate_optionsBefore($params)
	{
		$this->_clearSiblingcategoriesCache();
	}
}
