<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class beono_forumadvert extends CModule
{
	var $MODULE_ID = "beono.forumadvert";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	function beono_forumadvert()
	{
		$arModuleVersion = array();
		include("version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("BEONO_MODULE_FORUMADVERT_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("BEONO_MODULE_FORUMADVERT_INSTALL_DESCRIPTION");

		$this->PARTNER_NAME = "beono";
		$this->PARTNER_URI = "http://dev.1c-bitrix.ru/community/webdev/user/14039/";
	}


	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		RegisterModule($this->MODULE_ID);

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $DBType, $APPLICATION;

		COption::RemoveOption($this->MODULE_ID, "advert_code");
		COption::RemoveOption($this->MODULE_ID, "message_interval");
		COption::RemoveOption($this->MODULE_ID, "advert_forums");
		COption::RemoveOption($this->MODULE_ID, "status");	
		UnRegisterModule($this->MODULE_ID);

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		return true;
	}

	function InstallPublic()
	{
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();

		$APPLICATION->IncludeAdminFile(GetMessage("BEONO_MODULE_FORUMADVERT_INSTALL_TITLE"), dirname(__FILE__)."/step.php");
		return true;

	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage("BEONO_MODULE_FORUMADVERT_UNINSTALL_TITLE"), dirname(__FILE__)."/unstep.php");
		return true;
	}
}
?>