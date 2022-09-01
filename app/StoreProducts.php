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
            $stores_to_records = [];

            $product_ids = array_unique(array_column($this->final_products_arr, 'PRODUCT_ID'));

            foreach ($product_ids as $product_id) {
                $store_product_table = StoreProductTable::getList(['filter' => ['=PRODUCT_ID' => $product_id]])->fetchAll();

                foreach ($store_product_table as $ar_record) {
                    $totals[$product_id][$ar_record['STORE_ID']] = $ar_record['AMOUNT'];
                    $stores_to_records[$product_id][$ar_record['STORE_ID']] = $ar_record['ID'];
                }
            }

            foreach ($this->final_products_arr as $product) {
                $product_id = $product['PRODUCT_ID'];
                $store_id = $product['STORE_ID'];
                $amount = $product[$this->catalog_quantity];

                if ($totals[$product_id][$store_id] != $amount) {
                    if ($record_id = $stores_to_records[$product_id][$store_id]) {
                        StoreProductTable::update(
                            $record_id,
                            array(
                                'AMOUNT' => $amount
                            )
                        );
                    } else {
                        StoreProductTable::add(array('PRODUCT_ID' => $product_id, 'STORE_ID' => $store_id, 'AMOUNT' => $amount));
                    }

                    $totals[$product_id][$store_id] += $amount;
                }

            }

            foreach ($totals as $product_id => $stores) {
                ProductTable::update($product_id, array('QUANTITY' => array_sum($stores)));
                Util::DD($stores);
            }

        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }
    }
}
