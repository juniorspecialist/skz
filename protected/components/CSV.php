<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.07.14
 * Time: 11:12
 */

class CSV {

    public function csv_to_array(){

        $rows = array();

        if (($handle = fopen("сайт-номер.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                //print_r($data);
                $data = $rows;
            }
            fclose($handle);

            return $rows;
        }
    }
} 