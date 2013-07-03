<?php

/**
 * @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
 */
class ColumnOptionClass extends ObjectModel {
    public $id;
    public $id_column;
    public $id_option;
    public $position;
    protected $table = 'fpp_column_option';
    protected $identifier = 'id_column_option';
    protected $tables = array('fpp_column_option');
    protected $fieldsRequired = array('id_option', 'id_column', 'position');
    protected $fieldsValidate = array();
        
    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	public	function __construct($id = null, $id_lang = null, $id_shop = null)
	{		        
        if(version_compare(_PS_VERSION_, '1.5') >= 0){
            self::$definition = array(
        		'table' => 'fpp_column_option',
        		'primary' => 'id_column_option',
        		'multilang' => false,
        		'multilang_shop' => false,
        		'fields' => array(
                    'id_column' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
                    'id_option' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
                    'position' => 	        array('type' => self::TYPE_INT, 'required' => true)
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
            $fields['id_column_option'] = (int) ($this->id_column_option);
        $fields['id_column'] = (int) ($this->id_column);
        $fields['id_option'] = (int) ($this->id_option);
        $fields['position'] = (int) ($this->position);
        return $fields;
    }
    
    public static function deleteAllPositionsByColumn($id_column){
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_column_option
            WHERE
                id_column = " . (int)$id_column . "
        ");
    }
    
    public static function deletePositionByOption($id_option){
        return Db::getInstance()->Execute("
            DELETE FROM " . _DB_PREFIX_ . "fpp_column_option
            WHERE
                id_option = " . (int)$id_option . "
        ");
    }
    
    public static function getOptionsByColumn($id_column, $active = NULL){
        $active = is_null($active) ? -999999 : (int)$active;

        return Db::getInstance()->ExecuteS("
            SELECT * FROM 
                " . _DB_PREFIX_ . "fpp_column_option AS co,
                " . _DB_PREFIX_ . "fpp_option AS o
            WHERE
                co.id_column = " . (int)$id_column . "
                AND co.id_option = o.id_option
                AND (o.active = " . (is_null($active) ? 'o.active' : $active) . " OR " . $active ."= -999999)
            ORDER BY
                co.position
        ");
    }
    
    public static function getOptionsByColumnAndCheckIndexProducts($id_column, $active = NULL, $check_index_product = TRUE){
        $active = is_null($active) ? -999999 : (int)$active;

        return Db::getInstance()->ExecuteS("
            SELECT * FROM 
                " . _DB_PREFIX_ . "fpp_column_option AS co,
                " . _DB_PREFIX_ . "fpp_option AS o
            WHERE
                co.id_column = " . (int)$id_column . "
                AND co.id_option = o.id_option
                AND (o.active = " . (is_null($active) ? 'o.active' : $active) . " OR " . $active ."= -999999)
                AND o.id_option " . (!$check_index_product ? "NOT" : "" ) . " IN (SELECT DISTINCT(id_option) FROM " . _DB_PREFIX_ . "fpp_index_product)
            ORDER BY
                co.position
        ");
    }
}

?>