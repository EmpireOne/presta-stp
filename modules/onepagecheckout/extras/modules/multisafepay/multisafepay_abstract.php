<?php

if (!defined('_PS_VERSION_'))
	exit;

abstract class MultisafepayAbstract extends PaymentModule
{
	protected $_html = '';
	public $_errors	= array();
	public $default_country;
	public $iso_code;
	public $context;
	public $multisafepay_logos;
	public $link;

	const BACKWARD_REQUIREMENT = '0.1';
	const DEFAULT_COUNTRY_ISO = 'NL';

	const ONLY_PRODUCTS	= 1;
	const ONLY_DISCOUNTS = 2;
	const BOTH = 3;
	const BOTH_WITHOUT_SHIPPING	= 4;
	const ONLY_SHIPPING	= 5;
	const ONLY_WRAPPING	= 6;
	const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;

	public function __construct()
	{
		$this->name 	= 'multisafepay';
		$this->tab 		= 'payments_gateways';
		$this->version 	= '1.6.2';
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		parent::__construct();

		$this->displayName 		= $this->l('Multisafepay');
		$this->description 		= $this->l('Accepts payments by Multisafepay');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
			$this->backwardCompatibilityChecks();
		$this->page = basename(__FILE__, '.php');

		if (self::isInstalled($this->name))
		{
			/* Default methods (initialization & checks) */
			$this->loadLangDefault();
			$this->multisafepay_logos = new MultisafepayLogos($this->iso_code);

			if (defined('_PS_ADMIN_DIR_'))
			{
				/* Upgrade and compatibility checks */
				$this->runUpgrades();
				$this->compatibilityCheck();
				$this->warningsCheck();

				/* Allowed countries for mobile */
				$iso_code = Country::getIsoById((int)Configuration::get('PS_DEFAULT_COUNTRY'));
				$multisafepay_countries = array('NL');
				if (!$this->active && ($this->context->shop->getTheme() == 'default') && in_array($iso_code, $multisafepay_countries))
					$this->warning .= $this->l('The mobile theme only works with the Multisafepay\'s payment module at this time. Please activate the module to enable payments.');
			}
		}
	}

	/* Check status of backward compatibility module*/
	private function backwardCompatibilityChecks()
	{
		if (self::isInstalled($this->name))
		{
			/*if (Module::isInstalled('backwardcompatibility'))
			{
				$backward_module = Module::getInstanceByName('backwardcompatibility');
				if (!$backward_module->active)
					$this->warning .= $this->l('To work properly the module requires the backward compatibility module enabled');
				elseif ($backward_module->version < MULTISAFEPAY::BACKWARD_REQUIREMENT)
					$this->warning .= $this->l('To work properly the module requires at least the backward compatibility module v').MULTISAFEPAY::BACKWARD_REQUIREMENT;
			}
			else
				$this->warning .= $this->l('In order to use the module you need to install the backward compatibility.');
				*/
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		}
	}

	public function install()
	{
		/* Install and register on hook */
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') ||
        !$this->registerHook('shoppingCartExtra') || !$this->registerHook('backBeforePayment') || !$this->registerHook('rightColumn') ||
        !$this->registerHook('cancelProduct') || !$this->registerHook('productFooter') || !$this->registerHook('header') ||
		!$this->registerHook('adminOrder') || !$this->registerHook('backOfficeHeader'))
			return false;

		if ((_PS_VERSION_ >= '1.5') && (!$this->registerHook('displayMobileHeader') ||
		!$this->registerHook('displayMobileShoppingCartTop') || !$this->registerHook('displayMobileAddToCartTop')))
			return false;

		if (file_exists(_PS_MODULE_DIR_.$this->name.'/multisafepay_tools.php'))
		{
			include_once(_PS_MODULE_DIR_.$this->name.'/multisafepay_tools.php');
			$multisafepay_tools = new MultisafepayTools($this->name);
			$multisafepay_tools->moveTopPayments(1);
			$multisafepay_tools->moveRightColumn(3);
		}

		/* Set database */
		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'multisafepay_order` (
			`id_order` int(10) unsigned NOT NULL,
			`id_transaction` varchar(255) NOT NULL,
			`id_invoice` varchar(255) DEFAULT NULL,
			`currency` varchar(10) NOT NULL,
			`total_paid` varchar(50) NOT NULL,
			`shipping` varchar(50) NOT NULL,
			`capture` int(2) NOT NULL,
			`payment_date` varchar(50) NOT NULL,
			`payment_method` int(2) unsigned NOT NULL,
			`payment_status` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id_order`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

		/* Set database */
		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'multisafepay_customer` (
			`id_multisafepay_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_customer` int(10) unsigned NOT NULL,
			`multisafepay_email` varchar(255) NOT NULL,
			PRIMARY KEY (`id_multisafepay_customer`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'))
			return false;

		/* Set configuration */
		Configuration::updateValue('MULTISAFEPAY_SANDBOX', 0);
		Configuration::updateValue('MULTISAFEPAY_HEADER', '');
		Configuration::updateValue('MULTISAFEPAY_DEFAULT_TAX', '');
		Configuration::updateValue('MULTISAFEPAY_SEND_CONFIRMATION', 1);
		Configuration::updateValue('MULTISAFEPAY_ENABLE_ISSUER', 1);
		Configuration::updateValue('MULTISAFEPAY_TAX_SHIPPING', '');
		Configuration::updateValue('MULTISAFEPAY_API_USER', '');
		Configuration::updateValue('MULTISAFEPAY_API_PASSWORD', '');
		Configuration::updateValue('MULTISAFEPAY_API_SIGNATURE', '');
		Configuration::updateValue('MULTISAFEPAY_EXPRESS_CHECKOUT', 0);
		Configuration::updateValue('MULTISAFEPAY_CAPTURE', 0);
		Configuration::updateValue('MULTISAFEPAY_PAYMENT_METHOD', WPS);
		Configuration::updateValue('MULTISAFEPAY_NEW', 1);
		Configuration::updateValue('MULTISAFEPAY_DEBUG_MODE', 0);
		Configuration::updateValue('MULTISAFEPAY_SHIPPING_COST', 20.00);
		Configuration::updateValue('EXPRESS_CHECKOUT_SHORTCUT', 1);
		Configuration::updateValue('MULTISAFEPAY_VERSION', $this->version);
		Configuration::updateValue('MULTISAFEPAY_COUNTRY_DEFAULT', (int)Configuration::get('PS_COUNTRY_DEFAULT'));

		if (!Configuration::get('MULTISAFEPAY_OS_AUTHORIZATION'))
		{
			$orderState = new OrderState();
			$orderState->name = array();

			foreach (Language::getLanguages() as $language)
			{
				if (strtolower($language['iso_code']) == 'fr')
					$orderState->name[$language['id_lang']] = 'Autorisation acceptée par Multisafepay';
				else
					$orderState->name[$language['id_lang']] = 'Authorization accepted from Multisafepay';
			}

			$orderState->send_email = false;
			$orderState->color = '#DDEEFF';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = true;
			$orderState->invoice = true;

			if ($orderState->add())
				copy(dirname(__FILE__).'/../../img/os/'.Configuration::get('PS_OS_MULTISAFEPAY').'.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
			Configuration::updateValue('MULTISAFEPAY_OS_AUTHORIZATION', (int)$orderState->id);
		}

		return true;
	}

	public function uninstall()
	{
		/* Delete all configurations */
		Configuration::deleteByName('MULTISAFEPAY_SANDBOX');
		Configuration::deleteByName('MULTISAFEPAY_HEADER');
		Configuration::deleteByName('MULTISAFEPAY_API_USER');
		Configuration::deleteByName('MULTISAFEPAY_API_PASSWORD');
		Configuration::deleteByName('MULTISAFEPAY_API_SIGNATURE');
		Configuration::deleteByName('MULTISAFEPAY_EXPRESS_CHECKOUT');
		Configuration::deleteByName('MULTISAFEPAY_PAYMENT_METHOD');
		Configuration::deleteByName('MULTISAFEPAY_TEMPLATE');
		Configuration::deleteByName('MULTISAFEPAY_CAPTURE');
		Configuration::deleteByName('MULTISAFEPAY_DEBUG_MODE');
		Configuration::deleteByName('MULTISAFEPAY_COUNTRY_DEFAULT');
		Configuration::deleteByName('MULTISAFEPAY_DEFAULT_TAX');
		Configuration::deleteByName('MULTISAFEPAY_TAX_SHIPPING');
		Configuration::deleteByName('MULTISAFEPAY_SEND_CONFIRMATION');
		Configuration::deleteByName('MULTISAFEPAY_ENABLE_ISSUER');
	
		// Multisafepay v3 configuration
		Configuration::deleteByName('EXPRESS_CHECKOUT_SHORTCUT');

		return parent::uninstall();
	}

	/**
	 * Launch upgrade process
	 */
	public function runUpgrades($install = false)
	{
		if (_PS_VERSION_ < '1.5')
			foreach (array('2.8', '3.0') as $version)
			{
				$file = dirname(__FILE__).'/upgrade/install-'.$version.'.php';
				if (Configuration::get('MULTISAFEPAY_VERSION') < $version && file_exists($file))
				{
					include_once($file);
					call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this, $install);
				}
			}
	}

	private function compatibilityCheck()
	{
		/* For 1.4.3 and less compatibility */
		$updateConfig = array('PS_OS_CHEQUE' => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4,
		'PS_OS_DELIVERED' => 5, 'PS_OS_CANCELED' => 6, 'PS_OS_REFUND' => 7, 'PS_OS_ERROR' => 8, 'PS_OS_OUTOFSTOCK' => 9,
		'PS_OS_BANKWIRE' => 10, 'PS_OS_MULTISAFEPAY' => 11, 'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $key => $value)
			if (!Configuration::get($key) || (int)Configuration::get($key) < 1)
			{
				if (defined('_'.$key.'_') && (int)constant('_'.$key.'_') > 0)
					Configuration::updateValue($key, constant('_'.$key.'_'));
				else
					Configuration::updateValue($key, $value);
			}
	}

	public function isMultisafepayAPIAvailable()
	{

		return false;
	}

	public function fetchTemplate($path, $name, $extension = false)
	{
		if (_PS_VERSION_ < '1.4')
			$this->context->smarty->currentTemplate = $name;

		return $this->context->smarty->fetch(_PS_MODULE_DIR_.'/multisafepay/'.$path.$name.'.'.($extension ? $extension : 'tpl'));
	}

	public function getContent()
	{
		$this->_postProcess();

		if (($id_lang = Language::getIdByIso('EN')) == 0)
			$english_language_id = (int)$this->context->employee->id_lang;
		else
			$english_language_id = (int)$id_lang;

			
		$sql = 'SELECT * FROM '._DB_PREFIX_.tax;
		$tax = array();
		
		if ($results = Db::getInstance()->ExecuteS($sql))
		{
			foreach ($results as $row)
			{
				$tax[$row['id_tax']] = $row['rate'];
			}
		}	
		$select = '<select name="default_tax">';
		$i  =  1;
		
		while($tax[$i])
		{
			if($tax[$i] == Configuration::get('MULTISAFEPAY_DEFAULT_TAX'))
			{
				$select .= '<option value="'.$tax[$i].'" selected="selected">'.$tax[$i].'</option>';
			}
			else
			{
				$select .= '<option value="'.$tax[$i].'">'.$tax[$i].'</option>';
			}
			$i++;
		}
		$select .= '</select>';
		
		$this->context->smarty->assign(array(
			'PP_errors' 								=> $this->_errors,
			'Multisafepay_logo' 						=> $this->multisafepay_logos->getLogos(),
			'Multisafepay_allowed_methods' 				=> $this->getPaymentMethods(),
			'Multisafepay_country'						=> Country::getNameById((int)$english_language_id, (int)$this->default_country),
			'Multisafepay_country_id' 					=> (int)$this->default_country,
			'Multisafepay_payment_method'				=> (int)Configuration::get('MULTISAFEPAY_PAYMENT_METHOD'),
			'Multisafepay_api_username' 				=> Configuration::get('MULTISAFEPAY_API_USER'),
			'Multisafepay_api_password' 				=> Configuration::get('MULTISAFEPAY_API_PASSWORD'),
			'Multisafepay_api_signature' 				=> Configuration::get('MULTISAFEPAY_API_SIGNATURE'),
			'Multisafepay_express_checkout_shortcut' 	=> (int)Configuration::get('EXPRESS_CHECKOUT_SHORTCUT'),
			'Multisafepay_sandbox_mode' 				=> (int)Configuration::get('MULTISAFEPAY_SANDBOX'),
			'Multisafepay_send_confirmation' 			=> (int)Configuration::get('MULTISAFEPAY_SEND_CONFIRMATION'),
			'Multisafepay_issuer_selection' 			=> (int)Configuration::get('MULTISAFEPAY_ENABLE_ISSUER'),
			'Multisafepay_country_default' 				=> (int)$this->default_country,
			'Multisafepay_default_tax' 					=> $select,
			'Multisafepay_tax_shipping' 				=> (int)Configuration::get('MULTISAFEPAY_TAX_SHIPPING'),
			'Multisafepay_change_country_url' 			=> 'index.php?tab=AdminCountries&token='.Tools::getAdminTokenLite('AdminCountries').'#footer',
			'Countries'									=> Country::getCountries($english_language_id)));
			
		$this->getTranslations();

		return $this->fetchTemplate('/views/templates/back/', 'back_office');
	}

	/*
	** Added to be used properly with OPC
	*/
	public function hookHeader()
	{
		$this->context->smarty->assign(array('base_uri' => __PS_BASE_URI__, 'id_cart'  => (int)$this->context->cart->id));
		if (_PS_VERSION_ < '1.5')
		{
			$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/multisafepay.css');
		}
		else{
			$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/multisafepay-15.css');
		}
		
		return '<script type="text/javascript">'.$this->fetchTemplate('/js/', 'front_office', 'js').'</script>';
	}

	public function hookDisplayMobileHeader()
	{
		return $this->hookHeader();
	}
	
	public function hookDisplayMobileShoppingCartTop()
	{
		return $this->renderExpressCheckoutButton('cart').$this->renderExpressCheckoutForm('cart');
	}
	
	public function hookDisplayMobileAddToCartTop()
	{
		return $this->renderExpressCheckoutButton('cart');
	}
	
	public function hookProductFooter()
	{
		//$content = (!$this->context->getMobileDevice()) ? $this->renderExpressCheckoutButton('product') : '';
		//return $content.$this->renderExpressCheckoutForm('product');
	}
	
	public function renderExpressCheckoutButton($type)
	{
		if (!Configuration::get('EXPRESS_CHECKOUT_SHORTCUT') || !Configuration::get('PS_GUEST_CHECKOUT_ENABLED'))
			return;

		$iso_lang = array('en' => 'en_US', 'fr' => 'fr_FR');

		$this->context->smarty->assign(array(
			'use_mobile' 					=> (bool)$this->context->getMobileDevice(),
			'Multisafepay_payment_type' 	=> $type,
			'Multisafepay_lang_code' 		=> (isset($iso_lang[$this->context->language->iso_code])) ? $iso_lang[$this->context->language->iso_code] : 'en_US',
			'Multisafepay_current_shop_url' => Multisafepay::getShopDomainSsl(true, true).$_SERVER['REQUEST_URI'],
			'Multisafepay_tracking_code' 	=> $this->getTrackingCode())
		);

		return $this->fetchTemplate('/views/templates/front/express_checkout/', 'express_checkout');
	}
	
	public function renderExpressCheckoutForm($type)
	{
		if (!Configuration::get('EXPRESS_CHECKOUT_SHORTCUT') || !Configuration::get('PS_GUEST_CHECKOUT_ENABLED'))
			return;

		$this->context->smarty->assign(array(
			'Multisafepay_payment_type' 	=> $type,
			'Multisafepay_current_shop_url' => Multisafepay::getShopDomainSsl(true, true).$_SERVER['REQUEST_URI'],
			'Multisafepay_tracking_code' 	=> $this->getTrackingCode())
		);

		return $this->fetchTemplate('/views/templates/front/express_checkout/', 'express_checkout_form');
	}

	private function useMobileMethod()
	{
		return Configuration::get('MULTISAFEPAY_PAYMENT_METHOD');
	}

	public function hookPayment()
	{
		if (!$this->active)
			return;

		$method = $this->useMobileMethod();
		$shop_url = Multisafepay::getShopDomainSsl(true, true);

		if (isset($this->context->cookie->express_checkout))
		{
			// Check if user went through the payment preparation detail and completed it
			$detail = unserialize($this->context->cookie->express_checkout);

			if (!empty($detail['payer_id']) && !empty($detail['token']))
			{
				$values = array('get_confirmation' => true);
				$link = $shop_url._MODULE_DIR_.$this->name.'/express_checkout/submit.php';

				if (_PS_VERSION_ < '1.5')
					Tools::redirectLink($link.'?'.http_build_query($values, '', '&'));
				else
				{
					$controller = new FrontController();
					$controller->init();

					Tools::redirect(Context::getContext()->link->getModuleLink('multisafepay', 'confirm', $values));
				}
			}
		}

		$this->context->smarty->assign(array(
			'logos' 					=> $this->multisafepay_logos->getLogos(), 
			'sandbox_mode' 				=> Configuration::get('MULTISAFEPAY_SANDBOX'), 
			'use_mobile' 				=> (bool)$this->context->getMobileDevice(),
			'Multisafepay_lang_code' 	=> (isset($iso_lang[$this->context->language->iso_code])) ? $iso_lang[$this->context->language->iso_code] : 'en_US'
			));

		if($method == 1)
		{
			$this->context->smarty->assign(array(
				'Multisafepay_payment_method' 	=> $method,
				'Multisafepay_payment_type' 	=> 'payment_cart',
				'Multisafepay_current_shop_url' => $shop_url.$_SERVER['REQUEST_URI'],
				'html'							=> '<a href="'.$shop_url._MODULE_DIR_.$this->name.'/express_checkout/submit.php?gateway=connect" ><img src="http://www.multisafepay.com/downloads/betaalbutton/connect/msp-connect-betaalmethoden-liggend-450px.gif" width="450"/></a>',
				'Multisafepay_tracking_code' 	=> $this->getTrackingCode()));
				
			return $this->fetchTemplate('/views/templates/front/express_checkout/', 'multisafepay');
		}
		elseif ($method == 2)
		{
			$billing_address 			= new Address($this->context->cart->id_address_invoice);
			$delivery_address 			= new Address($this->context->cart->id_address_delivery);
			$billing_address->country 	= new Country($billing_address->id_country);
			$delivery_address->country 	= new Country($delivery_address->id_country);
			$billing_address->state		= new State($billing_address->id_state);
			$delivery_address->state 	= new State($delivery_address->id_state);

			$cart = new Cart((int)$this->context->cart->id);
			$cart_details = $cart->getSummaryDetails(null, true);

			// Backward compatibility
			if (_PS_VERSION_ < '1.5')
				$shipping = $this->context->cart->getOrderShippingCost();
			else
				$shipping = $this->context->cart->getTotalShippingCost();

			if ((int)Configuration::get('MULTISAFEPAY_SANDBOX') == 1)
				$url = true;
			else
				$url = false;

			$this->context->smarty->assign(array(
				'url' 							=> $url,
				'cart'							=> $this->context->cart,
				'cart_details' 					=> $cart_details,
				'currency'						=> new Currency((int)$this->context->cart->id_currency),
				'customer' 						=> $this->context->customer,
				'custom' 						=> Tools::jsonEncode(array('id_cart' => $this->context->cart->id, 'hash' => sha1(serialize($cart->nbProducts())))),
				'gift_price' 					=> (float)Configuration::get('PS_GIFT_WRAPPING_PRICE'),
				'billing_address' 				=> $billing_address,
				'delivery_address'	 			=> $delivery_address,
				'shipping' 						=> $shipping,
				'Multisafepay_payment_method' 	=> $method,
				'Multisafepay_current_shop_url' => $shop_url.$_SERVER['REQUEST_URI'],
				'Multisafepay_payment_type'		=> 'payment_cart',
				'subtotal' 						=> $cart_details['total_price_without_tax'] - $shipping,
				'time' 							=> time(),
				'cancel_return' 				=> $this->context->link->getPageLink('order.php'),
				'notify_url' 					=> $shop_url._MODULE_DIR_.$this->name.'/integral_evolution/notifier.php',
				'return_url' 					=> $shop_url._MODULE_DIR_.$this->name.'/integral_evolution/submit.php?id_cart='.(int)$this->context->cart->id,
				'html2' 						=> '<input type="image" src="'.$shop_url._MODULE_DIR_.$this->name.'/img/fco_trans.png" />',
				'tracking_code' 				=> $this->getTrackingCode()));
				
			return $this->fetchTemplate('/views/templates/front/express_checkout/', 'multisafepay');
		}
		elseif($method == 3)
		{
		
			require_once('api/MultiSafepay.combined.php');	
			
			$msp 								= 	new MultiSafepayConnector();
			
			if(Configuration::get('MULTISAFEPAY_SANDBOX'))
			{
				$msp->test 						= 	true;
			}
			else
			{
				$msp->test 						= 	false;
			}
		$msp->merchant['account_id'] 		=	Configuration::get('MULTISAFEPAY_API_USER');
		$msp->merchant['site_id'] 			= 	Configuration::get('MULTISAFEPAY_API_PASSWORD');
		$msp->merchant['site_code'] 		= 	Configuration::get('MULTISAFEPAY_API_SIGNATURE');
		
		$gateways = $msp->getGateways();
		$gatewaylist = '';
		
		$iDealIssuers = $msp->getIdealIssuers();
		$idealselect = '<select name="issuer" id="issuerselect">';
		
		$gateway_sort 		= array();
		$gateway_sort 		= $gateways;
		//$gateway_sort		= unset($gateway_sort['IDEAL']);
		
		unset($gateway_sort[IDEAL]);
	
		
		$i =1;
		foreach ($gateways as $key => $value){
			if($key == 'IDEAL'){
				$gateway_sort  = array_reverse($gateway_sort , true); 
				$gateway_sort[$key] = $val; 
				$gateway_sort[$key]['id'] = $key;
				$gateway_sort[$key]['description'] = $key;
				$gateway_sort  =array_reverse($gateway_sort , true); 
			}
			$i++;
		}
	
		
		if(Configuration::get('MULTISAFEPAY_SANDBOX'))
		{
			foreach($iDealIssuers['issuers'] as $issuer)
			{
				$idealselect .= '<option value="'.$issuer['code']['VALUE'].'">'.$issuer['description']['VALUE'].'</option>';
			}
		}
		else
			{
				foreach($iDealIssuers['issuers']['issuer'] as $issuer)
				{
					$idealselect .= '<option value="'.$issuer['code']['VALUE'].'">'.$issuer['description']['VALUE'].'</option>';
			}
		}
		
		if ((int)Configuration::get('MULTISAFEPAY_ENABLE_ISSUER') == 1)
				$issuer_selection = true;
			else
				$issuer_selection = false;
		
		
		$idealselect .= '</select>';
		
		if(isset($gateway_sort)){	
			foreach($gateway_sort as $key => $value){
				if($key == 'IDEAL' && $issuer_selection ){
					$gatewaylist .= '<p class="payment_module"><input type="hidden" name="gateway" value="'.$key.'" style="" id="'.$key.'"><label for="'.$key.'"><img src="'.$shop_url._MODULE_DIR_.$this->name.'/img/'.$key.'.png"  style="margin-left:15px;" class="paylogos" id="'.$key.'-msp"/></label><b>'.$this->l('Kies uw bank: ').'</b><b>'.$idealselect.'</b><input type="submit" value="Klik hier om te betalen"/></p>';
				}
				else{
					$gatewaylist .= '<p class="payment_module"><a href="'.$shop_url._MODULE_DIR_.$this->name.'/express_checkout/submit.php?gateway='.$key.'" ><img src="'.$shop_url._MODULE_DIR_.$this->name.'/img/'.$key.'.png" /><b>'.$this->l('Veilig betalen met: ').$value['description'].'</b></a></p>';
				}
			}
		}
		$gatewaylist .= '<div style="clear:both;"></div>';
		
			$this->getTranslations();
			$this->context->smarty->assign(array(
				'Multisafepay_payment_method' 	=> $method,
				'Multisafepay_payment_type' 	=> 'payment_cart',
				'Multisafepay_current_shop_url' => $shop_url.$_SERVER['REQUEST_URI'],
				'gatewaylist' 					=> $gatewaylist, 
				'Multisafepay_tracking_code' 	=> $this->getTrackingCode()));

			return $this->fetchTemplate('/views/templates/front/express_checkout/', 'multisafepay');
		}
		return '';
	}

	public function getTrackingCode()
	{
		if ((_PS_VERSION_ < '1.5') && (_THEME_NAME_ == 'prestashop_mobile' || (isset($_GET['ps_mobile_site']) && $_GET['ps_mobile_site'] == 1)))
		{
			if (_PS_MOBILE_TABLET_)
				return TABLET_TRACKING_CODE2;
			elseif (_PS_MOBILE_PHONE_)
				return SMARTPHONE_TRACKING_CODE2;
		}
		if (isset($this->context->mobile_detect))
		{
			if ($this->context->mobile_detect->isTablet())
				return TABLET_TRACKING_COD2E;
			elseif ($this->context->mobile_detect->isMobile())
				return SMARTPHONE_TRACKING_CODE2;
		}
		return TRACKING_CODE2;
	}

	public function hookShoppingCartExtra()
	{
		// No active or ajax request, drop it
		if (!$this->active || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']) || !Configuration::get('EXPRESS_CHECKOUT_SHORTCUT') || !Configuration::get('PS_GUEST_CHECKOUT_ENABLED'))
			return;

		$values = array('en' => 'en_US', 'nl' => 'nl_NL');
		$this->context->smarty->assign(array(
			'Multisafepay_payment_type' 	=> 'cart',
			'Multisafepay_lang_code' 		=> (isset($values[$this->context->language->iso_code]) ? $values[$this->context->language->iso_code] : 'en_US'),
			'Multisafepay_current_shop_url' => Multisafepay::getShopDomainSsl(true, true).$_SERVER['REQUEST_URI'],
			'Multisafepay_tracking_code' 	=> $this->getTrackingCode(),
			'include_form' 					=> true,
			'template_dir' 					=> dirname(__FILE__).'/views/templates/front/express_checkout/'));

		return $this->fetchTemplate('/views/templates/front/express_checkout/', 'express_checkout');
	}

	public function hookPaymentReturn()
	{
		if (!$this->active)
			return;

		return $this->fetchTemplate('/views/templates/front/', 'confirmation');
	}

	/*public function hookRightColumn()
	{
		$this->context->smarty->assign('logo', $this->multisafepay_logos->getCardsLogo(true));
		return $this->fetchTemplate('/views/templates/front/', 'column');
	}

	public function hookLeftColumn()
	{
		return $this->hookRightColumn();
	}*/

	public function hookBackBeforePayment($params)
	{
		if (!$this->active)
			return;
	}


	public function hookBackOfficeHeader()
	{
		if ((int)strcmp((_PS_VERSION_ < '1.5' ? Tools::getValue('configure') : Tools::getValue('module_name')), $this->name) == 0)
		{
			if (_PS_VERSION_ < '1.5')
			{
				$output =  '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery-ui-1.8.10.custom.min.js"></script>
					<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
					<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />
					<link type="text/css" rel="stylesheet" href="'._MODULE_DIR_.$this->name.'/css/multisafepay.css" />';
			}
			else
			{
				$this->context->controller->addJquery();
				$this->context->controller->addJQueryPlugin('fancybox');
				$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/multisafepay.css');
			}

			$this->getContext()->smarty->assign(array('Multisafepay_module_dir' => _MODULE_DIR_.$this->name));
			return (isset($output)?$output:null).$this->fetchTemplate('/views/templates/back/', 'header');
		}
		return '';
	}

	public function getTranslations()
	{
		$file = dirname(__FILE__).'/'._MULTISAFEPAY_TRANSLATIONS_XML_;
		if (file_exists($file))
			$xml = simplexml_load_file($file);
		else
			return false;

		if (isset($xml) && $xml)
		{
			$index = -1;
			$content = array();
			$default = array();

			while (isset($xml->country[++$index]))
			{
				$country = $xml->country[$index];
				$country_iso = $country->attributes()->iso_code;

				if (($this->iso_code != 'default') && ($country_iso == $this->iso_code))
					$content = (array)$country;
				elseif ($country_iso == 'default')
					$default = (array)$country;
			}

			$content += $default;
			$this->context->smarty->assign('Multisafepay_content', $content);

			return true;
		}
		return false;
	}


	public function getCountryDependency($iso_code)
	{
		$localizations = array(
			'AU' => array('AU'), 'BE' => array('BE'), 'CN' => array('CN', 'MO'), 'CZ' => array('CZ'), 'DE' => array('DE'), 'ES' => array('ES'),
			'FR' => array('FR'), 'GB' => array('GB'), 'HK' => array('HK'), 'IL' => array('IL'), 'IN' => array('IN'), 'IT' => array('IT', 'VA'),
			'JP' => array('JP'), 'MY' => array('MY'), 'NL' => array('AN', 'NL'), 'NZ' => array('NZ'), 'PL' => array('PL'),
			'RA' => array('AF', 'AS', 'BD', 'BN', 'BT', 'CC', 'CK', 'CX', 'FM', 'HM', 'ID', 'KH', 'KI', 'KN', 'KP', 'KR', 'KZ',	'LA', 'LK', 'MH',
				'MM', 'MN', 'MV', 'MX', 'NF', 'NP', 'NU', 'OM', 'PG', 'PH', 'PW', 'QA', 'SB', 'TJ', 'TK', 'TL', 'TM', 'TO', 'TV', 'TZ', 'UZ', 'VN',
				'VU', 'WF', 'WS'),
			'RE' => array('IE', 'ZA', 'GP', 'GG', 'JE', 'MC', 'MS', 'MP', 'PA', 'PY', 'PE', 'PN', 'PR', 'LC', 'SR', 'TT',
				'UY', 'VE', 'VI', 'AG', 'AR', 'CA', 'BO', 'BS', 'BB', 'BZ', 'BR', 'CL', 'CO', 'CR', 'CU', 'SV', 'GD', 'GT', 'HN', 'JM', 'NI', 'AD', 'AE',
				'AI', 'AL', 'AM', 'AO', 'AQ', 'AT', 'AW', 'AX', 'AZ', 'BA', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BV', 'BW', 'BY', 'CD', 'CF', 'CG',
				'CH', 'CI', 'CM', 'CV', 'CY', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ET', 'FI', 'FJ', 'FK', 'FO', 'GA', 'GE', 'GF',
				'GH', 'GI', 'GL', 'GM', 'GN', 'GQ', 'GR', 'GS', 'GU', 'GW', 'GY', 'HR', 'HT', 'HU', 'IM', 'IO', 'IQ', 'IR', 'IS', 'JO', 'KE', 'KM', 'KW',
				'KY', 'LB', 'LI', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MD', 'ME', 'MF', 'MG', 'MK', 'ML', 'MQ', 'MR', 'MT', 'MU', 'MW', 'MZ', 'NA',
				'NC', 'NE', 'NG', 'NO', 'NR', 'PF', 'PK', 'PM', 'PS', 'PT', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SC', 'SD', 'SE', 'SI', 'SJ', 'SK', 'SL',
				'SM', 'SN', 'SO', 'ST', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TN', 'UA', 'UG', 'VC', 'VG', 'YE', 'YT', 'ZM', 'ZW'),
			'SG' => array('SG'), 'TH' => array('TH'), 'TR' => array('TR'), 'TW' => array('TW'), 'US' => array('US'));

		foreach ($localizations as $key => $value)
			if (in_array($iso_code, $value))
				return $key;

		return $this->getCountryDependency(self::DEFAULT_COUNTRY_ISO);
	}

	public function getPaymentMethods()
	{
		/*$paymentMethod = array('AU' => array(WPS, HSS, ECS), 'BE' => array(WPS, ECS), 'CN' => array(WPS, ECS), 'CZ' => array(), 'DE' => array(WPS),
		'ES' => array(WPS, HSS, ECS), 'FR' => array(WPS, HSS, ECS), 'GB' => array(WPS, HSS, ECS), 'HK' => array(WPS, HSS, ECS),
		'IL' => array(WPS, ECS), 'IN' => array(WPS, ECS), 'IT' => array(WPS, HSS, ECS), 'JP' => array(WPS, HSS, ECS), 'MY' => array(WPS, ECS),
		'NL' => array(WPS, ECS), 'NZ' => array(WPS, ECS), 'PL' => array(WPS, ECS), 'RA' => array(WPS, ECS), 'RE' => array(WPS, ECS),
		'SG' => array(WPS, ECS), 'TH' => array(WPS, ECS), 'TR' => array(WPS, ECS), 'TW' => array(WPS, ECS), 'US' => array(WPS, ECS),
		'ZA' => array(WPS, ECS));
		*/

		//return isset($paymentMethod[$this->iso_code]) ? $paymentMethod[$this->iso_code] : $paymentMethod[self::DEFAULT_COUNTRY_ISO];
	}

	public function getCountryCode()
	{
		$cart 		= new Cart((int)$this->context->cookie->id_cart);
		$address 	= new Address((int)$cart->id_address_invoice);
		$country 	= new Country((int)$address->id_country);

		return $country->iso_code;
	}

	private function _preProcess()
	{
		if (Tools::isSubmit('submitMultisafepay'))
		{
			$payment_method = Tools::getValue('multisafepay_payment_method') !== false ? (int)Tools::getValue('multisafepay_payment_method') : false;
			$sandbox_mode 	= Tools::getValue('sandbox_mode') !== false ? (int)Tools::getValue('sandbox_mode') : false;

			if ($this->default_country === false || $sandbox_mode === false || $payment_method === false)
				$this->_errors[] = $this->l('Some fields are empty.');
			elseif (!Tools::getValue('api_username') || !Tools::getValue('api_password') || !Tools::getValue('api_signature'))
				$this->_errors[] = $this->l('Credentials fields cannot be empty');
		}

		return !count($this->_errors);
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submitMultisafepay'))
		{
			if (Tools::getValue('multisafepay_country_only'))
				Configuration::updateValue('MULTISAFEPAY_COUNTRY_DEFAULT', (int)Tools::getValue('multisafepay_country_only'));
			elseif ($this->_preProcess())
			{
				Configuration::updateValue('MULTISAFEPAY_PAYMENT_METHOD', (int)Tools::getValue('multisafepay_payment_method'));
				Configuration::updateValue('MULTISAFEPAY_API_USER', trim(Tools::getValue('api_username')));
				Configuration::updateValue('MULTISAFEPAY_API_PASSWORD', trim(Tools::getValue('api_password')));
				Configuration::updateValue('MULTISAFEPAY_API_SIGNATURE', trim(Tools::getValue('api_signature')));
				Configuration::updateValue('EXPRESS_CHECKOUT_SHORTCUT', (int)Tools::getValue('express_checkout_shortcut'));
				Configuration::updateValue('MULTISAFEPAY_SANDBOX', (int)Tools::getValue('sandbox_mode'));
				Configuration::updateValue('MULTISAFEPAY_DEFAULT_TAX', (int)Tools::getValue('default_tax'));
				Configuration::updateValue('MULTISAFEPAY_TAX_SHIPPING', (int)Tools::getValue('tax_shipping'));
				Configuration::updateValue('MULTISAFEPAY_SEND_CONFIRMATION', (int)Tools::getValue('send_confirmation'));
				Configuration::updateValue('MULTISAFEPAY_ENABLE_ISSUER', (int)Tools::getValue('issuer_select'));

				$this->context->smarty->assign('Multisafepay_save_success', true);
			}
			else
			{
				$this->_html = $this->displayError(implode('<br />', $this->_errors)); // Not displayed at this time
				$this->context->smarty->assign('Multisafepay_save_failure', true);
			}
		}

		$this->loadLangDefault();

		return;
	}

	public function _addNewPrivateMessage($id_order, $message)
	{
		if (!$id_order)
			return false;

		$new_message = new Message();
		$message = strip_tags($message, '<br>');

		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payment message is not valid, please check your module.');

		$new_message->message = $message;
		$new_message->id_order = (int)$id_order;
		$new_message->private = 1;

		return $new_message->add();
	}
	

	/**
	 * Return the complete URI for a module
	 * Could be use for return URL process or ajax call
	 *
	 * @param string $file of the module you want to target
	 * @param array $options (value=key)
	 *
	 * @return string
	 */
	public function getURI($file = '', $options = array())
	{
		return Multisafepay::getShopDomainSsl().(__PS_BASE_URI__.'modules/'.$this->name.'/'.(!empty($file) ? $file : '')).'?'.http_build_query($options, '', '&');
	}


    private function warningsCheck()
    {

        /* Check preactivation warning */
        if (Configuration::get('PS_PREACTIVATION_MULTISAFEPAY_WARNING'))
        {
            if (!empty($this->warning))
                $this->warning .= ', ';
            $this->warning .= Configuration::get('PS_PREACTIVATION_MULTISAFEPAY_WARNING');
        }
    }

    private function loadLangDefault()
    {
        $multisafepay_country_default	= (int)Configuration::get('MULTISAFEPAY_COUNTRY_DEFAULT');
        $this->default_country	= ($multisafepay_country_default ? (int)$multisafepay_country_default : (int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $this->iso_code	= $this->getCountryDependency(Country::getIsoById((int)$this->default_country));
    }

	public function getContext()
	{
		return $this->context;
	}

	public static function getShopDomainSsl($http = false, $entities = false)
	{
		if (method_exists('Tools', 'getShopDomainSsl'))
			return Tools::getShopDomainSsl($http, $entities);
		else
		{
			if (!($domain = Configuration::get('PS_SHOP_DOMAIN_SSL')))
				$domain = self::getHttpHost();
			if ($entities)
				$domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
			if ($http)
				$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$domain;
			return $domain;
		}
	}
}
