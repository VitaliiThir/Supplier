<?php

use Bitrix\Main\Loader;
use Supplier\StoreProducts;

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../"); // "c:/openserver/domains/bitrix.test/";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require('vendor/autoload.php');
set_time_limit(0);

Loader::includeModule('catalog');

$store = new StoreProducts();

$store->store_products_update();

echo 'All store product updated!';
