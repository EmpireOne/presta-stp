<?php

if (!defined('_PS_VERSION_'))
	exit;

class StepSearch extends Module
{
	/* @var boolean error */
	protected $_errors = false;
	
	public function __construct()
	{
		$this->name = 'stepsearch';
		$this->tab = 'search_filter';
		$this->version = '1.0';
		$this->author = 'editorstefan';
		$this->need_instance = 0;
		$this->table_name = 'stepsearch_lang';

	 	parent::__construct();

		$this->displayName = $this->l('Step Search module');
		$this->description = $this->l('Adds the possibility to search by steps');
		$this->confirmUninstall = $this->l('The module\'s configuration will be erased, continue?');
	}
	
	public function install()
	{
		if (!parent::install() OR
			!$this->registerHook('header') OR
			!$this->registerHook('displayMobileHeader') OR
			!$this->registerHook('displayMobileTopSiteMap') OR
			!$this->registerHook('leftColumn') OR
			!$this->_installConfig() OR
			!$this->_installTable() OR
			!$this->_installTableValues())
			return false;
		return true;
	}
	
	public function uninstall()
	{
		if (!parent::uninstall() OR
			!$this->_removeConfig() OR
			!$this->_eraseTable())
			return false;
		return true;
	}

	private function _installConfig()
	{
		if (!Configuration::updateValue('STEPSEARCH_STEPS', 2))
			return false;
		else return true;
	}

	private function _removeConfig()
	{
		if (!Configuration::deleteByname('STEPSEARCH_STEPS') OR !Configuration::deleteByname('STEPSEARCH_ADDED_CATEGORIES'))
			return false;
		else return true;
	}	


	private function _installTable(){
		$sql = 'CREATE TABLE  `'._DB_PREFIX_.$this->table_name.'` (
				`step` INT( 12 ) NOT NULL ,
				`name` VARCHAR( 64 ) NOT NULL ,
				`id_lang` INT( 12 ) NOT NULL
				) ENGINE =' ._MYSQL_ENGINE_;
		if (!Db::getInstance()->Execute($sql))
			return false;
		else return true;
	}


	private function _eraseTable(){
		if(!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.$this->table_name.'`'))
			return false;
		else return true;
	}

	private function _installTableValues()
	{
		$sql= 'INSERT INTO  `'._DB_PREFIX_.$this->table_name.'`
			(`step` ,`name`,`id_lang`)
			VALUES (1, "Step 1", 1),(2, "Step 2", 1)';
		if (!Db::getInstance()->Execute($sql))
			return false;
		else return true;					
	}
	

	public function getContent(){
		$output = '<h2>'.$this->displayName.'</h2>';
		$this->_errors = array();

		if (Tools::isSubmit('SubmitAddCategory'))
		{
			$category_to_add = Tools::getValue('addCategory');
			$addedCategories = Configuration::get('STEPSEARCH_ADDED_CATEGORIES');
			$addedCategories .= empty($addedCategories) ? $category_to_add : ','.$category_to_add;

			if (!Configuration::updateValue('STEPSEARCH_ADDED_CATEGORIES',$addedCategories))
				$this->_errors[] = $this->l('Error: '.mysql_error());
		}
		else if (Tools::isSubmit('submitRemoveCategory'))
		{
			$category_to_remove = Tools::getValue('addedCategories');
			$addedCategories = Configuration::get('STEPSEARCH_ADDED_CATEGORIES');
			$addedArray = explode(',', $addedCategories);

			foreach ($addedArray as $key => $value) {
				if ($value == $category_to_remove)
					unset($addedArray[$key]);
			}
			$fixed_categories = implode(',', $addedArray);

			if (!Configuration::updateValue('STEPSEARCH_ADDED_CATEGORIES',$fixed_categories))
				$this->_errors[] = $this->l('Error: '.mysql_error());
		}
		else if (Tools::isSubmit('SubmitChanges'))
		{

			$steps_count = Tools::getValue('steps');
			if (!is_numeric($steps_count))
				$this->_errors[] = $this->l('Step number must be an integer!');

			$maxstep = (int)(Db::getInstance()->getValue('SELECT MAX(step) FROM '._DB_PREFIX_.$this->table_name));

			if (sizeof($this->_errors)) {
				$output .= $this->displayError(implode(', ', $this->_errors));
				return $output.$this->_displayForm();
			}


			if ($steps_count < $maxstep) { // means we have reduced the number of steps, erase useless data
				if(!Db::getInstance()->delete(_DB_PREFIX_.$this->table_name, 'step > '.$steps_count))
					$this->_errors[] = $this->l('Error: '.mysql_error());
			}
			else if ($steps_count > $maxstep) // We need to add new steps
			{
				$to_add = $steps_count - $maxstep;

				for ($i=0; $i < $to_add; $i++) {
					$theN = $i+1+$maxstep;
					$data = array('step' => $theN, 'name' => 'Step '.$theN, 'id_lang' => 1);
					if(!Db::getInstance()->autoExecute(_DB_PREFIX_.$this->table_name, $data, 'INSERT'))
						$this->_errors[] = $this->l('Error: '.mysql_error());
				}

			}
			if (sizeof($this->_errors)) {
				$output .= $this->displayError(implode(', ', $this->_errors));
				return $output.$this->_displayForm();
			} else Configuration::updateValue('STEPSEARCH_STEPS', $steps_count);	

			if(is_array(Tools::getValue('step')))
			{
				$stepLangs = Tools::getValue('step');
				foreach ($stepLangs as $stepN => $lang) {
					if($stepN <= $steps_count)
						foreach ($lang as $id_lang => $name) {

							if(!Db::getInstance()->getValue('SELECT step FROM '._DB_PREFIX_.$this->table_name. ' WHERE step = '.$stepN.' AND id_lang = '.$id_lang)) {
								if(!Db::getInstance()->autoExecute(_DB_PREFIX_.$this->table_name, array('name' => $name, 'step'=> $stepN, 'id_lang' => $id_lang), 'INSERT'))
									$this->_errors[] = $this->l('Error: '.mysql_error()); // let's add the language
							}

							if(!Db::getInstance()->autoExecute(_DB_PREFIX_.$this->table_name, array('name' => $name), 'UPDATE', 'step = '.$stepN.' AND id_lang = '.$id_lang))
								$this->_errors[] = $this->l('Error: '.mysql_error());
						}
				}
			}	
		}
		if (sizeof($this->_errors))
			$output .= $this->displayError(implode(', ', $this->_errors));
		else $output .= $this->displayConfirmation('Values Updated');
	
		return $output.$this->_displayForm();
	}
	
	private function _displayForm()
	{
		global $cookie;

		/* Languages preliminaries */
		$defaultLanguage = (int)($cookie->id_lang);
		$languages = Language::getLanguages(false);
		$iso = Language::getIsoById((int)($cookie->id_lang));

		$steps_number = Configuration::get('STEPSEARCH_STEPS');

		$output = '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
		';

		$allCategories = Tools::isSubmit('parentFilter') ? $this->getSimpleCategoriesFiltered($cookie->id_lang, (int)(Tools::getValue('parentFilter'))) : $this->getSimpleCategoriesFiltered($cookie->id_lang);


		$addedCategories = Configuration::get('STEPSEARCH_ADDED_CATEGORIES');


		if (!empty($addedCategories))
		{
			$output .='<h4>'.$this->l('Added categories (remove filter to see all)').'</h4>';
			$addedCategories_array = explode(',', $addedCategories);
			$output .= '<select size="10" style="width: 400px" name="addedCategories">';
			foreach ($allCategories as $allkey => $category) {
				foreach ($addedCategories_array as $addedCategory) { // Retrieve added category name, saves bandwidth
					if (in_array($addedCategory, $category)) {
						$output .= '<option value="'.$category['id_category'].'">'.$category['name'].'</option>';
						unset($allCategories[$allkey]); // This way "add category" will be less crowded and confusing!
					}
				}
			}
			$output .= '</select><p class="clear"></p>';
			$output .= '<input type="submit" class="button" name="submitRemoveCategory" value="'.$this->l('Remove Selected').'">';

		} else {
			$output .='<p>'.$this->l('You have not added categories yet. Use the form below to do it').'</p>';
		}

		$output .= '
					<h4>'.$this->l('Add a category').'</h4>
					<p>'.$this->l('If you have many categories, you might filter the list below by parent category id. This way, only categories that are directly children of the selected category will be displayed, making your life easier.').'</p>
					<label>'.$this->l('Parent ID').'</label>
					<div class="margin-form">
						<input type="text" name="parentFilter" value="'.(Tools::isSubmit('parentFilter') ? Tools::getValue('parentFilter') : '').'"/>
						<input class="button" type="submit" name="submitParentFilter" value="'.$this->l('Filter!').'" >
					</div>';
		if ($allCategories) {
			$output .= '
					<div class="margin-form">
						<select name="addCategory">
		';

		foreach ($allCategories as $category) 
			$output .=' 	<option value="'.$category['id_category'].'">'.$category['name'].'</option>';



		$output .='		</select>';

			$output .= '<input type="submit" class="button" name="SubmitAddCategory" value="'.$this->l('Add').'">';


		$output .='	</div><hr>';
		} // End if allCategories
		else $output .= '<p>'.$this->l('No more children categories for this ID').'</p>';
						
		$output .='	<h4>'.$this->l('Steps').'</h4>
					<p>'.$this->l('Save data to be able to change steps names if you just increased the number, add eventual hints in parenthesis, they\'ll be shown when clicking on "Search Tips"').'</p>

					<label>'.$this->l('Number of steps').'</label>
					<div class="margin-form">
						<input size="1" type="text" name="steps" value="'.$steps_number.'"/>
					</div>
		';

		$step_data = $this->getSteps();
		
		$divLangName = 'step1';
		for ($i=2; $i <= $steps_number; $i++) { 
			$divLangName .= '¤step'.$i;
		}

		if (!$step_data) {
			$output .= '<p>'.$this->l('No Step data found, please save below to be able to add your names').'</p>';
		}
		else if(is_array($step_data)) // We have steps values, let's display'em
		{	
			foreach ($step_data as $skey => $step1)
			{

				$output .= '<label>'.$this->l('Step ').$skey.'</label>
							<div class="margin-form">
				';


				foreach ($step1 as $key => $step) { // Getting valoes for alla languages in the array
					$steplang[$skey][$step['id_lang']] = $step['name'];
				}

				foreach ($languages as $language)
				{
					$content = isset($steplang[$skey][$language['id_lang']]) ? $steplang[$skey][$language['id_lang']] : '';
					$output .= '
					<div id="step'.$skey.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" id="step'.$skey.'text_'.$language['id_lang'].'" name="step['.$skey.']['.$language['id_lang'].']" value="'.$content.'">
					</div>';
				}
				
				$output .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'step'.$skey, true);
				$output .= '</div><p class="clear">&nbsp;</p>';

			}
		}


		$output .= '<p class="center"><input class="button" type="submit" name="SubmitChanges" value="'.$this->l('Save').'"></p>
				</fieldset>
			</form>';

		return $output;
	}

	public function getSteps($id_lang = false)
	{
		$steps = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT *
			FROM '._DB_PREFIX_.$this->table_name. ($id_lang ? ' WHERE id_lang='.$id_lang : ''));
		if (!$id_lang && is_array($steps)) {
			foreach ($steps as $key => $step) {
				$finalSteps[$step['step']] = array();
			}
			foreach ($steps as $key => $step) {
				if (array_key_exists($step['step'], $finalSteps)) {
					$finalSteps[$step['step']][] = $step;
				}
			}
			$steps = $finalSteps;
		}
		return $steps;
		    
	}

	public function getSimpleCategoriesFiltered($id_lang, $parent = false, $ids = false)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.`id_category`, cl.`name`
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`)
		WHERE cl.`id_lang` = '.(int)($id_lang).
		($ids ? ' AND c.`id_category` IN('.$ids.')' : '').
		($parent ? ' AND c.`id_parent` ='.$parent : '').'
		ORDER BY cl.`name`');
	}

	public function hookLeftColumn($params)
	{
		global $cookie, $smarty;

		$addedCategories = Configuration::get('STEPSEARCH_ADDED_CATEGORIES');
		$steps = $this->getSteps($cookie->id_lang);
		if (!$addedCategories)
			return;
		$categories = $this->getSimpleCategoriesFiltered($cookie->id_lang, false, $addedCategories);

		$smarty->assign(array(
			'stepsearch_steps' => $steps,
			'stepsearch_categories' => $categories
		));

		return $this->display(__FILE__, 'stepsearch.tpl');
	}

	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	
	public function hookDisplayMobileTopSiteMap($params)
	{	
		return $this->hookLeftColumn($params);
	}


	public function hookHeader($params)
	{
		if (version_compare(@_PS_VERSION_,'1.5','>'))
		{
			$this->context->controller->addCSS(($this->_path).'css/stepsearch.css', 'all');
			$this->context->controller->addJS(($this->_path).'js/stepsearch.js', 'all');			
		}
		else {
			Tools::addCSS(($this->_path).'css/stepsearch.css', 'all');
			Tools::addJS(($this->_path).'js/stepsearch.js', 'all');			
		}
		
	}
	
	public function hookDisplayMobileHeader($params)
	{
		if (version_compare(@_PS_VERSION_,'1.5','>'))
		{
			$this->context->controller->addJS(($this->_path).'js/stepsearch_mobile_new.js', 'all');			
		}
		else {
			Tools::addJS(($this->_path).'js/stepsearch_mobile_new.js', 'all');			
		}
		
	}

	
}
?>