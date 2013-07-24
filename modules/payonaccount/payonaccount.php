<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class PayOnAccount extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $details;
	public $manager; 
	public $extra_mail_vars; 
	public function __construct()
	{
		$this->name = 'payonaccount';
		$this->tab = 'payments_gateways';
		$this->version = '0.1';
		$this->author = 'Michael Z'; 
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';


		$config = Configuration::getMultiple(array('PAY_ON_ACCOUNT_DETAILS', 'PAY_ON_ACCOUNT_MANAGER'));
		if (isset($config['PAY_ON_ACCOUNT_MANAGER']))
			$this->manager = $config['PAY_ON_ACCOUNT_MANAGER'];
		if (isset($config['PAY_ON_ACCOUNT_DETAILS']))
			$this->details = $config['PAY_ON_ACCOUNT_DETAILS'];

		parent::__construct();

		$this->displayName = $this->l('On Account');
		$this->description = $this->l('Accept payments for your products on an accounts-basis.');
		$this->confirmUninstall = $this->l('Are you sure you want to remove the On Account Module?');
		
		//may be able to remove paragraph if not used
		//if (!isset($this->manager) || !isset($this->details))
		//	$this->warning = $this->l('Manager name and extra information (details) must be configured before using this module.');
			
			
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
										'{payonaccount_manager}' => nl2br(Configuration::get('PAY_ON_ACCOUNT_MANAGER')),
										'{payonaccount_details}' => nl2br(Configuration::get('PAY_ON_ACCOUNT_DETAILS'))
										);
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		$this->createOrderState();
		return true;
	}

	public function uninstall()
	{
		if(Configuration::get('PS_OS_PAYONACCOUNT')){
			$order_state_id_for_deletion = Configuration::get('PS_OS_PAYONACCOUNT');
			$order_state_for_deletion = new OrderState($order_state_id_for_deletion, $this->context->language->id);
		
		if ($order_state_for_deletion->delete()) {
				$filename = dirname(__FILE__).'/../../img/os/'.$order_state_id_for_deletion.'.gif';
				if (is_writable($filename)) {unlink($filename);}
				$filename = dirname(__FILE__).'/../../mails/en/'.'payonaccount.html';
				if (is_writable($filename)) {unlink($filename);}
				$filename = dirname(__FILE__).'/../../mails/en/'.'payonaccount.txt';
				if (is_writable($filename)) {unlink($filename);}
			}		
		}
		
	
		//label configuration lines may be deleted if not installed/used above
		if (!Configuration::deleteByName('PAY_ON_ACCOUNT_DETAILS')
				|| !Configuration::deleteByName('PAY_ON_ACCOUNT_MANAGER')
				|| !Configuration::deleteByName('PS_OS_PAYONACCOUNT')
				|| !parent::uninstall())
			return false;
		return true;
	}

	//CAN BE REMOVED IF NOT ESSENTIAL
	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('details'))
				$this->_postErrors[] = $this->l('Details or summary of account keeping system are required.');
			elseif (!Tools::getValue('manager'))
				$this->_postErrors[] = $this->l('Name of manager is required.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('PAY_ON_ACCOUNT_DETAILS', Tools::getValue('details'));
			Configuration::updateValue('PAY_ON_ACCOUNT_MANAGER', Tools::getValue('manager'));
		}
		$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
	}

	private function _displayPayOnAccount()
	{
		$this->_html .= '<img src="../modules/payonaccount/payonaccount.jpg" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments via an accounts system.').'</b><br /><br />
		'.$this->l('If the client chooses to pay On Account, the order\'s status will change to "Awaiting Account Authorisation"').'<br />
		'.$this->l('That said, you must manually confirm the order upon settling the account. ').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		//$groups = Group::getGroups($this->context->language->id, true);
		$this->_html .=
		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Contact details').'</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please summarise the payment on account process for customers.').'.<br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;">'.$this->l('Manager').'</td><td><input type="text" name="manager" value="'.htmlentities(Tools::getValue('manager', $this->manager), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Details').'</td>
						<td style="padding-bottom:15px;">
							<textarea name="details" rows="4" cols="53">'.htmlentities(Tools::getValue('details', $this->details), ENT_COMPAT, 'UTF-8').'</textarea>
							<p>'.$this->l('Process to follow, what to do in case of issues etc...').'</p>
						</td>
					</tr>
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="alert error">'.$err.'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayPayOnAccount();
		$this->_displayForm();

		return $this->_html;
	}

	public function hookPayment($params)
	{
		$group_ids_with_accounts = array(4); //CHANGE THIS ARRAY TO YOUR LIKING
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;
		$group_check =	$this->context->customer->getGroups($this->context->customer->id_customer);
		if ($group_ids_with_accounts == array_diff($group_ids_with_accounts, $group_check))
			return;


		$this->smarty->assign(array(
			/*'groups' => $this->context->customer->getGroups($this->context->customer->id_customer),*/
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	//need to check this hook's function
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PS_OS_PAYONACCOUNT') || $state == Configuration::get('PS_OS_OUTOFSTOCK')) //change this line
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'payonaccountDetails' => Tools::nl2br($this->details),
				'payonaccountManager' => Tools::nl2br($this->manager),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}
	
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
	
	public function createOrderState()
	{
		if(Configuration::get('PS_OS_PAYONACCOUNT')){
			$order_state_id_for_deletion = Configuration::get('PS_OS_PAYONACCOUNT');
			$order_state_for_deletion = new OrderState($order_state_id_for_deletion, $this->context->language->id);
		
		if ($order_state_for_deletion->delete()) {
				$filename = dirname(__FILE__).'/../../img/os/'.$order_state_id_for_deletion.'.gif';
				if (is_writable($filename)) {unlink($filename);}
				$filename = dirname(__FILE__).'/../../mails/en/'.'payonaccount.html';
				if (is_writable($filename)) {unlink($filename);}
				$filename = dirname(__FILE__).'/../../mails/en/'.'payonaccount.txt';
				if (is_writable($filename)) {unlink($filename);}
			}		
		}
		
		if (!Configuration::get('PS_OS_PAYONACCOUNT'))
		{
			$orderState = new OrderState();
			$orderState->name = array();

			foreach (Language::getLanguages() as $language)
			{
					$orderState->name[$language['id_lang']] = 'Awaiting Account Authorisation';
					$orderState->template[$language['id_lang']] = 'payonaccount';
			}

			$orderState->send_email = true;
			$orderState->color = '#FF4422';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = false;
			$orderState->invoice = false;

			if ($orderState->add())
			{
				$source = dirname(__FILE__).'/logo.gif';
				$destination = dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif';
				copy($source, $destination);
				$source = dirname(__FILE__).'/email_templates/payonaccount.html';
				$destination = dirname(__FILE__).'/../../mails/en/'.'payonaccount.html';
				copy($source, $destination);
				$source = dirname(__FILE__).'/email_templates/payonaccount.txt';
				$destination = dirname(__FILE__).'/../../mails/en/'.'payonaccount.txt';
				copy($source, $destination);
			}
			Configuration::updateValue('PS_OS_PAYONACCOUNT', (int)$orderState->id);
		} else {
			$order_state_id_for_addition = Configuration::get('PS_OS_PAYONACCOUNT');
			$orderState = new OrderState($order_state_id_for_addition, $this->context->language->id);
			
		
		}
	}
}
