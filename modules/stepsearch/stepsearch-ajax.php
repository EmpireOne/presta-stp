<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/Classes/StepSearch.php');

if (Tools::getValue('finalpage'))
	die(Tools::jsonEncode(StepSearchCore::getFinalPage(Tools::getValue('id'))));
else 
	die(Tools::jsonEncode(StepSearchCore::getSubFilters(Tools::getValue('id'))));
	
	
?>