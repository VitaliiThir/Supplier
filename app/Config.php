<?php

namespace Supplier;

use Bitrix\Main\Type\DateTime;

trait Config
{
    /**
     * @var string
     * Название корневой директории
     */
    protected string $root_folder = 'supplier';

    /**
     * @var string
     * Название папки для хранения файлов с остатками
     */
    public string $files_folder = 'store';

    /**
     * @var string
     * Код св-ва товара (идентификатор)
     */
    public string $unique_prod_prop = 'ARTNUMBER';

    /**
     * @var string
     *Код св-ва кол-ва товаров
     */
    public string $catalog_quantity = 'CATALOG_QUANTITY';

    /**
     * @var string
     * ID инфоблока товаров
     */
    public string $catalog_ib_id = '3';

    /**
     * @var array|string[]
     * Разрешенные форматы файлов
     */
    public array $files_exts = ['csv', 'xml', 'json'];

    /**
     * @var string
     * Разделитель строки в файле CSV
     */
    public string $csv_separator = ';';

    /**
     * @var string
     * Название файла для записи ошибок
     */
    public string $error_log_file = "errors_logs.txt";

    /**
     * @return string
     * Путь к директории со складами и файлами
     */
    public function files_folder($document_root = false): string
    {
        return $document_root ? "$document_root/$this->root_folder/$this->files_folder" : "/$this->root_folder/$this->files_folder";
    }

    /**
     * @param $document_root
     * @param $data
     * @return void
     * Запись ошибок в файл errors_logs.txt
     */
    public function write_error_logs($document_root, $data)
    {
        $date_time_obj = new DateTime();
        $date_time = $date_time_obj->format('Y.m.d H:i:s');

        file_put_contents("$document_root/$this->root_folder/$this->error_log_file", "$date_time - $data", FILE_APPEND);
    }
}
