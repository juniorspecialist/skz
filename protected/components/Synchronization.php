<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.06.14
 * Time: 14:37
 */

/*
 * синхронизация справочников+копирование новых данных из БД Астерикса
 */
class Synchronization {


    /*
     * запускаем синхронизацию справочников
     */
    public function run(){

        //синхронизируем справочник - ОФИСЫ(группа менеджеров)
        //$this->catalogCategoryManager();

        //синхронизируем справочник менеджеров
        $this->catalogManager();

        //синхронизация справочника привязки номеров телефонов к городам и сайтам
        $this->syncSYN();

        //синхроинизируем список DID-номеров
        //$this->DidNumbers();
    }

    /*
     * СИНХРОНИЗАЦИЯ менеджеров, подвязанных к категориям(офисам)
     * получаем список менеджеров с ФИО и кодами из БД-астерикса и обновляем ФИО в текущей системе, ФИО может меняться+могут быть новые значения
     */
    public function catalogManager(){

        //отправляем запрос на АПИ, которое возвратить JSON массив имён менеджеров+их коды(array("code"=>3113, "name"=>"Ivanov"))
        $rows = json_decode(file_get_contents('http://80.84.116.238/restapi/aster_api.php?action=sync'), true);

        if(!empty($rows)){
            foreach($rows as $row){
                //Если не нашли менеджера - создадаим, если нашли - обновим ФИО
                $sql_find = 'SELECT * FROM {{manager}} WHERE code=:code';
                $row_manager =  YiiBase::app()->db->createCommand($sql_find)->bindValue(':code', $row['code'], PDO::PARAM_STR)->queryRow();
                //не нашли менеджера - ДОБАВИМ
                if(empty($row_manager)){
                    $model = new Manager();
                    $model->fio = $row['name'];
                    $model->code = $row['code'];
                    $model->save();
                }else{//нашли менеджера, обновим ФИО текущего менеджера
                    $sql_update = 'UPDATE {{manager}} SET fio=:fio WHERE code=:code';
                    $query_update = YiiBase::app()->db->createCommand($sql_update);
                    $query_update->bindValue(':fio', $row['name'], PDO::PARAM_STR);
                    $query_update->bindValue(':code', $row['code'], PDO::PARAM_STR);
                    $query_update->execute();
                }
            }
        }
    }

    /*
     * синхронизация справочников: список городов, списка сайтов+ список номеров(DID+привязка их в сайту и городу)
     * ответ:array(
     * 'phone'=>'123456789',
     * 'city'=>'Москва',
     * 'site'=>'theservice.ru'
     * )
     */
    public function syncSYN(){

        //отправляем запрос на СУН и получаем список данных
        $url = 'http://phone.theservice.ru/site/SYNInfo/';

        $request = file_get_contents($url);

        if(!empty($request)){

            //преобразуем ответ в массив данных
            $rows = json_decode($request, true);

            if($rows){
                foreach($rows as $row){
                    //проверяем наличие каждого значения в соответствующем справочнике(если нет - добавим+ обновление привязок номера к сайту и городу)
                    $row['phone'] = str_replace('8 (','7', $row['phone']);
                    $row['phone'] = str_replace(array(' ',')', '(', '-'), '', $row['phone']);
                    //$phone = $row['phone'];

                    //echo '<pre>'; print_r($row);

                    $phone = PhoneRegions::model()->findByAttributes(array('phone'=>$row['phone'],'region'=>$row['city'], 'site'=>$row['site']));
                    //$phone = YiiBase::app()->db->createCommand('SELECT * FROM {{phone_regions_site}} WHERE phone=:phone')->bindParam(':phone',)
                    //{"phone":"8 (4822) 63-32-71","city":"\u0422\u0432\u0435\u0440\u044c","site":"theservice.ru"
                    //continue;
                    if($phone){
                        //сравниваем значения
                        $not_good = false;
                        if($row['city']!==$phone['region']){ $not_good = true;}
                        if($row['site']!==$phone['site']){ $not_good = true;}

                        //если есть данные для обновления - обновим
                        if($not_good){
                            $phone->region = $row['city'];
                            $phone->site = $row['site'];
                            $phone->save();
                        }
                    }else{
                        $phone = new PhoneRegions();
                        $phone->phone = $row['phone'];
                        $phone->region = $row['city'];
                        $phone->site = $row['site'];
                        if($phone->validate()){
                            $phone->save();
                        }else{
                            echo '<pre>';
                            print_r($phone->errors);
                        }
                    }
                }
            }
        }

    }

    /*
     * проверим на существование города в списке, если нет - создадим
     */
    public function synCity($city){
        $row = YiiBase::app()->db->createCommand('SELECT id FROM ');
    }
} 