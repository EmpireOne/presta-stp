<?php 


class StepSearchCore extends ObjectModel {
	
	public $id_lang;

	public static function getFinalPage($id){

		global $cookie;

		if (!is_numeric($id)) {
			die('bad ID');
		}

		$rewrite = Db::getInstance()->getValue('SELECT link_rewrite FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.$id.' AND id_lang ='.$cookie->id_lang);


		$link = new Link();

		return $link->getCategoryLink($id, $rewrite, $cookie->id_lang);

	}

	public static function getSubFilters($id){
		
		global $cookie;

		$id_lang = $cookie->id_lang;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT c.id_category, cl.name
			FROM '._DB_PREFIX_.'category c
			LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (c.id_category = cl.id_category)
			WHERE c.id_parent = '.$id.'
			AND cl.id_lang = '.$id_lang.'
			ORDER BY cl.name');
		
		if ($result) {

			$html = '<option value="">--</option>';
			foreach ($result as $key => $option) {
				$html .='<option value="'.$option['id_category'].'">'.$option['name'].'</option>';
			}

			return $html;
		}
	}
}

?>