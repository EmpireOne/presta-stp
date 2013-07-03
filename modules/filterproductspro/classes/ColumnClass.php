<?php

/**
 * @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
 */
class ColumnClass extends ObjectModel {
    public $id;
    public $id_filter;
    public $position;
    public $value;
    protected $table = 'fpp_column';
    protected $identifier = 'id_column';
    protected $tables = array('fpp_column', 'fpp_column_lang');
    protected $fieldsRequired = array('id_filter','position');
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array();
    protected $fieldsSizeLang = array('value' => 200);
    protected $fieldsValidateLang = array('value' => 'isGenericName');
    
    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	public	function __construct($id = null, $id_lang = null, $id_shop = null)
	{		        
        if(version_compare(_PS_VERSION_, '1.5') >= 0){
            self::$definition = array(
        		'table' => 'fpp_column',
        		'primary' => 'id_column',
        		'multilang' => true,
        		'multilang_shop' => false,
        		'fields' => array(						            
                    'id_filter' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
                    'position' => 	        array('type' => self::TYPE_INT, 'required' => true),
                    
                    // Lang fields
        			'value' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => false, 'size' => 200)
        		)
        	);
            
            parent::__construct($id, $id_lang, $id_shop);
        }else{
            parent::__construct($id, $id_lang);
        }
	}
    
    public function getFields() {
        parent::validateFields();
        if (isset($this->id))
            $fields['id_column'] = (int) ($this->id);
        $fields['id_filter'] = (int) ($this->id_filter);
        $fields['position'] = (int) ($this->position);
        return $fields;
    }   
    
    /**
     * Check then return multilingual fields for database interaction
     *
     * @return array Multilingual fields
     */
    public function getTranslationsFieldsChild() {
        parent::validateFieldsLang();

        $fieldsArray = array('value');
        $fields = array();
        $languages = Language::getLanguages(false);
        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));

        foreach ($languages as $language) {
            $fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
            $fields[$language['id_lang']][$this->identifier] = (int) ($this->id);

            foreach ($fieldsArray as $field) {
                if (!Validate::isTableOrIdentifier($field))
                    die(Tools::displayError());
                if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
                elseif (in_array($field, $this->fieldsRequiredLang))
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
                else
                    $fields[$language['id_lang']][$field] = '';
            }
        }

        return $fields;
    }
}

?>