<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.06.14
 * Time: 8:18
 */
error_reporting(E_ALL);

ini_set('display_errors', true);

ini_set('error_reporting',  E_ALL);

$city = array();
$city_class = new ListCity();
class ListCity{
    public $list_city;
    public function setCit($city){
        $this->list_city[] = $city;
    }

    public function getCity(){
        return $this->list_city;
    }
}

$file = fopen('city.tsv', 'r');
while (($line2 = fgetcsv($file)) !== FALSE) {
    $city[] = explode("\t", $line2[0]);
}
fclose($file);

//echo '<pre>'; print_r($city); die();
 //die();

$file = fopen('region.tsv', 'r');
$count = 0;
while (($line = fgetcsv($file)) !== FALSE) {

    $line1 = explode("\t", $line[0]);

    if($line1[2]=='RU'){
        //echo '<pre>';print_r($line1);echo '</pre>';
        findCity($city, $line1[0], $city_class);
        //echo $line1[0].'<br>';
        $count++;
    }
}
fclose($file);

echo $count;

function findCity($city, $findCodeCity, $city_class){
    if(empty($findCodeCity)){
        echo('empty-'.$findCodeCity);
    }else{
        foreach($city as $row){

            if(empty($row[1])){ continue;}

            if($row[1]==0){continue;}

            if($row[1]==$findCodeCity){
                //echo $row[2].'<br>'; //break;
                $city_class->setCit($row[2]);
            }
        }
    }
}

$uniq = array_unique($city_class->getCity());
foreach($uniq as $town){
    echo $town.'<br>';
}

