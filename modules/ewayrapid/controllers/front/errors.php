<?php
class ewayrapiderrorsModuleFrontController extends ModuleFrontController
{
	
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();
		
		//not necessary to set http-referrer in PS 1.5
		
		$this->context->smarty->assign(array(
			'eway_errors' => array('[eWAY] '.$this->context->cookie->eway_errors),
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->setTemplate('errors.tpl');
	}
}

?>