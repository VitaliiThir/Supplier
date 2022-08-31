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
    static function get_file_extension($file): string
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
    static function in_assoc_array($arr, $key, $needle): bool
    {
        foreach ($arr as $item) {
            foreach ($item as $sub_item) {
                if ($sub_item[$key] == $needle) {
                    return true;
                }
            }
        }

        return false;
    }
}