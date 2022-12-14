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
    private array $storeProducts;

    /**
     * @var array|false
     */
    private array $catalogFilteredProducts;

    /**
     * @var array
     */
    private array $finalProductsArr;

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function __construct()
    {
        $this->storeProducts = $this->getProductsArr();
        $this->catalogFilteredProducts = $this->getFilteredCatalog();
        $this->finalProductsArr = $this->getFinalProductsArr();
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    private function getFilteredCatalog()
    {
        try {
            $catalog = [];

            $dbItems = ElementTable::getList(array(
                'select' => array('ID', 'IBLOCK_ID'),
                'filter' => array('IBLOCK_ID' => (int)$this->catalogIbId)
            ));

            while ($arItem = $dbItems->fetch()) {
                $dbProperty = CIBlockElement::getProperty(
                    $arItem['IBLOCK_ID'],
                    $arItem['ID'], [],
                    array("CODE" => $this->uniqueProductPropName)
                );

                while ($arProperty = $dbProperty->Fetch()) {
                    $arItem[$this->uniqueProductPropName] = $arProperty["VALUE"];
                }

                if (Util::inAssocArray($this->storeProducts, $this->uniqueProductPropName, $arItem[$this->uniqueProductPropName])) {
                    $catalog[$arItem[$this->uniqueProductPropName]] = $arItem;
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
    private function getFinalProductsArr(): array
    {
        $finalArr = [];

        foreach ($this->storeProducts as $storeId => $store) {
            foreach ($store as $art => $prod) {
                $prod['PRODUCT_ID'] = $this->catalogFilteredProducts[$art]['ID'];
                $prod['STORE_ID'] = $storeId;
                $finalArr[] = $prod;
            }
        }

        return $finalArr;
    }

    /**
     * ???????????????????? ????????????????
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function storeProductsUpdate()
    {
        try {
            $totals = [];
            $storesToRecords = [];
            $updatableProductsIds = [];

            $productIds = array_unique(array_column($this->finalProductsArr, 'PRODUCT_ID'));

            foreach ($productIds as $productId) {
                $storeProductTable = StoreProductTable::getList(['filter' => ['=PRODUCT_ID' => $productId, 'STORE.ACTIVE' => 'Y']])->fetchAll();

                foreach ($storeProductTable as $arRecord) {
                    $totals[$productId][$arRecord['STORE_ID']] = $arRecord['AMOUNT'];
                    $storesToRecords[$productId][$arRecord['STORE_ID']] = $arRecord['ID'];
                }
            }

            foreach ($this->finalProductsArr as $product) {
                $productId = $product['PRODUCT_ID'];
                $storeId = $product['STORE_ID'];
                $amount = $product[$this->catalogQuantity];

                if ($totals[$productId][$storeId] != $amount) {
                    if ($recordId = $storesToRecords[$productId][$storeId]) {
                        StoreProductTable::update(
                            $recordId,
                            array(
                                'AMOUNT' => $amount
                            )
                        );
                    } else {
                        StoreProductTable::add(array('PRODUCT_ID' => $productId, 'STORE_ID' => $storeId, 'AMOUNT' => $amount));
                    }

                    $totals[$productId][$storeId] = $amount;

                    $updatableProductsIds[] = $productId;
                }

            }

            foreach ($totals as $productId => $stores) {
                if (in_array($productId, $updatableProductsIds)) {
                    ProductTable::update($productId, array('QUANTITY' => array_sum($stores)));
                    echo "\033[1;30m Product [\033[0m#$productId\033[1;30m] - updated \033[01;32m(ok)\n";
                }
            }

            $updatedProductsCnt = count($updatableProductsIds);

            if ($updatedProductsCnt > 0) {
                echo "\033[01;33m ???????????? ?????????????? ??????????????????! ??????-???? ($updatedProductsCnt)";
            } else {
                echo "\033[01;33m ?????????????? ?????? ???????????????????? ??????";
            }

            Util::clearUploads($this->getFilesFolder($_SERVER["DOCUMENT_ROOT"]));

        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }
    }
}
