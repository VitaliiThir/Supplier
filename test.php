<?php

use Supplier\StoreProducts;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require('vendor/autoload.php');

\Bitrix\Main\Loader::includeModule('iblock');

$store = new StoreProducts();
$store_products = $store->get_products_arr();
$catalog = [];

$dbItems = \Bitrix\Iblock\ElementTable::getList(array(
    'select' => array('ID', 'IBLOCK_ID', 'NAME'),
    'filter' => array('IBLOCK_ID' => 3)
));
while ($arItem = $dbItems->fetch()){
    $dbProperty = \CIBlockElement::getProperty(
        $arItem['IBLOCK_ID'],
        $arItem['ID'],[],
        array("CODE"=>"ARTNUMBER")
    );
    while($arProperty = $dbProperty->Fetch()){
        $arItem['ARTNUMBER'] = $arProperty["VALUE"];
    }

    $catalog[] = $arItem;
}

//\Supplier\Util::DD($arr);
\Supplier\Util::DD($store_products);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
