<?php
/**
 * This is the bootstrap file for test application.
 * This file should be removed when the application is deployed for production.
 */

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'../../geo/SxGeo.php');
//$ip = '188.134.0.0';
$ip = '';
if (!empty($_SERVER['HTTP_CLIENT_IP'])){
    $ip=$_SERVER['HTTP_CLIENT_IP'];
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
}
else{
    $ip=$_SERVER['REMOTE_ADDR'];
}
//$ip = '212.57.162.124';
//$ip = '188.134.0.0';
if(preg_match('/,/',$ip)){
    $ip_expl = explode(',', $ip);
    $ip = trim($ip_expl[sizeof($ip_expl)-1]);
}
if(empty($ip)){
    return '<input type="hidden" name="geo_city" id="php_geo_city" value="Москва">';
}else{
    $SxGeo = new SxGeo(dirname(__FILE__).DIRECTORY_SEPARATOR.'../../geo/SxGeoCity.dat', SXGEO_BATCH | SXGEO_MEMORY); // Самый быстрый режим
    $geo_info =  $SxGeo->get($ip); //(возвращает информацию о городе, без названия региона и временной зоны)
    $city = $geo_info['city'];
    $result = '';
    if($city== "Москва"){ $result = $city;}
    if($city== "Санкт-Петербург"){ $result = $city;}
    if($city== "Екатеринбург"){ $result = $city;}
    if($city== "Казань"){ $result = $city;}
    if($city== "Нижний Новгород"){ $result = $city;}
    if($city== "Новосибирск"){ $result = $city;}
    if($city== "Омск"){ $result = $city;}
    if($city== "Ростов-на-Дону"){ $result = $city;}
    if($city== "Самара"){ $result = $city;}
    if($city== "Челябинск"){ $result = $city;}
    if($city== "Пермь"){ $result = $city;}

    if($city== "Воронеж"){ $result = $city;}
    if($city== "Краснодар"){ $result = $city;}
    if($city== "Уфа"){ $result = $city;}


    if($city== "Барнаул"){ $result = $city;}
    if($city== "Владимир"){ $result = $city;}
    if($city== "Ижевск"){ $result = $city;}
    if($city== "Иркутск"){ $result = $city;}
    if($city== "Калининград"){ $result = $city;}
    if($city== "Красноярск"){ $result = $city;}
    if($city== "Рязань"){ $result = $city;}
    if($city== "Саратов"){ $result = $city;}
    if($city== "Ставрополь"){ $result = $city;}
    if($city== "Тула"){ $result = $city;}
    if($city== "Тюмень"){ $result = $city;}
    if($city== "Ярославль"){ $result = $city;}


    if(empty($result)){    $result = 'Москва';}
    $city = $result;
    echo  '<input type="hidden" name="geo_city" id="php_geo_city" value="'.$city.'">';
}
