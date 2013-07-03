<?php


class filterproductsproSearchModuleFrontController extends ModuleFrontController 
{
    public $ssl = false;
	public $php_self = 'search';
    
    public $filterproductspro;

    public function init()
	{
        parent::init();        
                
        $this->filterproductspro = Module::getInstanceByName('filterproductspro');        
	}

    public function initContent()
	{  
		$this->searchProducts();
    }  
    
    public function searchProducts(){
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
            
            $nbProducts = $category->getProducts($this->context->cookie->id_lang, NULL, NULL, NULL, NULL, TRUE);
            
            $this->productSort();
            $this->pagination($nbProducts);
            
            $products = $category->getProducts($this->context->cookie->id_lang, $this->p, $this->n);
        }
        else if(empty($options) && !empty($id_manufacturer)){
            
            $nbProducts = Manufacturer::getProducts($id_manufacturer, NULL, NULL, NULL, NULL, NULL, TRUE);
            
            $this->productSort();
            $this->pagination($nbProducts);
            
            $products = Manufacturer::getProducts($id_manufacturer, $this->context->cookie->id_lang, $this->p, $this->n);
        }
        else if(empty($options) && !empty($id_supplier)){
            
            $nbProducts = Supplier::getProducts($id_supplier, NULL, NULL, NULL, NULL, NULL, TRUE);
            
            $this->productSort();
            $this->pagination($nbProducts);
            
            $products = Supplier::getProducts($id_supplier, $this->context->cookie->id_lang, $this->p, $this->n);
        }
        else if(empty($options) && (empty($id_category) && empty($id_manufacturer) && empty($id_supplier))){
            $category = Category::getRootCategory();
            
            $nbProducts = $category->getProducts($this->context->cookie->id_lang, NULL, NULL, NULL, NULL, TRUE);
            
            $this->productSort();
            $this->pagination($nbProducts);
            
            $products = $category->getProducts($this->context->cookie->id_lang, $this->p, $this->n);                        
                
            if (!is_array($products) || !sizeof($products)){
                $nbProducts = $this->filterproductspro->getProductsRandom($this->context->cookie->id_lang, NULL, NULL, TRUE);
                
                $this->productSort();
                $this->pagination($nbProducts);
            
                $products = $this->filterproductspro->getProductsRandom($this->context->cookie->id_lang, $this->p, $this->n);
            }                                
            
            $this->context->smarty->assign('no_options_selected', true);
        }
        else{
            $nbProducts = $this->filterproductspro->searchProducts(NULL, NULL, TRUE);
        
            $this->productSort();
            $this->pagination($nbProducts);
            
            $products = $this->filterproductspro->searchProducts($this->p, $this->n, FALSE, $this->orderBy, $this->orderWay);
        }
        
        $products = $this->filterproductspro->removeProductsOutStock($products);
        
        $this->context->smarty->assign(array(
            'products' => $products,
            'static_token' => Tools::getToken(false)
        ));
        
        $this->setTemplate('results.tpl');
        $this->display();
    }     
}

?>