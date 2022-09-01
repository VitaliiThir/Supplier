<?php

namespace Supplier;

abstract class Supplier
{
    /**
     * @param string $fileName
     * @return array|false
     * Генерация и получение массива PHP из файла XML
     */
    abstract public function getXmlToPhpArr(string $fileName);

    /**
     * @param string $fileName
     * @return array|false
     * Генерация и получение массива PHP из файла CSV
     */
    abstract public function getCsvToPhpArr(string $fileName);

    /**
     * @param string $fileName
     * @return array|false
     * Генерация и получение массива PHP из файла JSON
     */
    abstract public function getJsonToPhpArr(string $fileName);

    /**
     * @return array
     * Проверка и обработка файлов в директориях складо. На выходе array([id склада] => array(products))
     */
    abstract public function getProductsArr(): array;
}
