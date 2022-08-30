<?php

/*$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www"; // или realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);*/

use Supplier\StoreProducts;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require('vendor/autoload.php');
//set_time_limit(0);

// Код
\Bitrix\Main\Loader::includeModule('iblock');
$prods = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID'=>3,'PROPERTY_ARTNUMBER'=>array('174-19-06','174-19-04','174-19-03','174-19-02','174-19-01')],
    false, false,
    ['ID','NAME']
);
while ($prod = $prods->GetNext()) {
    \Supplier\Util::DD($prod);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
