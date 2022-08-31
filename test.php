<?php

use Bitrix\Main\Loader;
use Supplier\StoreProducts;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require('vendor/autoload.php');

Loader::includeModule('catalog');

$store = new StoreProducts();

$store->store_products_update();


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
