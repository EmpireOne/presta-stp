<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/ewayrapid.php');

//I added the true line
if (!$cookie->isLogged(true))
    Tools::redirect('authentication.php?back=order.php');


$Ewayrapid = new Ewayrapid();
$Ewayrapid->afterRedirect();

include_once(dirname(__FILE__).'/../../footer.php');

?>