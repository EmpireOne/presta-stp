<?php
//i made
//source: ajax_products_list.php
//test: index.php?fc=module&module=filterproductspro&controller=list&id_feature=5&q=eo&limit=20&timestamp=1372533870935

if (isset($_GET['ad']))
	define('_PS_ADMIN_DIR_', $_GET['ad']);
else if(isset($_POST['ad']))
	define('_PS_ADMIN_DIR_', $_POST['ad']);
include('../../../../config/config.inc.php');

if(in_array($_SERVER["SERVER_ADDR"], array("127.0.0.1", "::1")))
	include('../../../../../'._PS_ADMIN_DIR_.'/init.php');//localhost
else
	include('../../../../'._PS_ADMIN_DIR_.'/init.php');//online

//getting values
if (!Tools::getIsset('action')){
/*class filterproductsprolistModuleFrontController extends ModuleFrontController{

	public function display(){*/
		$query = Tools::getValue('q', false);
		if (!$query or $query == '' or strlen($query) < 1)
			die();

		/*
		 * In the SQL request the "q" param is used entirely to match result in database.
		 * In this way if string:"(ref : #ref_pattern#)" is displayed on the return list, 
		 * they are no return values just because string:"(ref : #ref_pattern#)" 
		 * is not write in the name field of the product.
		 * So the ref pattern will be cut for the search request.
		 */
		if ($pos = strpos($query, ' (ref:'))
			$query = substr($query, 0, $pos);
		
		$excludeIds = Tools::getValue('excludeIds', false);
		if ($excludeIds && $excludeIds != 'NaN' and strlen($excludeIds) > 0)
			$excludeIds = implode(',', array_map('intval', explode('-', $excludeIds)));
		else
			$excludeIds = '';

		$id_feature = (int)Tools::getValue('id_feature', 0);
		$sql = '
			SELECT fvl.`id_feature_value`, fvl.`value`
			FROM `'._DB_PREFIX_.'feature_value_lang` fvl
			LEFT JOIN `'._DB_PREFIX_.'feature_value` fv
			ON (fvl.`id_feature_value` = fv.`id_feature_value`)
			LEFT JOIN `'._DB_PREFIX_.'feature_shop` fs
			ON (fv.`id_feature` = fs.`id_feature`)
			WHERE fv.`id_feature` = '.$id_feature.'
			AND fvl.`id_lang` = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('fs').'
			AND fvl.`value` LIKE \'%'.pSQL($query).'%\''.
			(!empty($excludeIds) ? ' AND fvl.`id_feature_value` NOT IN ('.$excludeIds.') ' : ' ');
		//var_dump($sql);//i debug

		$items = Db::getInstance()->executeS($sql);
		//var_dump($items);//i debug

		if ($items)
			foreach ($items AS $item)
				echo trim($item['value']).'|'.(int)($item['id_feature_value'])."\n";
	/*}
}*/

//adding a new value
}else{
	$action = Tools::getValue('action');
	if ($action == 'addFeature'){
		$id_product = Tools::getValue('id_product');
		$id_feature = Tools::getValue('id_feature');
		$value = trim(Tools::getValue('value'));
		
		if (strlen($value) > 0){
			$feature_value = new FeatureValue();
			$languages = Language::getLanguages();
			foreach ($languages as $language)
				$feature_value->value[$language['id_lang']] = strval($value);	
			$feature_value->id_feature = (int)$id_feature;
			$feature_value->add();
			//var_dump($feature_value);//i debug

			$return['value'] = strval($value);
			$return['id_value'] = $feature_value->id;
	
			echo Tools::jsonEncode($return);
		}//else return nothing
	}
}
