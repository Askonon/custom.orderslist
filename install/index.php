<?php

use Bitrix\Main\ModuleManager;
class custom_orderslist extends CModule
{
    var $MODULE_ID = "custom.orderslist";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__.'/version.php');
		$this->MODULE_NAME = $arModuleVersion['MODULE_NAME'];
		$this->MODULE_DESCRIPTION = $arModuleVersion['MODULE_DESCRIPTION'];
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
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
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/local/modules/custom.orderslist/install/js/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/js/custom.orderslist/",
			true, true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/local/modules/custom.orderslist/install/css/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/css/custom.orderslist/",
			true, true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/local/modules/custom.orderslist/install/admin", 
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", 
			true, true
		);
		return true;
	}
	function UnInstallFiles()
	{
		DeleteDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/local/modules/custom.orderslist/install/js/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/js/custom.orderslist/",
		);
		DeleteDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/local/modules/custom.orderslist/install/css/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/css/custom.orderslist/"
		);
		DeleteDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/local/modules/custom.orderslist/install/admin", 
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin"
		);
		return true;
	}
	function DoInstall()
	{
		global $APPLICATION;
		if (!IsModuleInstalled("custom.orderslist"))
		{
			$this->InstallFiles();
			$this->InstallEvents();
			ModuleManager::registerModule("custom.orderslist");
		}
	}
	function DoUninstall()
	{
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		ModuleManager::unRegisterModule("custom.orderslist");
	}
}