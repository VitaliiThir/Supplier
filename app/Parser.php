<?php

namespace Supplier;

class Parser extends Supplier
{
    use Config;

    /**
     * @param string $file_name
     * @return array|false
     */
    public function get_xml_to_php_arr(string $file_name)
    {
        $arr = [];
        $xml = simplexml_load_file($file_name);

        foreach ($xml as $item) {
            $art = trim((string)$item->Article);
            $cnt = trim((string)$item->Count);

            if ($art && $cnt) {
                $arr[] = [
                    $this->unique_prod_prop => $art,
                    $this->catalog_quantity => $cnt
                ];
            }
        }

        if (!is_array($arr) || empty($arr)) {
            $arr = false;

            $this->write_error_logs($_SERVER["DOCUMENT_ROOT"], "Ошибка обработки файла XML;\n");
        }

        return $arr;
    }

    /**
     * @param string $file_name
     * @return array|false
     */
    public function get_csv_to_php_arr(string $file_name)
    {
        $arr = [];
        $file = fopen($file_name, 'r');

        while (($line = fgetcsv($file)) !== FALSE) {
            $art = trim(explode($this->csv_separator, $line[0])[0]);
            $cnt = trim(explode($this->csv_separator, $line[0])[1]);

            if ($art && $cnt) {
                $arr[] = [
                    $this->unique_prod_prop => $art,
                    $this->catalog_quantity => $cnt,
                ];
            }
        }

        fclose($file);

        if (is_array($arr) && !empty($arr)) {
            return array_slice($arr, 1);
        } else {
            $arr = false;

            $this->write_error_logs($_SERVER["DOCUMENT_ROOT"], "Ошибка обработки файла CSV;\n");
        }

        return $arr;

    }

    /**
     * @param string $file_name
     * @return array|false
     */
    public function get_json_to_php_arr(string $file_name)
    {
        $arr = [];
        $json = file_get_contents($file_name);
        $json_data = json_decode($json,true);

        foreach ($json_data as $item) {
            $art = trim($item['article']);
            $cnt = trim($item['count']);

            if ($art && $cnt) {
                $arr[] = [
                    $this->unique_prod_prop => $art,
                    $this->catalog_quantity => $cnt
                ];
            }
        }

        if (!is_array($arr) || empty($arr)) {
            $arr = false;

            $this->write_error_logs($_SERVER["DOCUMENT_ROOT"], "Ошибка обработки файла JSON;\n");
        }

        return $arr;
    }

    /**
     * @return array
     */
    public function get_products_arr(): array
    {
        $DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
        $files_dir = $this->files_folder;
        $files_dir_full_path = $this->files_folder($DOCUMENT_ROOT);
        $prods = [];

        if (is_dir($files_dir)) {
            $wh_dirs = scandir($files_dir);

            foreach ($wh_dirs as $wh_dir) {
                if ($wh_dir != '.' && $wh_dir != '..') {
                    if (is_dir("$files_dir/$wh_dir") && !empty(glob("$files_dir/$wh_dir/*.*"))) {
                        $file = scandir("$files_dir/$wh_dir", 1);
                        $file_name = $file[0];
                        $file_ext = Util::get_file_extension($file_name);

                        if (in_array($file_ext, $this->files_exts)) {
                            $file_path = "$files_dir_full_path/$wh_dir/$file_name";
                            $arr = [];

                            switch ($file_ext) {
                                case 'xml':
                                    $arr = $this->get_xml_to_php_arr($file_path);
                                    break;
                                case 'csv':
                                    $arr = $this->get_csv_to_php_arr($file_path);
                                    break;
                                case 'json':
                                    $arr = $this->get_json_to_php_arr($file_path);
                                    break;
                            }

                            if (is_array($arr)) {
                                $prods[$wh_dir] = $arr;
                            }
                        }
                    }
                }
            }
        }
        return $prods;
    }
}
