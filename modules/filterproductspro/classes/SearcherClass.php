<?php

/**
 * @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
 */
class SearcherClass extends ObjectModel {
    public $id;
    public $internal_name;
    public $position;
    public $name;
    public $instant_search;
    public $hide_filter_category;
    public $hide_filter_manufacturer;
    public $hide_filter_supplier;
    public $type_filter_category;
    public $filter_categories;
    public $active;
    protected $table = 'fpp_searcher';
    protected $identifier = 'id_searcher';
    protected $tables = array('fpp_searcher', 'fpp_searcher_lang');
    protected $fieldsRequired = array('internal_name', 'position', 'instant_search');
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array('name');
    protected $fieldsSizeLang = array('name' => 200);
    protected $fieldsValidateLang = array('name' => 'isGenericName');

    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	public	function __construct($id = null, $id_lang = null, $id_shop = null)
	{		        
        if(version_compare(_PS_VERSION_, '1.5') >= 0){                        
            self::$definition = array(
        		'table' => 'fpp_searcher',
        		'primary' => 'id_searcher',
        		'multilang' => true,
        		'multilang_shop' => false,
        		'fields' => array(			
        			'internal_name' =>      array('type' => self::TYPE_STRING, 'required' => true, 'size' => 100),            
                    'position' => 	        array('type' => self::TYPE_INT, 'required' => true),
                    'instant_search' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
                    'hide_filter_category' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false),
                    'hide_filter_manufacturer' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false),
                    'hide_filter_supplier' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false),
                    'type_filter_category' =>     array('type' => self::TYPE_INT, 'required' => false),            
                    'filter_categories' =>  array('type' => self::TYPE_STRING, 'required' => false, 'size' => 500),
                    'active' => 	        array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
                    
                    // Lang fields
        			'name' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 200)
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
            $fields['id_searcher'] = (int) ($this->id);
        $fields['internal_name'] = $this->internal_name;
        $fields['position'] = $this->position;
        $fields['instant_search'] = $this->instant_search;
        $fields['hide_filter_category'] = $this->hide_filter_category;        
        $fields['hide_filter_manufacturer'] = $this->hide_filter_manufacturer;        
        $fields['hide_filter_supplier'] = $this->hide_filter_supplier;        
        $fields['type_filter_category'] = (int)$this->type_filter_category;
        $fields['filter_categories'] = pSQL($this->filter_categories);        
        $fields['active'] = $this->active;
        return $fields;
    }
    
    public static function getSearchers($id_lang, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;
                                                
        return Db::getInstance()->ExecuteS("
            SELECT
                s.*,
                sl.name
            FROM 
                " . _DB_PREFIX_ . "fpp_searcher AS s,
                " . _DB_PREFIX_ . "fpp_searcher_lang AS sl
            WHERE 
                sl.id_searcher = s.id_searcher
                AND sl.id_lang = " . (int)$id_lang . "
                AND (s.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
        ");
    }
    
    public static function getSearchersByPosition($positions, $id_lang, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;
        
        $list_positions = "";
        foreach ($positions as $position) {
            $list_positions .= "'" . $position . "',";
        }
        $list_positions = strlen($list_positions) > 0 ? substr($list_positions, 0, -1) : $list_positions;
                           
        return Db::getInstance()->ExecuteS("
            SELECT
                s.*,
                sl.name
            FROM 
                " . _DB_PREFIX_ . "fpp_searcher AS s,
                " . _DB_PREFIX_ . "fpp_searcher_lang AS sl
            WHERE 
                sl.id_searcher = s.id_searcher
                AND sl.id_lang = " . (int)$id_lang . "
                AND s.position IN (" . $list_positions . ")
                AND (s.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
        ");
    }

    /**
     * Check then return multilingual fields for database interaction
     *
     * @return array Multilingual fields
     */
    public function getTranslationsFieldsChild() {
        parent::validateFieldsLang();

        $fieldsArray = array('name');
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
    
    public function delete(){
        if(parent::delete()){
            $filters = Db::getInstance()->ExecuteS("
                SELECT id_filter
                FROM " . _DB_PREFIX_ . "fpp_filter
                WHERE id_searcher = " . $this->id ."
            ");            
            
            foreach ($filters as $filter) {
                $FilterClass = new FilterClass($filter['id_filter']);                      
                if(Validate::isLoadedObject($FilterClass) && !$FilterClass->delete())
                    return FALSE;
            }
            
            return TRUE;
        }
        else
            return FALSE;
    }
}

?>