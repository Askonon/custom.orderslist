<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight("form")>"D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_store",
		"sort"        => 100,
		"url"         => "list.php", 
		"text"        => Loc::getMessage("ORDERS_LIST_MENU_MAIN"),
		"title"       => Loc::getMessage("ORDERS_LIST_MENU_MAIN"),
		"icon"        => "form_menu_icon",
		"page_icon"   => "form_page_icon", 
		"items_id"    => "menu_store",
	);
	return $aMenu;
}
return false;
?>