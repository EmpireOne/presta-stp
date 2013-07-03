<?php


require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . "/filterproductspro.php");

$FilterProductsPro = new FilterProductsPro();

global $smarty, $cookie;

//Verificar si las opciones llegan vacias y se esta en categoria, consultar los productos de esta
$id_category = (int)Tools::getValue('id_category', 0);
$id_manufacturer = (int)Tools::getValue('id_manufacturer', 0);
$id_supplier = (int)Tools::getValue('id_supplier', 0);
$options = Tools::getValue('options');

//Limpiar opciones
if(is_array($options))
    foreach ($options as $it => $option)
        if(empty ($option))
            unset($options[$it]);

if(empty($options) && !empty($id_category)){
    $category = new Category($id_category);        
    
    $nbProducts = $category->getProducts($cookie->id_lang, NULL, NULL, NULL, NULL, TRUE);
    
    $FilterProductsPro->productSort();
    $FilterProductsPro->pagination($nbProducts);
    
    $products = $category->getProducts($cookie->id_lang, $FilterProductsPro->p, $FilterProductsPro->n);
}
else if(empty($options) && !empty($id_manufacturer)){
    $nbProducts = Manufacturer::getProducts($id_manufacturer, NULL, NULL, NULL, NULL, NULL, TRUE);

    $FilterProductsPro->productSort();
    $FilterProductsPro->pagination($nbProducts);

    $products = Manufacturer::getProducts($id_manufacturer, $cookie->id_lang, $FilterProductsPro->p, $FilterProductsPro->n);
}
else if(empty($options) && !empty($id_supplier)){

    $nbProducts = Supplier::getProducts($id_supplier, NULL, NULL, NULL, NULL, NULL, TRUE);

    $FilterProductsPro->productSort();
    $FilterProductsPro->pagination($nbProducts);

    $products = Supplier::getProducts($id_supplier, $cookie->id_lang, $FilterProductsPro->p, $FilterProductsPro->n);
}
else if(empty($options) && (empty($id_category) && empty($id_manufacturer) && empty($id_supplier))){
    $category = Category::getRootCategory();
            
    $nbProducts = $category->getProducts($cookie->id_lang, NULL, NULL, NULL, NULL, TRUE);
    
    $FilterProductsPro->productSort();
    $FilterProductsPro->pagination($nbProducts);
    
    $products = $category->getProducts($cookie->id_lang, $FilterProductsPro->p, $FilterProductsPro->n);                        
        
    if (!is_array($products) || !sizeof($products)){
        $nbProducts = $FilterProductsPro->getProductsRandom($cookie->id_lang, NULL, NULL, TRUE);
        
        $FilterProductsPro->productSort();
        $FilterProductsPro->pagination($nbProducts);
    
        $products = $FilterProductsPro->getProductsRandom($cookie->id_lang, $FilterProductsPro->p, $FilterProductsPro->n);
    }                                
    
    $smarty->assign('no_options_selected', true);
}else{
    $nbProducts = $FilterProductsPro->searchProducts(NULL, NULL, TRUE);

    $FilterProductsPro->productSort();
    $FilterProductsPro->pagination($nbProducts);
    
    $products = $FilterProductsPro->searchProducts($FilterProductsPro->p, $FilterProductsPro->n, FALSE, $FilterProductsPro->orderBy, $FilterProductsPro->orderWay);
}

$products = $FilterProductsPro->removeProductsOutStock($products);

$smarty->assign(array(
    'products' => $products,
    'static_token' => Tools::getToken(false)
));

echo $smarty->display(dirname(__FILE__) . '/views/templates/front/results.tpl');

?>