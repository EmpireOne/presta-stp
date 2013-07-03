<?php

if (!defined('_PS_VERSION_'))
  exit;
 
function upgrade_module_1_7_5($object)
{
    return $object->updateVersion();
}
?>