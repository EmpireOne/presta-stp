<?php

/**
 * @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
 */

class FilterClass extends ObjectModel {
    public $id;
    public $id_searcher;
    public $type;
    public $name;
    public $multi_options;
    public $position;
    public $criterion;
    public $level_depth;
    public $id_parent;
    public $num_columns;
    public $search_ps;
    public $active;
    protected $table = 'fpp_filter';
    protected $identifier = 'id_filter';
    protected $tables = array('fpp_filter', 'fpp_filter_lang');
    protected $fieldsRequired = array('id_searcher', 'type', 'multi_options', 'position', 'criterion', 'search_ps');
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
        		'table' => 'fpp_filter',
        		'primary' => 'id_filter',
        		'multilang' => true,
        		'multilang_shop' => false,
        		'fields' => array(
                    'id_searcher' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
                    'type' =>               array('type' => self::TYPE_STRING, 'required' => true),
                    'multi_options' =>      array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
                    'position' => 	        array('type' => self::TYPE_INT, 'required' => true),
                    'criterion' =>          array('type' => self::TYPE_STRING, 'required' => true),
                    'level_depth' =>        array('type' => self::TYPE_INT, 'required' => false),
                    'id_parent' =>          array('type' => self::TYPE_INT, 'required' => false),
                    'num_columns' =>        array('type' => self::TYPE_INT, 'required' => false),
                    'search_ps' =>          array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
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
            $fields['id_filter'] = (int) ($this->id);
        $fields['id_searcher'] = (int) ($this->id_searcher);
        $fields['type'] = $this->type;
        $fields['multi_options'] = $this->multi_options;
        $fields['position'] = $this->position;
        $fields['criterion'] = $this->criterion;
        $fields['level_depth'] = (int) ($this->level_depth);
        $fields['id_parent'] = (int) ($this->id_parent);
        $fields['num_columns'] = (int) ($this->num_columns);
        $fields['search_ps'] = (int) ($this->search_ps);
        $fields['active'] = $this->active;
        return $fields;
    }
    
    public static function getFilters($id_lang, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;
        
        return Db::getInstance()->ExecuteS("
            SELECT
                f.*,
                fl.name
            FROM 
                " . _DB_PREFIX_ . "fpp_filter AS f,
                " . _DB_PREFIX_ . "fpp_filter_lang AS fl
            WHERE 
                f.id_filter = fl.id_filter
                AND fl.id_lang = " . (int)$id_lang . "
                AND (f.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
            ORDER BY
                f.position
        ");
    }
    
    public static function getFiltersByCriterion($id_lang, $criterion, $level_depth = NULL, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;

        return Db::getInstance()->ExecuteS("
            SELECT
                f.*,
                fl.name
            FROM 
                " . _DB_PREFIX_ . "fpp_filter AS f,
                " . _DB_PREFIX_ . "fpp_filter_lang AS fl
            WHERE 
                f.id_filter = fl.id_filter
                AND f.criterion = '" . $criterion . "'
                " . (!is_null($level_depth) ?  "AND f.level_depth = " . (int)$level_depth : "") . "
                AND fl.id_lang = " . (int)$id_lang . "
                AND (f.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
            ORDER BY
                f.position
        ");
    }

    public static function getLevelsDepthCategory(){
        return Db::getInstance()->ExecuteS("
            SELECT
                DISTINCT(level_depth) AS level_depth
            FROM
                " . _DB_PREFIX_ . "category
            WHERE 
                level_depth > 0
            ORDER BY
                level_depth
        ");
    }
    
    public static function getCategoriesByLevelDepth($id_lang, $level_depth, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;
                                                
        return Db::getInstance()->ExecuteS("
            SELECT
                c.*,
                cl.name
            FROM
                " . _DB_PREFIX_ . "category AS c,
                " . _DB_PREFIX_ . "category_lang AS cl
            WHERE
                cl.id_category = c.id_category
                AND cl.id_lang = " . (int)$id_lang . "
                AND c.level_depth = " . (int)$level_depth . "
                AND (c.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
            ORDER BY
                name
        ");
    }

    public static function getFiltersListBySearcher($id_lang, $id_searcher, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;
                                                
        return Db::getInstance()->ExecuteS("
            SELECT
                f.*,
                fl.name
            FROM 
                " . _DB_PREFIX_ . "fpp_filter AS f,
                " . _DB_PREFIX_ . "fpp_filter_lang AS fl
            WHERE 
                f.id_filter = fl.id_filter
                AND f.id_searcher = " . (int)$id_searcher . "
                AND fl.id_lang = " . (int)$id_lang . "
                AND (f.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
            ORDER BY
                f.position
        ");
    }
    
    public static function deleteOptionsByFilter($id_filter){
        return Db::getInstance()->Execute("
            DELETE FROM 
                " . _DB_PREFIX_ . "fpp_option
            WHERE 
                id_filter = " . (int)$id_filter . "
        ");
    }
    
    public static function deleteIndexProductByFilter($id_filter){
        return Db::getInstance()->Execute("
            DELETE FROM
                " . _DB_PREFIX_ . "fpp_index_product
            WHERE
                id_filter = " . (int)$id_filter . "
        ");
    }

    public static function getColumns($id_filter){
        return Db::getInstance()->ExecuteS("
            SELECT
                id_column, position
            FROM 
                " . _DB_PREFIX_ . "fpp_column
            WHERE 
                id_filter = " . (int)$id_filter . "
            ORDER BY
                position
        ");
    }
    
    private static function deleteRecursiveDependencies($id_filter){
        $filter = Db::getInstance()->ExecuteS("
            SELECT id_filter FROM " . _DB_PREFIX_ . "fpp_filter WHERE id_parent = " . (int)$id_filter. "
        ");
                
        if($filter){
            foreach ($filter as $f) {
                if(!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_dependency_option
                    WHERE 
                        id_filter = " . $f['id_filter'] ."
                        OR id_filter_parent = " . $f['id_filter'] ."
                "))
                    return FALSE;
                
                if(Db::getInstance()->Execute("
                    UPDATE " . _DB_PREFIX_ . "fpp_filter
                    SET id_parent = 0
                    WHERE id_filter = " . $f['id_filter'] ."
                "))
                    self::deleteRecursiveDependencies($f['id_filter']);
            }
        }
    }
    
    public static function verifyHasOption($id_filter, $id_option_criterion){
        $option = Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option
            WHERE 
                id_filter = " . (int)$id_filter . "
                AND id_option_criterion = " . (int)$id_option_criterion . "
        ");
        
        return (is_array($option) && sizeof($option) ? TRUE : FALSE);
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
            if(!Db::getInstance()->Execute("
                DELETE FROM " . _DB_PREFIX_ . "fpp_filter_category
                WHERE id_filter = " . $this->id ."
            "))
                return FALSE;
            
            $options = Db::getInstance()->ExecuteS("
                SELECT id_option
                FROM " . _DB_PREFIX_ . "fpp_option
                WHERE id_filter = " . $this->id ."
            ");
            
            foreach ($options as $option) {
                if(!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_index_product
                    WHERE id_option = " . $option['id_option'] ."
                "))
                    return FALSE;
            }
            
            if(!Db::getInstance()->Execute("
                DELETE FROM " . _DB_PREFIX_ . "fpp_option
                WHERE id_filter = " . $this->id ."
            "))
                return FALSE;
                
            $columns = Db::getInstance()->ExecuteS("
                SELECT id_column
                FROM " . _DB_PREFIX_ . "fpp_column
                WHERE id_filter = " . $this->id ."
            ");

            foreach ($columns as $column) {
                if(!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_column_lang
                    WHERE id_column = " . $column['id_column'] ."
                "))
                    return FALSE;
                
                if(!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_column_option
                    WHERE id_column = " . $column['id_column'] ."
                "))
                    return FALSE;
            }

            if(!Db::getInstance()->Execute("
                DELETE FROM " . _DB_PREFIX_ . "fpp_column
                WHERE id_filter = " . $this->id ."
            "))
                return FALSE;
            
            if(!Db::getInstance()->Execute("
                DELETE FROM " . _DB_PREFIX_ . "fpp_dependency_option
                WHERE 
                    id_filter = " . $this->id ."
                    OR id_filter_parent = " . $this->id ."
            "))
                return FALSE;
            
            self::deleteRecursiveDependencies($this->id);
            
            return TRUE;
        }
        else
            return FALSE;
    }
}

?>