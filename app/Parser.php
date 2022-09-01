<?php

namespace Supplier;

class Parser extends Supplier
{
    use Config;

    /**
     * @param string $file_name
     * @return array|false
     */
    public function getXmlToPhpArr(string $file_name)
    {
        $arr = false;
        $xml = simplexml_load_file($file_name);

        foreach ($xml as $item) {
            $art = trim((string)$item->Article);
            $cnt = trim((string)$item->Count);

            if ($art && $cnt) {
                $arr[$art] = [
                    $this->uniqueProductPropName => $art,
                    $this->catalogQuantity => $cnt
                ];
            }
        }

        if (!is_array($arr) || empty($arr)) {
            $this->writeErrorLogs($_SERVER["DOCUMENT_ROOT"], "Ошибка обработки файла XML;\n");
        }

        return $arr;
    }

    /**
     * @param string $file_name
     * @return array|false
     */
    public function getCsvToPhpArr(string $file_name)
    {
        $arr = false;
        $file = fopen($file_name, 'r');

        while (($line = fgetcsv($file)) !== FALSE) {
            $art = trim(explode($this->csvSeparator, $line[0])[0]);
            $cnt = trim(explode($this->csvSeparator, $line[0])[1]);

            if ($art && $cnt) {
                $arr[$art] = [
                    $this->uniqueProductPropName => $art,
                    $this->catalogQuantity => $cnt,
                ];
            }
        }

        fclose($file);

        if (is_array($arr) && !empty($arr)) {
            return array_slice($arr, 1);
        } else {
            $this->writeErrorLogs($_SERVER["DOCUMENT_ROOT"], "Ошибка обработки файла CSV;\n");
        }

        return $arr;

    }

    /**
     * @param string $file_name
     * @return array|false
     */
    public function getJsonToPhpArr(string $file_name)
    {
        $arr = false;
        $json = file_get_contents($file_name);
        $json_data = json_decode($json, true);

        foreach ($json_data as $item) {
            $art = trim($item['article']);
            $cnt = trim($item['count']);

            if ($art && $cnt) {
                $arr[$art] = [
                    $this->uniqueProductPropName => $art,
                    $this->catalogQuantity => $cnt
                ];
            }
        }

        if (!is_array($arr) || empty($arr)) {
            $this->writeErrorLogs($_SERVER["DOCUMENT_ROOT"], "Ошибка обработки файла JSON;\n");
        }

        return $arr;
    }

    /**
     * @return array|false
     */
    public function getProductsArr(): array
    {
        $filesDirFullPath = $this->getFilesFolder($_SERVER["DOCUMENT_ROOT"]);
        $prods = [];

        if (is_dir($filesDirFullPath)) {
            $storeDirs = scandir($filesDirFullPath);

            foreach ($storeDirs as $storeDir) {
                if ($storeDir != '.' && $storeDir != '..') {
                    if (is_dir("$filesDirFullPath/$storeDir") && !empty(glob("$filesDirFullPath/$storeDir/*.*"))) {
                        $file = scandir("$filesDirFullPath/$storeDir", 1);
                        $fileName = $file[0];
                        $fileExt = Util::getFileExtension($fileName);

                        if (in_array($fileExt, $this->filesExts)) {
                            $filePath = "$filesDirFullPath/$storeDir/$fileName";
                            $arr = [];

                            switch ($fileExt) {
                                case 'xml':
                                    $arr = $this->getXmlToPhpArr($filePath);
                                    break;
                                case 'csv':
                                    $arr = $this->getCsvToPhpArr($filePath);
                                    break;
                                case 'json':
                                    $arr = $this->getJsonToPhpArr($filePath);
                                    break;
                            }

                            if (is_array($arr)) {
                                $prods[$storeDir] = $arr;
                            }
                        }
                    }
                }
            }
        }

        if (!is_array($prods) || empty($prods)) {
            return false;
        }

        return $prods;
    }
}
