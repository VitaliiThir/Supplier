<?php

namespace Supplier;

use SplFileInfo;

class Util
{
    /**
     * @param array $arr
     * @return void
     * Распечатка массива
     */
    static function DD(array $arr)
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

    /**
     * @param $file
     * @return string
     * Получение расширения файла
     */
    static function getFileExtension($file): string
    {
        $file_info = new SplFileInfo($file);
        return $file_info->getExtension();
    }

    /**
     * @param $arr
     * @param $key
     * @param $needle
     * @return bool
     */
    static function inAssocArray($arr, $key, $needle): bool
    {
        foreach ($arr as $item) {
            foreach ($item as $subItem) {
                if ($subItem[$key] == $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    static function clearUploads($dir)
    {
        if (is_dir($dir)) {
            $store = scandir($dir);

            foreach ($store as $storeId) {
                if ($storeId != '.' && $storeId != '..') {
                    if (is_dir("$dir/$storeId") && !empty(glob("$dir/$storeId/*.*"))) {
                        $file = scandir("$dir/$storeId", 1);
                        $fileName = $file[0];

                        unlink("$dir/$storeId/$fileName");
                    }
                }
            }
        }
    }
}
