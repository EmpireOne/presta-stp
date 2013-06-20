<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;
class TMProducts extends Module
{
	private $_html = '';
	private $_postErrors = array();
	function __construct()
	{
		$this->name = 'tmproducts';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'TM';
		parent::__construct();
		$this->displayName = $this->l('TM Products');
		$this->description = $this->l('Displays some products at the top of your homepage.');
	}
	function install()
	{
		if (!Configuration::updateValue('TMPRODUCTS_NBR', 4) OR !parent::install() OR !$this->registerHook('home'))
			return false;
		return true;
	}
	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitHomeFeatured'))
		{
			$nbr = (int)(Tools::getValue('nbr'));
			if (!$nbr OR $nbr <= 0 OR !Validate::isInt($nbr))
				$errors[] = $this->l('Invalid number of products');
			else
				Configuration::updateValue('TMPRODUCTS_NBR', (int)($nbr));
			if (isset($errors) AND sizeof($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}
	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Number of products displayed').'</label>
				<div class="margin-form">
					<input type="text" size="5" name="nbr" value="'.Tools::getValue('nbr', (int)(Configuration::get('TMPRODUCTS_NBR'))).'" />
					<p class="clear">'.$this->l('The number of products displayed on homepage (default: 3).').'</p>
					
				</div>
				<center><input type="submit" name="submitHomeFeatured" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}
	function hookHome($params)
	{
		global $smarty;
		$category = new Category(4, Configuration::get('PS_LANG_DEFAULT'));
		$nb = (int)(Configuration::get('TMPRODUCTS_NBR'));
		$products = $category->getProducts((int)($params['cookie']->id_lang), 1, ($nb ? $nb : 10));
		$smarty->assign(array(
		'products' => $products,
		'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
		'homeSize' => Image::getSize('home')));
		return $this->display(__FILE__, 'tmproducts.tpl');
	}
}