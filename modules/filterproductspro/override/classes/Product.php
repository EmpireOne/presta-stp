<?php
//i made

class Product extends ProductCore{

        public static function getFrontFeaturesStatic($id_lang, $id_product){
			if (!Feature::isFeatureActive())
					return array();
			//if not stored in cache already
			if (!array_key_exists($id_product.'-'.$id_lang, self::$_frontFeaturesCache)){
				self::$_frontFeaturesCache[$id_product.'-'.$id_lang] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
						SELECT name, value, pf.id_feature
						FROM '._DB_PREFIX_.'feature_product pf
						LEFT JOIN '._DB_PREFIX_.'feature_lang fl ON (fl.id_feature = pf.id_feature AND fl.id_lang = '.(int)$id_lang.')
						LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = pf.id_feature_value AND fvl.id_lang = '.(int)$id_lang.')
						LEFT JOIN '._DB_PREFIX_.'feature f ON (f.id_feature = pf.id_feature AND fl.id_lang = '.(int)$id_lang.')
						WHERE pf.id_product = '.(int)$id_product.'
						ORDER BY f.position ASC'
				);
				//join multiple values of one feature into one value
				$features =& self::$_frontFeaturesCache[$id_product.'-'.$id_lang];
				$features_key = array();
				$first = true;
				foreach ($features as $key => $feature){
					if (!array_key_exists($feature['name'], $features_key)){
						$features_key[$feature['name']] = $key;
					}else{
						$features[(int)$features_key[$feature['name']]]['value'] .= (($first) ? ': ' : ', ')
							.$feature['value'];
						$first = false;
						unset($features[$key]);
					}
				}
			}
			return self::$_frontFeaturesCache[$id_product.'-'.$id_lang];
	}
}
