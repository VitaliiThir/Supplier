<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use Supplier\StoreProducts;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require('vendor/autoload.php');

$store = new StoreProducts();

$store->store_products_update();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
