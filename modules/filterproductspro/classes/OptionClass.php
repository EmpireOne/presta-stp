<?php

/**
 * @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
 */

class OptionClass extends ObjectModel {
    public $id;
    public $id_filter;
    public $position;
    public $active;
    public $id_option_criterion;
    protected $table = 'fpp_option';
    protected $identifier = 'id_option';
    protected $tables = array('fpp_option');
    protected $fieldsRequired = array('id_filter', 'position');
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array();
    protected $fieldsSizeLang = array();
    protected $fieldsValidateLang = array();
    
    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	public	function __construct($id = null, $id_lang = null, $id_shop = null)
	{		        
        if(version_compare(_PS_VERSION_, '1.5') >= 0){                        
            self::$definition = array(
        		'table' => 'fpp_option',
        		'primary' => 'id_option',
        		'multilang' => false,
        		'multilang_shop' => false,
        		'fields' => array(
        			'id_filter' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
        		    'position' => 	        array('type' => self::TYPE_INT, 'required' => true),
                    'active' => 	        array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
                    'id_option_criterion' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false)
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
            $fields['id_option'] = (int) ($this->id);
        $fields['id_filter'] = (int) ($this->id_filter);
        $fields['position'] = $this->position;
        $fields['active'] = $this->active;
        $fields['id_option_criterion'] = (int) ($this->id_option_criterion);
        return $fields;
    }
    
    public static function getOptionListByFilter($id_lang, $id_filter, $active = NULL, $count = false){
        $active = is_null($active) ? -999999 : (int)$active;

        if ($count)
            return Db::getInstance()->getValue("
                SELECT
                    count(o.id_option)
                FROM 
                    " . _DB_PREFIX_ . "fpp_option AS o,
                    " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
                WHERE
                    o.id_option_criterion = ocl.id_option_criterion
                    AND o.id_filter = " . (int)$id_filter . "
                    AND ocl.id_lang = " . (int)$id_lang . "
                    AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
            ");        
        
        $limit = 40;
        $page = Tools::getValue('page', 0);
        $star = ($page == 0 ? 0 : $page * $limit);          
                                   
        return Db::getInstance()->ExecuteS("
            SELECT
                o.*,
                ocl.value
            FROM 
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
            WHERE
                o.id_option_criterion = ocl.id_option_criterion
                AND o.id_filter = " . (int)$id_filter . "
                AND ocl.id_lang = " . (int)$id_lang . "
                AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
            ORDER BY
                o.position
            " . (isset($_POST['page']) ? "LIMIT ". $star .", ". $limit : "" ) . "
        ");
    }
        
    public static function getOptionListByFilterDependency($id_lang, $id_filter, $active = NULL, $count = false, $show_option_without_dependency = false){
        $active = is_null($active) ? -999999 : (int)$active;
        
        $FilterClass = new FilterClass($id_filter);
        
        $options = array();
        if (Validate::isLoadedObject($FilterClass)){
                        
            if ($count)
                return Db::getInstance()->getValue("
                            SELECT 
                                count(*) 
                            FROM 
                                " . _DB_PREFIX_ . "fpp_dependency_option 
                            WHERE 
                                id_filter = " . (int)$id_filter);
            
            $limit = 40;
            $page = Tools::getValue('page', 0);
            $star = ($page == 0 ? 0 : $page * $limit); 
                                
            //añadimos las opciones que no tienes dependencias, para que puedan ser borradas luego.
            //-------------------------------------------------------------------------------------
            if ($page == 0 && $show_option_without_dependency){
                $ids_options = array();
                $dependency_options = Db::getInstance()->ExecuteS("
                    SELECT 
                        * 
                    FROM 
                        " . _DB_PREFIX_ . "fpp_dependency_option 
                    WHERE 
                        id_filter = " . (int)$id_filter);
                    
                foreach($dependency_options AS $dependency_option){
                    $ids_option = $dependency_option['ids_option'];
                    $arr_ids_option = explode(',', $ids_option);
                    
                    array_push($ids_options, (int)$arr_ids_option[$FilterClass->level_depth]);
                }
                $options_without_dependency = Db::getInstance()->ExecuteS("
                        SELECT
                            o.*,
                            ocl.value
                        FROM 
                            " . _DB_PREFIX_ . "fpp_option AS o,
                            " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
                        WHERE
                            o.id_option_criterion = ocl.id_option_criterion                        
                            AND ocl.id_lang = " . (int)$id_lang . "
                            AND o.id_option NOT IN (" . implode(',', $ids_options) . ")
                            AND o.id_filter = " . (int)$id_filter . "
                            AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
                    ");
                    
                if (is_array($options_without_dependency))
                    foreach($options_without_dependency AS $option){
                        array_push($options, $option);
                    }
            }
            //-------------------------------------------------------------------------------------                                            
        
            $dependency_options = Db::getInstance()->ExecuteS("
                SELECT 
                    * 
                FROM 
                    " . _DB_PREFIX_ . "fpp_dependency_option 
                WHERE 
                    id_filter = " . (int)$id_filter . "
                ORDER BY
                    ids_option
                " . (isset($_POST['page']) ? "LIMIT ". $star .", ". $limit : "" ));
                    
            $i = sizeof($options);
            foreach($dependency_options AS $dependency_option){                
                $ids_option = $dependency_option['ids_option'];
                $arr_ids_option = explode(',', $ids_option);
                $str_dependency = '';
                
                $x=0;
                foreach($arr_ids_option AS $id_option){
                    $value_option = Db::getInstance()->getValue("
                        SELECT
                            ocl.value
                        FROM 
                            " . _DB_PREFIX_ . "fpp_option AS o,
                            " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
                        WHERE
                            o.id_option_criterion = ocl.id_option_criterion                        
                            AND ocl.id_lang = " . (int)$id_lang . "
                            AND o.id_option = " . $id_option . "
                            AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
                    ");
                    
                    if ($x <= $FilterClass->level_depth){
                        if (!empty($str_dependency))
                            $str_dependency .= ' > ';
                        
                        $str_dependency .= $value_option;
                    }
                    
                    $x++;
                }
                
                $_option = Db::getInstance()->getRow("
                    SELECT
                        o.*,
                        ocl.value
                    FROM 
                        " . _DB_PREFIX_ . "fpp_option AS o,
                        " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
                    WHERE
                        o.id_option_criterion = ocl.id_option_criterion                        
                        AND ocl.id_lang = " . (int)$id_lang . "
                        AND o.id_option = " . (int)$arr_ids_option[$FilterClass->level_depth] . "
                        AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
                ");
                if ($_option){
                    $options[$i] = $_option;
                    $options[$i]['id_dependency_option'] = $dependency_option['id_dependency_option'];
                    $options[$i]['ids_dependency'] = $ids_option;
                    $options[$i]['str_dependency'] = $str_dependency;
                }
                  
                $i++;  
            }                        
        }
        
        return $options;
    }

    public static function getOptionListByFilterAndCheckIndexProducts($id_lang, $id_filter, $active = NULL, $check_index_product = TRUE){
        global $FilterProducts;
        $active = is_null($active) ? -999999 : (int)$active;
                                                                      
        return Db::getInstance()->ExecuteS("
            SELECT
                o.*,
                ocl.value
            FROM 
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
            WHERE
                o.id_option_criterion = ocl.id_option_criterion
                AND o.id_filter = " . (int)$id_filter . "
                AND ocl.id_lang = " . (int)$id_lang . "
                AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)                
                AND o.id_option " . (!$check_index_product ? "NOT" : "" ) . " IN (
                    SELECT DISTINCT(id_option) FROM " . _DB_PREFIX_ . "fpp_index_product
                    " . (!$check_index_product ? "" : "
                        UNION
			SELECT id_option 
			FROM 
                            " . _DB_PREFIX_ . "fpp_filter AS f,
                            " . _DB_PREFIX_ . "fpp_option AS o
			WHERE
                            o.id_filter = f.id_filter
                            AND f.id_filter = " . (int)$id_filter . "
                            AND f.criterion = '" . $FilterProducts->Criterions->Custom . "'") . "
                )    
            ORDER BY
                o.position
        ");
    }
    
    public static function getOptionById($id_lang, $id_option, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;
        
        return Db::getInstance()->ExecuteS("
            SELECT
                o.*,
                ocl.value
            FROM 
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
            WHERE
                o.id_option_criterion = ocl.id_option_criterion
                AND ocl.id_lang = " . (int)$id_lang . "
                AND o.id_option = " . (int)$id_option . "
                AND (o.active = " . (is_null($active) ? 'active' : $active) . " OR " . $active ."= -999999)
        ");
    }
    
    public static function getColorByOption($id_option){
        global $FilterProducts;
        $color = NULL;
        
        $option = Db::getInstance()->ExecuteS("
            SELECT
                a.color
            FROM
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_option_criterion AS oc,
                " . _DB_PREFIX_ . "attribute_group AS ag,
                " . _DB_PREFIX_ . "attribute AS a
            WHERE
                oc.id_option_criterion = o.id_option_criterion
                AND ag.id_attribute_group = oc.level_depth
                AND a.id_attribute = oc.id_table
                AND a.id_attribute_group = ag.id_attribute_group
                AND ag.is_color_group = TRUE
                AND oc.criterion = '" . $FilterProducts->Criterions->Attribute . "'
                AND o.id_option = " . (int)$id_option . "
        ");
                
        if($option)
            $color = (isset($option[0]['color']) && !empty($option[0]['color'])) ? $option[0]['color'] : $color;
        
        return $color;
    }

    public static function deleteOptionsByIdOptionCriterion($id_option_criterion){
        $options = Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option
            WHERE
                id_option_criterion = " . $id_option_criterion ."
        ");
        
        foreach ($options as $option) {
            if(!Db::getInstance()->Execute("
                DELETE FROM " . _DB_PREFIX_ . "fpp_index_product
                WHERE
                    id_option = " . $option['id_option'] ."
            "))
                return FALSE;
        }
        
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_option
            WHERE
                id_option_criterion = " . $id_option_criterion ."
        ");
    }
    
    public static function getOptionsByIdOptionCriterion($id_option_criterion){
        return Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option
            WHERE
                id_option_criterion = " . $id_option_criterion ."
        ");
    }
    
    public static function getOptionsByIdOptionCriterionAndFilter($id_option_criterion, $id_filter){
        return Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option
            WHERE
                id_option_criterion = " . $id_option_criterion ."
                AND id_filter = " . $id_filter . "
        ");
    }
    
    public static function getIdsProductsFromIndexByOptions($options, $id_searcher){
        global $FilterProducts;
        
        //TRAE TODAS LAS OPCIONES PERSONALIZADAS.
        //------------------------------------------------------------------------------------
        $options_custom_availables = array();

        $_options_custom_availables = Db::getInstance()->ExecuteS("
            SELECT
                o.id_option
            FROM
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_searcher AS s,
                " . _DB_PREFIX_ . "fpp_filter AS f
            WHERE
                s.id_searcher = f.id_searcher
                AND f.id_filter = o.id_filter
                AND f.criterion = '" . $FilterProducts->Criterions->Custom . "'
                AND s.id_searcher = " . $id_searcher . "
                AND f.search_ps = FALSE
        ");
        
        foreach ($_options_custom_availables as $option) {
            array_push($options_custom_availables, $option['id_option']);
        }
        //------------------------------------------------------------------------------------  
        $options_custom = array();
        
        //Eliminar de las opciones enviadas aquellas que sean customizadas de las opciones enviadas y asigna un nuevo arreglo con solo las personalizadas.
        foreach($options as $it => $option){
            if(in_array($option, $options_custom_availables)){
                array_push($options_custom, $option);
                
                unset($options[$it]);
            }
        }
        
        $_products_normal = array();
        if (sizeof($options))
            $_products_normal = Db::getInstance()->ExecuteS("
                SELECT
                    id_product
                FROM 
					" . _DB_PREFIX_ . "fpp_filter AS f,
                    " . _DB_PREFIX_ . "fpp_index_product AS ip
                WHERE 
					f.id_filter = ip.id_filter                            
					AND f.search_ps = FALSE
					AND f.criterion <> '" . $FilterProducts->Criterions->Custom . "'
                    AND ip.id_option IN(" . implode(',', $options) .")
                GROUP BY id_product
                HAVING COUNT( * ) = " . sizeof($options)
            );     
                 
        $_products_custom = array();
        if(sizeof($options_custom)) 
            $_products_custom = Db::getInstance()->ExecuteS("
                SELECT 
                    id_product
                FROM
                    " . _DB_PREFIX_ . "fpp_index_product AS ip,
                    " . _DB_PREFIX_ . "fpp_dependency_option AS do
                WHERE
                    do.id_dependency_option = ip.id_dependency_option AND
                    do.ids_option = '" . implode(',', $options_custom) . "'
            ");
        
        $products = array();
        if ($_products_normal && $_products_custom){//productos normales y custom        
            foreach($_products_normal AS $product_normal){                
                foreach($_products_custom AS $product_custom){
                    if ($product_custom['id_product'] == $product_normal['id_product'])
                        array_push($products, $product_normal['id_product']);
                }
            }
        }elseif ($_products_normal && !$_products_custom){//productos normales        
            foreach($_products_normal AS $product_normal){                
                array_push($products, $product_normal['id_product']);                
            }
        }elseif (!$_products_normal && $_products_custom){//productos custom        
            foreach($_products_custom AS $product_custom){                
                array_push($products, $product_custom['id_product']);                
            }
        }
        
        return $products;
    }
    
    public static function getUnavailableOptionsByOptions($options, $id_searcher, $id_filter = ''){ 
        global $FilterProducts;
        
        //TRAE TODAS LAS OPCIONES PERSONALIZADAS.
        //------------------------------------------------------------------------------------
        $options_custom_availables = array();

        $_options_custom_availables = Db::getInstance()->ExecuteS("
            SELECT
                o.id_option
            FROM
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_searcher AS s,
                " . _DB_PREFIX_ . "fpp_filter AS f
            WHERE
                s.id_searcher = f.id_searcher
                AND f.id_filter = o.id_filter
                AND f.criterion = '" . $FilterProducts->Criterions->Custom . "'
                AND s.id_searcher = " . $id_searcher . "
                AND f.search_ps = FALSE
        ");
        
        foreach ($_options_custom_availables as $option) {
            array_push($options_custom_availables, $option['id_option']);
        }
        //------------------------------------------------------------------------------------  
        
        //Eliminar de las opciones enviadas aquellas que sean customizadas, sin productos y cuyo filtro no busque en el motor de PS
        foreach($options as $it => $option){
            if(in_array($option, $options_custom_availables))
                unset($options[$it]);
        }
        
        //Obtiene los productos del filtro ya sea categoria, fabricante o proveedor y lo coloca en la consulta que trae las opciones,
        //para que solo devuelva las opciones segun los productos de la pagina en donde esta ubicado el cliente.
        //-----------------------------------------------------------------------
        $ids_products = array();
        $searcher = new SearcherClass($id_searcher);
        
        if(Validate::isLoadedObject($searcher) && $searcher->hide_filter_category && Tools::isSubmit('id_category')){            
            $_ids_products_category = Db::getInstance()->ExecuteS("
                SELECT id_product
                FROM " . _DB_PREFIX_ . "category_product
                WHERE id_category = " . (int)Tools::getValue('id_category') . "
            ");
            
            foreach ($_ids_products_category as $data) {
                $ids_products[] = $data['id_product'];
            }
        }
        if(Validate::isLoadedObject($searcher) && $searcher->hide_filter_manufacturer && Tools::isSubmit('id_manufacturer')){
            $_ids_products_manufacturer = Db::getInstance()->ExecuteS("
                SELECT id_product
                FROM " . _DB_PREFIX_ . "product
                WHERE id_manufacturer = " . (int)Tools::getValue('id_manufacturer') . "
            ");

            foreach ($_ids_products_manufacturer as $data) {
                $ids_products[] = $data['id_product'];
            }
        }
        if(Validate::isLoadedObject($searcher) && $searcher->hide_filter_supplier && Tools::isSubmit('id_supplier')){
            $_ids_products_supplier = Db::getInstance()->ExecuteS("
                SELECT id_product
                FROM " . _DB_PREFIX_ . (version_compare(_PS_VERSION_, '1.5') >= 0 ? 'product_supplier' : 'product') ."
                WHERE id_supplier = " . (int)Tools::getValue('id_supplier') . "
            ");

            foreach ($_ids_products_supplier as $data) {
                $ids_products[] = $data['id_product'];
            }
        }
        //-----------------------------------------------------------------------

        $all_options = Db::getInstance()->ExecuteS("
            SELECT 
                ip.*
            FROM
                " . _DB_PREFIX_ . "fpp_index_product AS ip,
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_filter AS f
            WHERE 
                ip.id_option = o.id_option
                AND o.id_filter = f.id_filter
                AND f.id_searcher = " . $id_searcher . "           
                AND f.criterion <> '" . $FilterProducts->Criterions->Custom . "'
                ".(sizeof($ids_products) ? ' AND ip.id_product IN (' . implode(',', $ids_products) . ')' : '' ));//filtra opciones segun la pagina donde este el cliente.
   
        $index_products = array();
        
        foreach ($all_options as $option) {
            $index_products[$option['id_product']][] = (string)$option['id_option'];
        }
                                         
        $_available_options = array();
                
        foreach ($index_products as $id_product => $_options) {
            $valid = 0;
            foreach($options AS $id_option){   
                foreach ($_options AS $i => $_id_option){
                    if ($id_option == $_id_option){                        
                        $valid += 1;
                    }                    
                }
            }
            if ($valid == sizeof($options)/* && $valid != sizeof($_options)*/)
                $_available_options[] = $_options;
        }
                                              
        $available_options = array();
                
        foreach ($_available_options as $option) {
            foreach ($option as $opt) {
                if(!in_array($opt, $available_options))
                    $available_options[] = $opt;
            }
        }
                
        //Incluir en arreglo de opciones validas, las opciones customizadas consultadas anteriormente
        $available_options = array_merge($available_options, $options_custom_availables);
                        
        if(!sizeof($available_options))
            return array();
        
        $unavailable_options = Db::getInstance()->ExecuteS("
            SELECT 
                DISTINCT(o.id_option), f.id_filter, f.type
            FROM
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_filter AS f
            WHERE
                o.id_filter = f.id_filter
                AND f.id_searcher = " . $id_searcher . "                
                AND o.id_option NOT IN(" . implode(',', $available_options) . ")
        ");
        
        $_unavailable_options = array();   
        foreach($unavailable_options AS $option){            
            $_unavailable_options[$option['id_filter']]['type'] = $option['type'];
            $_unavailable_options[$option['id_filter']]['options'][] = (int)$option['id_option'];
        }
        
        $available_options_select = Db::getInstance()->ExecuteS("
            SELECT 
                DISTINCT(o.id_option), f.id_filter, f.type
            FROM
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_filter AS f
            WHERE
                o.id_filter = f.id_filter
                AND f.id_searcher = " . $id_searcher . "
                AND f.type = 'select'
                AND o.id_option IN(" . implode(',', $available_options) . ")
                AND f.criterion <> '" . $FilterProducts->Criterions->Custom . "'
            ORDER BY o.position
        ");
        
        foreach($available_options_select AS $option){                        
            $_unavailable_options[$option['id_filter']]['options_select'][] = (int)$option['id_option'];
        }
                        
        return $_unavailable_options;
    }
    
    public function delete($delete_criterion = FALSE){
        $OptionCriterionClass = new OptionCriterionClass($this->id_option_criterion);
        if(parent::delete()){
            $dependency_options = Db::getInstance()->ExecuteS('
                SELECT * FROM '. _DB_PREFIX_ . 'fpp_index_product
                    WHERE 
                        id_option = '.(int)$this->id.'
            ');
            
            foreach($dependency_options AS $dependency_option){
                if (empty($dependency_option['id_dependency_option']))
                    continue;
                    
                if(!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_dependency_option
                        WHERE 
                            id_dependency_option = " . $dependency_option['id_dependency_option']."
                "))
                    return FALSE;
            }
            
            if(!Db::getInstance()->Execute("
                DELETE FROM " . _DB_PREFIX_ . "fpp_index_product
                    WHERE 
                        id_option = " . $this->id ."
            "))
                return FALSE;
            
            if(!ColumnOptionClass::deletePositionByOption($this->id))
                return FALSE;
            
            if($delete_criterion)
                if(!$OptionCriterionClass->delete())
                    return FALSE;
                
            return TRUE;
        }
        else
            return FALSE;
    }
}
?>