<?php

if (!defined('_PS_VERSION_'))
  exit;
 
function upgrade_module_1_6_5($object)
{
    return $object->updateVersion();
}
?>