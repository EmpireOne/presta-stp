<?php


require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__)."/filterproductspro.php");

global $cookie;

if (isset($_POST['action'])){
    $action = $_POST['action'];
    
    $FilterProductsPro = new FilterProductsPro();
    switch ($action){
        //BACK_OFFICE
        //Actiones para Searcher
        case "updateSearcher":          
            if (isset($_POST['id_searcher']) AND isset($_POST['internal_name']) AND isset($_POST['public_names']) AND isset($_POST['position']) AND isset($_POST['active']) AND isset($_POST['instant_search']) AND isset($_POST['hide_filter_category'])){
                $id_searcher = $_POST['id_searcher'];
                $internal_name = $_POST['internal_name'];
                $public_names = $_POST['public_names'];
                $position = $_POST['position'];        
                $instant_search = $_POST['instant_search'];
                $hide_filter_category = $_POST['hide_filter_category'];
                $hide_filter_manufacturer = $_POST['hide_filter_manufacturer'];
                $hide_filter_supplier = $_POST['hide_filter_supplier'];
                $type_filter_category = $_POST['type_filter_category'];
                $filter_categories = $_POST['filter_categories'];
                $active = (int)$_POST['active']; 
                                    
                echo $FilterProductsPro->updateSearcher($id_searcher, $internal_name, $public_names, $position, $instant_search, $hide_filter_category, 
                        $hide_filter_manufacturer, $hide_filter_supplier, $type_filter_category, $filter_categories, $active);
            }      
        break; 
        case "deleteSearcher":
            if (isset($_POST['id_searcher'])){
                $id_searcher = $_POST['id_searcher'];
                
                echo $FilterProductsPro->deleteSearcher($id_searcher);
            }
        break;
        case "loadSearcher":
            if (isset($_POST['id_searcher'])){
                $id_searcher = $_POST['id_searcher'];
                
                echo $FilterProductsPro->loadSearcher($id_searcher);
            }
        break;
        case "getSearchersList":
            echo $FilterProductsPro->getSearchersList();
        break;
        //Acciones para Filtros y Opciones
        case "sortOptionsFilter":
            echo $FilterProductsPro->jsonEncode($FilterProductsPro->sortOptionsFilter(Tools::getValue('id_filter'), Tools::getValue('sort_asc')));
            break;
        case "updateFilter":          
            if (isset($_POST['id_filter']) AND isset($_POST['id_searcher']) AND isset($_POST['names']) AND isset($_POST['criterion']) 
                AND isset($_POST['type']) AND isset($_POST['multi_option']) AND isset($_POST['level_depth']) AND isset($_POST['num_columns']) 
                AND isset($_POST['categories_selected']) AND isset($_POST['id_filter_custom_clone'])
            ){
                $id_filter = $_POST['id_filter'];
                $id_searcher = $_POST['id_searcher'];
                $names = $_POST['names'];
                $criterion = $_POST['criterion'];
                $type = $_POST['type'];                    
                $multi_option = (int)$_POST['multi_option'];                      
                $level_depth = (int)$_POST['level_depth'];
                $id_parent = isset($_POST['id_parent']) ? (int)$_POST['id_parent'] : 0;
                $num_columns = (int)$_POST['num_columns'];
                $search_ps = (int)$_POST['search_ps'];
                $categories_selected = $_POST['categories_selected'];
                $categories_selected = explode(',', $categories_selected);
                $id_filter_custom_clone = (int)$_POST['id_filter_custom_clone'];
                                                    
                echo $FilterProductsPro->updateFilter($id_filter, $id_searcher, $names, $criterion, $type, $multi_option, $level_depth, $num_columns, $search_ps, $categories_selected, 0, 0, 1,  $id_filter_custom_clone);
            }      
        break;  
        case "getFiltersListBySearcher":
            if (isset($_POST['id_searcher'])){
                $id_searcher = $_POST['id_searcher'];
                
                echo $FilterProductsPro->getFiltersListBySearcher($id_searcher);
            }
        break;
        case "updateFiltersPosition":
            if (isset($_POST['order_filters'])){
                $order_filters = $_POST['order_filters'];
                
                echo $FilterProductsPro->updateFiltersPosition($order_filters);
            }
        break;
        case "loadFilter":
            if (isset($_POST['id_filter'])){
                $id_filter = $_POST['id_filter'];
                
                echo $FilterProductsPro->loadFilter($id_filter);
            }
        break; 
        case "deleteFilter":
            if (isset($_POST['id_filter'])){
                $id_filter = $_POST['id_filter'];
                
                echo $FilterProductsPro->deleteFilter($id_filter);
            } 
        case "activeFilter":
            if (isset($_POST['id_filter']) AND isset($_POST['active'])){
                $id_filter = $_POST['id_filter'];
                $active = (int)$_POST['active'];
                
                echo $FilterProductsPro->activeFilter($id_filter, $active);
            }
        break;
        case "getOptionsByFilter":
            if (isset($_POST['id_filter'])){
                $id_filter = $_POST['id_filter'];
                
                echo $FilterProductsPro->getOptionsByFilter($id_filter);
            }
        break; 
        case "activeOption":
            if (isset($_POST['id_option']) AND isset($_POST['active'])){
                $id_option = $_POST['id_option'];
                $active = (int)$_POST['active'];
                
                echo $FilterProductsPro->activeOption($id_option, $active);
            }
        break;
        case "updateOptionsPosition":
            if (isset($_POST['order_options'])){
                $order_options = $_POST['order_options'];
                
                echo $FilterProductsPro->updateOptionsPosition($order_options);
            }
        break;
        case "updateOptionsColumnPosition":
            if (isset($_POST['order_options_column']) AND isset($_POST['id_col'])){
                $order_options_column = $_POST['order_options_column'];
                $id_col = $_POST['id_col'];
                
                echo $FilterProductsPro->updateOptionsColumnPosition($order_options_column, $id_col);
            }
        break;
        case "updateValuesColumn":
            if (isset($_POST['values']) AND isset($_POST['id_col'])){
                $values = $_POST['values'];
                $id_col = $_POST['id_col'];
                
                echo $FilterProductsPro->updateValuesColumn($id_col, $values);
            }
        break;
        case "reindexByFilter":            
            if (isset($_POST['id_filter'])){
                $id_filter = $_POST['id_filter'];
                
                echo $FilterProductsPro->reindexByFilter($id_filter);
            }
        break;
        case "getUnavailableOptionsByOptions":
            if(isset($_POST['options']) && isset($_POST['id_searcher'])){
                $options = Tools::getValue('options');
                $id_searcher = (int)Tools::getValue('id_searcher');
                
                echo $FilterProductsPro->getUnavailableOptionsByOptions($options, $id_searcher);
            }
        break;
        case "getCategoriesByLevelDepth":            
            if (isset($_POST['level_depth'])){
                $level_depth = (int)$_POST['level_depth'];
                
                echo $FilterProductsPro->getCategoriesByLevelDepth($level_depth);
            }
        break;
        case "getValuesByFeature":            
            if (isset($_POST['id_feature'])){
                $id_feature = (int)$_POST['id_feature'];
                
                echo $FilterProductsPro->getValuesByFeature($id_feature);
            }
        break;        
        case "getAttributesByAttributeGroup":            
            if (isset($_POST['id_attr_group'])){
                $id_attr_group = (int)$_POST['id_attr_group'];
                
                echo $FilterProductsPro->getAttributesByAttributeGroup($id_attr_group);
            }
        break;
        //Acciones para Herramientas        
        case "reindexCategories":
            echo $FilterProductsPro->reindexCategories();
        break;       
        case "reindexProducts":
            echo $FilterProductsPro->reindexProducts();
        break;
        case "saveConfiguration":
            if(isset($_POST['show_button_back_filters'])){
                $show_button_back_filters = (int)$_POST['show_button_back_filters'];
                $show_button_expand_options = (int)$_POST['show_button_expand_options'];
                $show_only_products_stock = (int)$_POST['show_only_products_stock'];
                $id_content_results = $_POST['id_content_results'];
                
                echo $FilterProductsPro->saveConfiguration($show_button_back_filters, $show_button_expand_options, $show_only_products_stock, $id_content_results);
            }
        break;
        //Acciones para Dependencia de Filtros         
        case "getDependenciesFilters":
            if (isset($_POST['id_searcher'])){
                $id_searcher = $_POST['id_searcher'];
                
                echo $FilterProductsPro->getDependenciesFilters($id_searcher);
            }
        break;
        case "updateDependenciesFilters":            
            if (isset($_POST['dependencies']) && isset($_POST['id_searcher'])){
                $dependencies = $_POST['dependencies'];
                $id_searcher = (int)$_POST['id_searcher'];
                
                echo $FilterProductsPro->updateDependenciesFilters($dependencies, $id_searcher);
            }
        break;
        //Acciones para Dependecia de Opciones
        case "getDataFilterDependencyOptions":
            if (isset($_POST['id_filter'])){
                $id_filter = $_POST['id_filter'];
                
                echo $FilterProductsPro->getDataFilterDependencyOptions($id_filter);
            }
        break;
        case "getOptionsChild":
            if (isset($_POST['id_filter_parent']) && isset($_POST['id_option_parent']) && isset($_POST['id_dependency_option'])){
                $id_filter_parent = $_POST['id_filter_parent'];
                $id_option_parent = $_POST['id_option_parent'];
                $id_dependency_option = $_POST['id_dependency_option'];
                
                echo $FilterProductsPro->getOptionsChild($id_filter_parent, $id_option_parent, $id_dependency_option);
            }
        break;
        case "getDependenciesOptions":
            if (isset($_POST['id_filter_parent'])){
                $id_filter_parent = $_POST['id_filter_parent'];
                
                echo $FilterProductsPro->getDependenciesOptions($id_filter_parent);
            }
        break;
        case "updateDependenciesOptions":
            if (isset($_POST['id_filter_parent']) && isset($_POST['id_filter_child']) && isset($_POST['options_checked']) && isset($_POST['dependency_option_checked']) && isset($_POST['options_unchecked'])){
                $options_checked = Tools::getValue('options_checked');
                $dependency_option_checked = Tools::getValue('dependency_option_checked');
                $options_unchecked = Tools::getValue('options_unchecked');
                $id_filter_parent = (int)$_POST['id_filter_parent'];
                $id_filter_child = (int)$_POST['id_filter_child'];
                
                echo $FilterProductsPro->updateDependenciesOptions($id_filter_parent, $id_filter_child, $options_checked, $dependency_option_checked, $options_unchecked);
            }
        break;
        //Acciones para Opciones Customizadas
        case "updateOptionCustomName":
            if (isset($_POST['id_option']) && isset($_POST['id_option_criterion']) && isset($_POST['id_filter']) && isset($_POST['id_searcher']) && isset($_POST['names'])){
                $id_option = (int)$_POST['id_option'];
                $id_option_criterion = (int)$_POST['id_option_criterion'];
                $id_filter = (int)$_POST['id_filter'];
                $id_searcher = (int)$_POST['id_searcher'];
                $names = $_POST['names'];
                
                echo $FilterProductsPro->updateOptionCustomName($id_option, $id_option_criterion, $id_filter, $names, $id_searcher);
            }
        break;
        case "getProductsByOptionCustom":
            if (isset($_POST['id_filter']) && isset($_POST['id_option']) && isset($_POST['id_dependency_option'])){
                $id_filter = $_POST['id_filter'];
                $id_option = $_POST['id_option'];
                $id_dependency_option = $_POST['id_dependency_option'];
                
                echo $FilterProductsPro->getProductsByOptionCustom($id_filter, $id_option, $id_dependency_option);
            }
        break;
        case "loadOption":
            if (isset($_POST['id_option'])){
                $id_option = (int)$_POST['id_option'];
                
                echo $FilterProductsPro->loadOption($id_option);
            }
        break;
        case "addProductOptionCustom":
            if (isset($_POST['id_searcher']) && isset($_POST['id_filter']) && isset($_POST['id_option']) && isset($_POST['id_dependency_option']) && isset($_POST['id_product'])){
                $id_searcher = (int)$_POST['id_searcher'];
                $id_filter = (int)$_POST['id_filter'];
                $id_option = (int)$_POST['id_option'];
                $id_dependency_option = (int)$_POST['id_dependency_option'];
                $id_product = (int)$_POST['id_product'];
                
                echo $FilterProductsPro->addProductOptionCustom($id_searcher, $id_filter, $id_option, $id_dependency_option, $id_product);
            }
        break;
        case "deleteOptionCustom":
            if (isset($_POST['id_option'])){
                $id_option = (int)$_POST['id_option'];
                
                echo $FilterProductsPro->deleteOptionCustom($id_option);
            }
        break;
        case "deleteProductOptionCustom":
            if (isset($_POST['id_option']) && isset($_POST['id_dependency_option']) && isset($_POST['id_product'])){
                $id_option = (int)$_POST['id_option'];
                $id_dependency_option = (int)$_POST['id_dependency_option'];
                $id_product = (int)$_POST['id_product'];
                
                echo $FilterProductsPro->deleteProductOptionCustom($id_option, $id_dependency_option, $id_product);
            }
        break;
        //Acciones para Rango de Precios
        case "saveRangePrice":
            if (isset($_POST['condition']) && isset($_POST['first_value']) && isset($_POST['second_value'])){
                $condition = $_POST['condition'];
                $first_value = (float)$_POST['first_value'];
                $second_value = (float)$_POST['second_value'];
                
                echo $FilterProductsPro->saveRangePrice($condition, $first_value, $second_value);
            }
        break;
        case "deleteRangePrice":
            if (isset($_POST['id_option_criterion'])){
                $id_option_criterion = (int)$_POST['id_option_criterion'];
                
                echo $FilterProductsPro->deleteRangePrice($id_option_criterion);
            }
        break;
        case "getRangesPriceByCondition":
            if (isset($_POST['condition'])){
                $condition = $_POST['condition'];
                
                echo $FilterProductsPro->getRangesPriceByCondition($condition, TRUE);
            }
        break;
    
        //FRONT_OFFICE
        case "searchProducts":
            if (isset($_POST['options'])){
                $options = $_POST['options'];
                
                echo $FilterProductsPro->searchProducts($options);
            }
        break;
        case "getAvailableOptionsDependency":
            if (isset($_POST['id_filter']) && isset($_POST['options'])){
                $id_filter = Tools::getValue('id_filter');
                $options = Tools::getValue('options');
                
                echo $FilterProductsPro->getAvailableOptionsDependency($id_filter, $options);
            }
        break;
        case "getChildrenCategories":
            if (Tools::isSubmit('getChildrenCategories') && Tools::getValue('id_category_parent'))
            {
                if(version_compare(_PS_VERSION_, '1.5') >= 0)
                    $children_categories = $FilterProductsPro->getChildrenWithNbSelectedSubCat15(Tools::getValue('id_category_parent'), 2, $cookie->id_lang, null, Tools::getValue('use_shop_context'));
                else            	
                    $children_categories = $FilterProductsPro->getChildrenWithNbSelectedSubCat(Tools::getValue('id_category_parent'), Tools::getValue('selectedCat'), $cookie->id_lang);
            	die($FilterProductsPro->jsonEncode($children_categories));
            }
        break;
        
    }
}
?>