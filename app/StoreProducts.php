<?php

namespace Supplier;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;

class StoreProducts extends Parser
{
    public function update()
    {
        return $this->get_products_arr();
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     * @throws Exception
     */
    private function get_store()
    {
        $prod_id = "227";
        $prod_cnt = 1800;
        $store_id = 1;

        $rsStoreProduct = StoreProductTable::getList(array(
            'filter' => array('=PRODUCT_ID' => $prod_id, 'STORE.ACTIVE' => 'Y'),
        ))->fetch();

        $updateStore = StoreProductTable::update(
            $rsStoreProduct['ID'],
            array(
                'PRODUCT_ID' => intval($prod_id),
                'STORE_ID' => $store_id,
                'AMOUNT' => $prod_cnt
            )
        );

        $updateQuantity = ProductTable::update($prod_id, array('QUANTITY' => $prod_cnt));
    }
}
