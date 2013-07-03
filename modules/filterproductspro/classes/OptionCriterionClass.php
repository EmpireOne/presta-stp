<?php

/**
 * @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
 */
class OptionCriterionClass extends ObjectModel {
    public $id;
    public $criterion;
    public $level_depth;
    public $id_table;
    public $value;
    protected $table = 'fpp_option_criterion';
    protected $identifier = 'id_option_criterion';
    protected $tables = array('fpp_option_criterion', 'fpp_option_criterion_lang');
    protected $fieldsRequired = array('criterion', 'id_table');
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array('value');
    protected $fieldsSizeLang = array('value' => 250);
    protected $fieldsValidateLang = array(/*'value' => 'isGenericName'*/);    
    
    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	public	function __construct($id = null, $id_lang = null, $id_shop = null)
	{		        
        if(version_compare(_PS_VERSION_, '1.5') >= 0){
            self::$definition = array(
        		'table' => 'fpp_option_criterion',
        		'primary' => 'id_option_criterion',
        		'multilang' => true,
        		'multilang_shop' => false,
        		'fields' => array(
                    'criterion' =>                  array('type' => self::TYPE_STRING, 'required' => true, 'size' => 50),                        
                    'level_depth' => 	            array('type' => self::TYPE_INT, 'required' => false),
                    'id_table' => 	                array('type' => self::TYPE_INT, 'required' => false),
                    
                    // Lang fields
        			'value' => 				        array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 250)
        		)
        	);
            
            parent::__construct($id, $id_lang, $id_shop);
        }else{
            parent::__construct($id, $id_lang);
        }
	}
    
    public function getFields() {
        parent::validateFields();
        if (isset($this->id))
            $fields['id_option_criterion'] = (int) ($this->id);
        $fields['criterion'] = $this->criterion;
        $fields['level_depth'] = (int) ($this->level_depth);
        $fields['id_table'] = (int) ($this->id_table);
        return $fields;
    }      
    
    public static function getOptionsCriterionByCriterion($criterion){
        return Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE 
                criterion = '" . $criterion . "'
        ");
    }

    //Indexar para Categoria
    public static function indexCategories($clean_index = FALSE){
        global $FilterProducts;
        $all_categories = Category::getCategories(FALSE, FALSE);
        
        if($clean_index && !self::cleanIndexCategories())
            return FALSE;
        
        foreach ($all_categories as $categories) {
            foreach ($categories as $id_category => $category) {
                $category = new Category($id_category);
                
                $OptionCriterionClass = new OptionCriterionClass();
                
                $OptionCriterionClass->criterion = $FilterProducts->Criterions->Category;
                $OptionCriterionClass->level_depth = $category->level_depth;
                $OptionCriterionClass->id_table = $category->id;
                $OptionCriterionClass->value = $category->name;
                                
                if(!$OptionCriterionClass->add())
                    return FALSE;
            }
        }
        
        return TRUE;
    }
    
    //Eliminar indexado para Categoria
    public static function cleanIndexCategories(){
        global $FilterProducts;
        
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE
                criterion = '" . $FilterProducts->Criterions->Category . "'
        ");
    }
    
    //Indexar para Caracteristicas
    //Para este indexado se usa el campo {level_depth} para almacenar el {id_feature}, para luego poder hacer
    //el filtro de {option_criterion} por {criterio} y {id_feature}
    public static function indexFeatures($clean_index = FALSE){
        global $FilterProducts;
        
        if($clean_index && !self::cleanIndexFeatures())
            return FALSE;
            
        $features = Db::getInstance()->ExecuteS("
            SELECT
                fv.*
            FROM
                " . _DB_PREFIX_ . "feature_value AS fv,
                " . _DB_PREFIX_ . "feature_value_lang AS fvl
            WHERE
                fv.id_feature_value = fvl.id_feature_value AND
                fvl.id_lang = ".Configuration::get('PS_LANG_DEFAULT')."
            ORDER BY fvl.value
        ");
        
        foreach ($features as $feature) {
            $featureValue = new FeatureValue($feature['id_feature_value']);
            
            $OptionCriterionClass = new OptionCriterionClass();
                
            $OptionCriterionClass->criterion = $FilterProducts->Criterions->Feature;
            $OptionCriterionClass->level_depth = $featureValue->id_feature;
            $OptionCriterionClass->id_table = $featureValue->id;
            $OptionCriterionClass->value = $featureValue->value;

            if (!$OptionCriterionClass->validateFieldsLang(false, true))
                continue;
                
            if (is_array($OptionCriterionClass->value) && 
                sizeof($OptionCriterionClass->value) && 
                empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
            )
                continue;
                
            if(!$OptionCriterionClass->add())
                return FALSE;            
        }
        
        return TRUE;
    }
    
    //Eliminar indexado para Categoria
    public static function cleanIndexFeatures(){
        global $FilterProducts;
        
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE
                criterion = '" . $FilterProducts->Criterions->Feature . "'
        ");
    }
    
    //Indexar para Atributos
    //Para este indexado se usa el campo {level_depth} para almacenar el {id_attribute_group}, para luego poder hacer
    //el filtro de {option_criterion} por {criterio} y {id_attribute_group}
    public static function indexAttributes($clean_index = FALSE){
        global $FilterProducts;
        
        if($clean_index && !self::cleanIndexAttributes())
            return FALSE;
        
        $attributes = Db::getInstance()->ExecuteS("
            SELECT
                a.*
            FROM
                " . _DB_PREFIX_ . "attribute AS a,
                " . _DB_PREFIX_ . "attribute_lang AS al
            WHERE
                a.id_attribute = al.id_attribute AND
                al.id_lang = ".Configuration::get('PS_LANG_DEFAULT')."
            ORDER BY al.name
        ");
        
        foreach ($attributes as $attr) {
            $attribute = new Attribute($attr['id_attribute']);
                       
            $OptionCriterionClass = new OptionCriterionClass();
                
            $OptionCriterionClass->criterion = $FilterProducts->Criterions->Attribute;
            $OptionCriterionClass->level_depth = $attribute->id_attribute_group;
            $OptionCriterionClass->id_table = $attribute->id;
            $OptionCriterionClass->value = $attribute->name;

            if (!$OptionCriterionClass->validateFieldsLang(false, true))
                continue;
                
            if (is_array($OptionCriterionClass->value) && 
                sizeof($OptionCriterionClass->value) && 
                empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
            )
                continue;
                
            if(!$OptionCriterionClass->add())
                return FALSE;            
        }
        
        return TRUE;
    }
    
    //Eliminar indexado para Atributos
    public static function cleanIndexAttributes(){
        global $FilterProducts;
        
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE
                criterion = '" . $FilterProducts->Criterions->Attribute . "'
        ");
    }
    
    //Indexar para Fabricantes
    public static function indexManufacturers($clean_index = FALSE){
        global $FilterProducts;
        
        if($clean_index && !self::cleanIndexManufacturers())
            return FALSE;
                
        $manufacturers = self::getManufacturers(FALSE);
        
        foreach ($manufacturers as $manufacturer) {
            $OptionCriterionClass = new OptionCriterionClass();
                            
            $OptionCriterionClass->criterion = $FilterProducts->Criterions->Manufacturer;            
            $OptionCriterionClass->id_table = $manufacturer['id_manufacturer'];
            $OptionCriterionClass->value = self::getEmptyValuesLang($manufacturer['name']);
            
            if (!$OptionCriterionClass->validateFieldsLang(false, true))
                continue;
                
            if (is_array($OptionCriterionClass->value) && 
                sizeof($OptionCriterionClass->value) && 
                empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
            )
                continue;
                
            if(!$OptionCriterionClass->add())
                return FALSE;    
        }
        
        return TRUE;
    }
    
    //Eliminar indexado para Fabricantes
    public static function cleanIndexManufacturers(){
        global $FilterProducts;
        
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE
                criterion = '" . $FilterProducts->Criterions->Manufacturer . "'
        ");
    }
    
    //Indexar para Fabricantes
    public static function indexSuppliers($clean_index = FALSE){
        global $FilterProducts;
        
        if($clean_index && !self::cleanIndexSuppliers())
            return FALSE;
        
        $suppliers = self::getSuppliers(FALSE);
        
        foreach ($suppliers as $supplier) {
            $OptionCriterionClass = new OptionCriterionClass();
                            
            $OptionCriterionClass->criterion = $FilterProducts->Criterions->Supplier;            
            $OptionCriterionClass->id_table = $supplier['id_supplier'];
            $OptionCriterionClass->value = self::getEmptyValuesLang($supplier['name']);

            if (!$OptionCriterionClass->validateFieldsLang(false, true))
                continue;
                
            if (is_array($OptionCriterionClass->value) && 
                sizeof($OptionCriterionClass->value) && 
                empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
            )
                continue;
                
            if(!$OptionCriterionClass->add())
                return FALSE;    
        }
        
        return TRUE;
    }
    //Eliminar indexado para Atributos
    public static function cleanIndexSuppliers(){
        global $FilterProducts;
        
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE
                criterion = '" . $FilterProducts->Criterions->Supplier . "'
        ");
    }

    public static function getOptionsCriterionsByLevelDepth($criterions = array(), $levels_depth = NULL){
        $criterions = is_array($criterions) && sizeof($criterions) > 0 ? $criterions : NULL;
        if(is_null($criterions))return array();
        
        $list_criterions = "";
        foreach ($criterions as $criterion) {
            $list_criterions .= "'" . $criterion . "',";
        }
        $list_criterions = strlen($list_criterions) > 0 ? substr($list_criterions, 0, -1) : $list_criterions;
                
        return Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE 
                criterion IN (" . $list_criterions . ")
                ".(!is_null($levels_depth) ? "AND level_depth IN(" . implode(',', $levels_depth) . ")" : "")."
        ");
    }
    
    public static function getOptionsCriterionsByIdTable($criterion = '', $ids_table = array()){
        if(empty($criterion))return array();
        
        return Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE 
                criterion = '" . $criterion . "'
                ".(!is_null($ids_table) && sizeof($ids_table) ? "AND id_table IN(" . implode(',', $ids_table) . ")" : "")."
        ");
    }
    
    public static function getManufacturers($id_lang = 0)
	{
		if (!$id_lang)
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$sql = 'SELECT m.*, ml.`description`, ml.`short_description`
			FROM `'._DB_PREFIX_.'manufacturer` m
			LEFT JOIN `'._DB_PREFIX_.'manufacturer_lang` ml ON (
				m.`id_manufacturer` = ml.`id_manufacturer`
				AND ml.`id_lang` = '.(int)$id_lang.'
			)
			';
            
		$sql .= '
			GROUP BY m.id_manufacturer
			ORDER BY m.`name` ASC';

		$manufacturers = Db::getInstance()->executeS($sql);
		if ($manufacturers === false)
			return false;

		$total_manufacturers = count($manufacturers);
		$rewrite_settings = (int)Configuration::get('PS_REWRITING_SETTINGS');

		for ($i = 0; $i < $total_manufacturers; $i++)
			if ($rewrite_settings)
				$manufacturers[$i]['link_rewrite'] = Tools::link_rewrite($manufacturers[$i]['name'], false);
			else
				$manufacturers[$i]['link_rewrite'] = 0;

		return $manufacturers;
	}
    
    public static function getSuppliers($id_lang = 0)
	{        
        if (!$id_lang)
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$sql = 'SELECT s.*, sl.`description`
			FROM `'._DB_PREFIX_.'supplier` s
			LEFT JOIN `'._DB_PREFIX_.'supplier_lang` sl ON (
				s.`id_supplier` = sl.`id_supplier`
				AND sl.`id_lang` = '.(int)$id_lang.'
			)
			';
            
		$sql .= '
			GROUP BY s.id_supplier
			ORDER BY s.`name` ASC';

		$suppliers = Db::getInstance()->executeS($sql);
        
		if ($suppliers === false)
			return false;

		$nb_suppliers = count($suppliers);
		$rewrite_settings = (int)Configuration::get('PS_REWRITING_SETTINGS');
		for ($i = 0; $i < $nb_suppliers; $i++)
			if ($rewrite_settings)
				$suppliers[$i]['link_rewrite'] = Tools::link_rewrite($suppliers[$i]['name'], false);
			else
				$suppliers[$i]['link_rewrite'] = 0;
		return $suppliers;
	}

    static function getEmptyValuesLang($val = '_') {
        $languages = Language::getLanguages(false);
        $values = array();

        foreach ($languages as $lang) {
            $values[$lang['id_lang']] = $val;
        }

        return $values;
    }

    /**
     * Check then return multilingual fields for database interaction
     *
     * @return array Multilingual fields
     */
    public function getTranslationsFieldsChild() {
        parent::validateFieldsLang();

        $fieldsArray = array('value');
        $fields = array();
        $languages = Language::getLanguages(false);
        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));

        foreach ($languages as $language) {
            $fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
            $fields[$language['id_lang']][$this->identifier] = (int) ($this->id);

            foreach ($fieldsArray as $field) {
                if (!Validate::isTableOrIdentifier($field))
                    die(Tools::displayError());
                if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
                elseif (in_array($field, $this->fieldsRequiredLang))
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
                else
                    $fields[$language['id_lang']][$field] = '';
            }
        }

        return $fields;
    }
}

?>