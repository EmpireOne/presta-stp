<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;
class TMBanner1 extends Module
{
	public $adv_link;
	/**
	 * adv_img contains url to the image to display.
	 * 
	 * @var mixed
	 */
	public $adv_img;
	/**
	 * adv_imgname is the filename of the image to display
	 * @TODO make it configurable for SEO, but need a function to clean filename
	 * @var string
	 */
	public $adv_imgname = 'advertising_custom';
	public function __construct()
	{
		$this->name = 'tmbanner1';
		$this->tab = 'advertising_marketing';
		$this->version = 1.0;
		$this->author = 'PrestaShop';
		parent::__construct();
		$this->displayName = $this->l('TM Banner #1');
		$this->description = $this->l('Adds a block to display a banner.');
		$current_dir = defined('__DIR__')?__DIR__:dirname(__FILE__);
		if (!file_exists($current_dir.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT_TMBANNER1')))
			$this->adv_img = Tools::getMediaServer($this->name)._MODULE_DIR_.$this->name.'/advertising.jpg';
		else
			$this->adv_img = Tools::getMediaServer($this->name)._MODULE_DIR_.$this->name.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT_TMBANNER1');
		$this->adv_link = htmlentities(Configuration::get('BLOCKADVERT_LINK_TMBANNER1'), ENT_QUOTES, 'UTF-8');
	}
	public function install()
	{
		Configuration::updateValue('BLOCKADVERT_LINK_TMBANNER1', 'http://www.prestashop.com');
		if (!parent::install())
			return false;
		if (!$this->registerHook('home'))
			return false;
		return true;
	}
	/**
	 * _deleteCurrentImg delete current image, (so this will use default image)
	 * 
	 * @return void
	 */
	private function _deleteCurrentImg()
	{
		// can work before 5.3
		$current_dir=defined(__DIR__)?__DIR__:dirname(__FILE__);
		if(file_exists($current_dir.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT_TMBANNER1')))
			unlink($current_dir.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT_TMBANNER1'));
	}
	/**
	 * postProcess update configuration
	 * @TODO adding alt and title attributes for <img> and <a>
	 * @var string
	 * @return void
	 */
	public function postProcess()
	{
		global $currentIndex;
		$errors = '';
		if (Tools::isSubmit('submitDeleteImgConf'))
			$this->_deleteCurrentImg();	
		if (Tools::isSubmit('submitAdvConf'))
		{
			$file = false;
			if (isset($_FILES['adv_img']) AND isset($_FILES['adv_img']['tmp_name']) AND !empty($_FILES['adv_img']['tmp_name']))
			{
				if ($error = checkImage($_FILES['adv_img'], 4000000))
					$errors .= $error;
				elseif ($dot_pos = strrpos($_FILES['adv_img']['name'],'.'))
				{
					// __DIR__ exists since php 5.3
					$current_dir = defined(__DIR__)?__DIR__:dirname(__FILE__);
					// as checkImage tell us it's a good image, we'll just copy the extension
					$ext=substr($_FILES['adv_img']['name'], $dot_pos+1);
					$newname=$this->adv_imgname.'.'.$ext;
					$this->_deleteCurrentImg();
					if (!move_uploaded_file($_FILES['adv_img']['tmp_name'], $current_dir.'/'.$newname))
						$errors .= $this->l('Error move uploaded file');
					Configuration::updateValue('BLOCKADVERT_IMG_EXT_TMBANNER1',$ext);
					$this->adv_img = Tools::getMediaServer($this->name)._MODULE_DIR_.$this->name.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT_TMBANNER1');
				}
			}
			if ($link = Tools::getValue('adv_link'))
			{
				Configuration::updateValue('BLOCKADVERT_LINK_TMBANNER1', $link);
				$this->adv_link = htmlentities($link, ENT_QUOTES, 'UTF-8');
			}
		}
		if ($errors)
			echo $this->displayError($errors);
	}
	/**
	 * getContent used to display admin module form
	 * 
	 * @return void
	 */
	public function getContent()
	{
		global $protocol_content;
		
		$this->postProcess();
		$output = '';
		$output .= '
<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
<fieldset><legend>'.$this->l('Advertising block configuration').'</legend>
<a href="'.$this->adv_link.'" target="_blank" title="'.$this->l('Advertising').'">';
		if ($this->adv_img)
			$output .= '<img src="'.$protocol_content.$this->adv_img.'" alt="'.$this->l('Advertising image').'" style="height:163px;margin-left: 100px;width:163px"/>';
		else
			$output .= $this->l('no image');
		$output .= '</a>';
		if ($this->adv_img)
			$output .= '<input class="button" type="submit" name="submitDeleteImgConf" value="'.$this->l('Delete image').'" style=""/>';
		$output .= '<br/>
<br/>
<label for="adv_img">'.$this->l('Change image').'&nbsp;&nbsp;</label><input id="adv_img" type="file" name="adv_img" />
( '.$this->l('Image will be displayed as 155x163').' )
<br/>
<br class="clear"/>
<label for="adv_link">'.$this->l('Image link').'&nbsp;&nbsp;</label><input id="adv_link" type="text" name="adv_link" value="'.$this->adv_link.'" />
<br class="clear"/>
<br/>
<input class="button" type="submit" name="submitAdvConf" value="'.$this->l('validate').'" style="margin-left: 200px;"/>
</fieldset>
</form>
';
		return $output;
	}
	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookHome($params)
	{
		global $smarty, $protocol_content;
		Tools::addCSS(($this->_path).'blockadvertising.css', 'all');
		$smarty->assign('image', $protocol_content.$this->adv_img);
		$smarty->assign('adv_link', $this->adv_link);
		return $this->display(__FILE__, 'tmbanner1.tpl');
	}
}