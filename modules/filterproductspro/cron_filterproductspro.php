<?php



require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__)."/filterproductspro.php");
    
$FilterProductsPro = new FilterProductsPro();

$FilterProductsPro->reindexCategories();
$FilterProductsPro->reindexProducts();

?>
