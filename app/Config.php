<?php

namespace Supplier;

use Bitrix\Main\Type\DateTime;

trait Config
{
    /**
     * @var string
     * Название корневой директории
     */
    protected string $rootFolderName = 'supplier';

    /**
     * @var string
     * Название папки для хранения файлов с остатками
     */
    public string $filesFolderName = 'store';

    /**
     * @var string
     * Код св-ва товара (идентификатор)
     */
    public string $uniqueProductPropName = 'ARTNUMBER';

    /**
     * @var string
     *Код св-ва кол-ва товаров
     */
    public string $catalogQuantity = 'CATALOG_QUANTITY';

    /**
     * @var string
     * ID инфоблока товаров
     */
    public string $catalogIbId = '3';

    /**
     * @var array|string[]
     * Разрешенные форматы файлов
     */
    public array $filesExts = ['csv', 'xml', 'json'];

    /**
     * @var string
     * Разделитель строки в файле CSV
     */
    public string $csvSeparator = ';';

    /**
     * @var string
     * Название файла для записи ошибок
     */
    public string $errorLogFileName = "errors_logs.txt";

    /**
     * @return string
     * Путь к директории со складами и файлами
     */
    public function getFilesFolder($document_root = false): string
    {
        return $document_root ? "$document_root/$this->rootFolderName/$this->filesFolderName" : "/$this->rootFolderName/$this->filesFolderName";
    }

    /**
     * @param $document_root
     * @param $data
     * @return void
     * Запись ошибок в файл errors_logs.txt
     */
    public function writeErrorLogs($document_root, $data)
    {
        $dateTimeObj = new DateTime();
        $dateTime = $dateTimeObj->format('Y.m.d H:i:s');

        file_put_contents("$document_root/$this->rootFolderName/$this->errorLogFileName", "$dateTime - $data", FILE_APPEND);
    }
}
