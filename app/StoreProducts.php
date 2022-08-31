<?php

namespace Supplier;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockElement;
use Exception;

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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function __construct()
    {
        $this->store_products = $this->get_products_arr();
        $this->catalog_filtered_products = $this->get_filtered_catalog();
        $this->final_products_arr = $this->get_final_products_arr();
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
                'filter' => array('IBLOCK_ID' => (int)$this->catalog_ib_id)
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
     * @return array
     */
    public function get_final_products_arr(): array
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
     * Обновление остатков
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function store_products_update()
    {
        try {
            $totals = [];

            foreach ($this->final_products_arr as $product) {
                $product_id = (int)$product['PRODUCT_ID'];
                $store_id = (int)$product['STORE_ID'];
                $amount = (int)$product[$this->catalog_quantity];

                $rsStoreProduct = StoreProductTable::getList(array(
                    'filter' => array('=PRODUCT_ID' => $product_id, 'STORE.ACTIVE' => 'Y', '=STORE_ID' => $store_id),
                ));

                if ($arStoreProduct = $rsStoreProduct->fetch()) {
                    $updateStore = StoreProductTable::update(
                        $arStoreProduct['ID'],
                        array(
                            'PRODUCT_ID' => $product_id,
                            'AMOUNT' => $amount
                        )
                    );
                } else {
                    $addStore = \Bitrix\Catalog\StoreProductTable::add(array('PRODUCT_ID' => $product_id, 'STORE_ID' => $store_id, 'AMOUNT' => $amount));
                }

                $totals[$product_id] += $amount;

            }

            foreach ($totals as $product_id => $quantity) {
                $updateQuantity = ProductTable::update($product_id, array('QUANTITY' => $quantity));
            }

        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }
    }
}
