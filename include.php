<?php

CModule::AddAutoloadClasses(
    null,
	array(
		'OrdersList\Date' => '/local/modules/custom.orderslist/lib/date.php',
	)
);

$arJsConfig = array( 
	'custom.orderslist' => array( 
		'js' => '/bitrix/js/custom.orderslist/list.js', 
		'css' => '/bitrix/css/custom.orderslist/list.css', 
		'rel' => array(), 
	) 
); 
foreach ($arJsConfig as $ext => $arExt) { 
	CJSCore::RegisterExt($ext, $arExt); 
}

CUtil::InitJSCore(array('custom.orderslist'));