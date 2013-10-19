<?
IncludeModuleLangFile(__FILE__);

$module_access = $APPLICATION->GetGroupRight('forum');

if ($module_access>="R")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "forum",
		"sort" => 550,
		"text" => GetMessage('BEONO_MODULE_FORUMADVERT_MENU_TITLE'),
		"url"  => "/bitrix/admin/beono_forumadvert.php?lang=".LANG,
		"title"=> GetMessage('BEONO_MODULE_FORUMADVERT_MENU_TITLE'),
		"icon" => "forum_menu_icon",
		"page_icon" => "forum_page_icon",
		"items_id" => "menu_forumadvert"
	);
	return $aMenu;
}
?>