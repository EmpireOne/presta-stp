<?php
/* version 1.5.3.1 */
class AdminProductsController extends AdminProductsControllerCore
{

	//adding features to DB

	//getting values for template
	//adding features to DB

	//getting values for template
	//adding features to DB

	//getting values for template
	//adding features to DB
	public function processFeatures(){
		if (!Feature::isFeatureActive())
			return;

		if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product')))){
			// delete all objects
			$product->deleteFeatures();

			// add new objects
			$languages = Language::getLanguages(false);
			foreach ($_POST as $id_featureey => $val){
				if (preg_match('/^feature_([0-9]+)_value/i', $id_featureey, $match)){
					if ($val){
						//$product->addFeaturesToDB($match[1], $val);//i orig
						$v = explode('-', $val);
						foreach ($v as $value)
							$product->addFeaturesToDB($match[1], $value);
					}
					//else{
						if ($default_value = $this->checkFeatures($languages, $match[1])){
							$id_value = $product->addFeaturesToDB($match[1], 0, 1);
							foreach ($languages as $language){
								//if custom value passed
								if ($cust = trim(Tools::getValue('custom_'.$match[1].'_'.(int)$language['id_lang']))
									and strlen($cust) > 0)
									$product->addFeaturesCustomToDB($id_value, (int)$language['id_lang'], $cust);
								else
									$product->addFeaturesCustomToDB($id_value, (int)$language['id_lang'], trim($default_value));
							}
						}
					//}
				}
			}
		}
		else
			$this->errors[] = Tools::displayError('A product must be created before adding features.');
	}

	//getting values for template
	public function initFormFeatures($obj){//i modif highly
		//i add, overriding default template for Prestashop has no this method inplemented, it moves only php files, not tpl from overriding folder
		if ($this->tpl_form === 'features.tpl'){
			$original_template_dir = $this->context->smarty->template_dir;
			$this->context->smarty->template_dir = '../modules/filterproductspro/override/controllers/admin/templates/products/';
		}
		//<<
		$data = $this->createTemplate($this->tpl_form);
		if (!Feature::isFeatureActive())
			$this->displayWarning($this->l('This feature has been disabled. ')
				.' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance').'#featuresDetachables">'.$this->l('Performances').'</a>');
		else{
			if ($obj->id){
				if ($this->product_exists_in_shop){
					$features = Feature::getFeatures($this->context->language->id, (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP));
					
					//sorting - inserting id_feature into key place //is needed?
					foreach ($features as $k => $v)
						$f[$v['id_feature']] = $v;
					$features = $f;

					foreach ($features as $id_feature => $feature){
						$features[$id_feature]['selected_values'] = false;//selected features values for product
						$features[$id_feature]['custom_value'] = array();//custom feature value
						$custom = false;//if has custom

						//all feature values values (names) for seected lang
						$features[$id_feature]['featureValues'] = FeatureValue::getFeatureValuesWithLang($this->context->language->id, (int)$feature['id_feature']);
						//sorting for late use
						if (is_array($features[$id_feature]['featureValues']) and !empty($features[$id_feature]['featureValues'])){
							$ff = array();
							foreach ($features[$id_feature]['featureValues'] as $kk => $vv)
								$ff[$vv['id_feature_value']] = $vv;
							$features[$id_feature]['featureValues'] = $ff;
						}

						$i = 0;
						foreach ($obj->getFeatures() as $obj_feature){//all features values for current product, can be multiple
							if ($obj_feature['id_feature'] == $feature['id_feature']){
								//if there is no values, there can not be selected values
								if (is_array($features[$id_feature]['featureValues']) 
									and !empty($features[$id_feature]['featureValues'])
									and isset($features[$id_feature]['featureValues'][$obj_feature['id_feature_value']])
									){
									$features[$id_feature]['selected_values'][$i]['id_feature_value'] = $obj_feature['id_feature_value'];
									$features[$id_feature]['selected_values'][$i]['value'] = $features[$id_feature]['featureValues'][$obj_feature['id_feature_value']]['value'];
									$i++;
								}else
									$custom = $obj_feature['id_feature_value'];
							}
						}
						if ($custom and (int)$custom > 0)
							$features[$id_feature]['custom_value'] = FeatureValue::getFeatureValueLang((int)$custom);
					}
					//var_dump($features);//i debug
					$data->assign('available_features', $features);
					$data->assign('product', $obj);
					$data->assign('link', $this->context->link);
					$data->assign('languages', $this->_languages);
					$data->assign('default_form_language', $this->default_form_language);
				}
				else
					$this->displayWarning($this->l('You must save the product in this shop before adding features.'));
			}
			else
				$this->displayWarning($this->l('You must save this product before adding features.'));

			$this->tpl_form_vars['custom_form'] = $data->fetch();
		}
		//i add, returning back template dir variable
		if ($this->tpl_form === 'features.tpl')
			$this->context->smarty->template_dir = $original_template_dir;
		//<<
	}
}