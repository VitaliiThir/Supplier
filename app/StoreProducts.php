<?php

namespace Supplier;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockElement;
use Exception;
use Bitrix\Main\LoaderException;

class StoreProducts extends Parser
{
    /**
     * @var array|false
     */
    private array $store_products;

    /**
     * @var array|false
     */
    private array $catalog_filtered_products;

    /**
     * @var array
     */
    private array $final_products_arr;

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function __construct()
    {
        $this->store_products = $this->get_products_arr();
        $this->catalog_filtered_products = $this->get_filtered_catalog();
        $this->final_products_arr = $this->get_final_products_arr();
    }

    /**
     * @return array
     */
    private function get_final_products_arr(): array
    {
        $final_arr = [];

        foreach ($this->store_products as $store_id => $store) {
            foreach ($store as $art => $prod) {
                $prod['PRODUCT_ID'] = $this->catalog_filtered_products[$art]['ID'];
                $prod['STORE_ID'] = $store_id;
                $final_arr[] = $prod;
            }
        }

        return $final_arr;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    private function get_filtered_catalog()
    {
        try {
            $catalog = [];

            $dbItems = ElementTable::getList(array(
                'select' => array('ID', 'IBLOCK_ID'),
                'filter' => array('IBLOCK_ID' => $this->catalog_ib_id)
            ));

            while ($arItem = $dbItems->fetch()) {
                $dbProperty = CIBlockElement::getProperty(
                    $arItem['IBLOCK_ID'],
                    $arItem['ID'], [],
                    array("CODE" => $this->unique_prod_prop)
                );

                while ($arProperty = $dbProperty->Fetch()) {
                    $arItem[$this->unique_prod_prop] = $arProperty["VALUE"];
                }

                if (Util::in_assoc_array($this->store_products, $this->unique_prod_prop, $arItem[$this->unique_prod_prop])) {
                    $catalog[$arItem[$this->unique_prod_prop]] = $arItem;
                }
            }

            return $catalog;

        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }

        return false;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     * @throws Exception
     * Временный код обновления остатков для дальнейшего внедрения
     */
    public function store_products_update()
    {
        try {
            Loader::includeModule('iblock');

            foreach ($this->final_products_arr as $product) {

                $rsStoreProduct = StoreProductTable::getList(array(
                    'filter' => array('=PRODUCT_ID' => $product['ID'], 'STORE.ACTIVE' => 'Y'),
                ))->fetch();

                $updateStore = StoreProductTable::update(
                    $rsStoreProduct['ID'],
                    array(
                        'PRODUCT_ID' => intval($product['ID']),
                        'STORE_ID' => $product['STORE_ID'],
                        'AMOUNT' => $product['CATALOG_QUANTITY']
                    )
                );

                $updateQuantity = ProductTable::update($product['ID'], array('QUANTITY' => $product['CATALOG_QUANTITY']));
            }
        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }
    }
}
