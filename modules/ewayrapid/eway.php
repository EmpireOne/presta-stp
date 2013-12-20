<?php

/* SSL Management */
$useSSL = true;

require('../../config/config.inc.php');


// init front controller in order to use Tools::redirect
$controller = new FrontController();
$controller->init();

include(dirname(__FILE__).'/ewayrapid.php');

//I added the true line
if (!$cookie->isLogged(true))
    Tools::redirect('authentication.php?back=order.php');

$Ewayrapid = new Ewayrapid();
$Ewayrapid->afterRedirect();


?>