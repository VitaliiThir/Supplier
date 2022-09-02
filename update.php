<?php

use Bitrix\Main\Loader;
use Supplier\StoreProducts;

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../"); // php -f =FULL_PATH=/supplier/update.php
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require('vendor/autoload.php');
set_time_limit(0);

Loader::includeModule('catalog');

// В идеале здесь перекрывать доступ к supplier/store для добавления новых файлов, пока не отработает выгрузка

$store = new StoreProducts();

$store->storeProductsUpdate();

// Снова разрешать доступ к supplier/store для добавления новых файлов в случае успешного обновления
// После успешной выгрузки удалять все файлы из папок supplier/store (1,2,3)
