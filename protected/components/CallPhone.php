<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.06.14
 * Time: 14:09
 */
/*
  * выбираем список данных за интервал временни и копируем необходимые данные
  */
class CallPhone{

    //константы статусов событий звонка
    //const EVENT_


    public $intervalMinuts = 60;//за какой интервал времени делать выборку по звонкам, за последние N-минут

    /*
     * проверим наличие звонка в системе по идентифитору звонка
     */
    public function issetCall($linkDid){

        $sql = 'SELECT id FROM  tbl_report WHERE linkedid=:linkedid';

        $query = YiiBase::app()->db->createCommand($sql);

        $query->bindValue(':linkedid', $linkDid, PDO::PARAM_STR);

        $row = $query->queryRow();

        if(!empty($row)){
            return true;
        }else{
            return false;
        }
    }


    /*
     * копируем данные из БД Астерикса по звонкам
     */
    public function run(){

        //получаем список УНИКАЛЬНЫХ идентификаторов звонков за некий интервал, а далее по этим "linkedid" делаем выборки данных
        $linkedid_list = $this->getUniqueLinkDidList();

        //есть звонки для выборки
        if(!empty($linkedid_list)){
            // перебираем список Идентифиторов звонков и собираем инфу и добавим, если данного звонка не было в системе
            foreach($linkedid_list as $link_did){

                //если уже есть инфа по данному взонку пропускаем дальнейший анализ данных
                if($this->issetCall($link_did['linkedid'])){ continue;}

                //ищем общую информацию о звонке
                /*
                 * в таблице "cdr" пол "uniqueid"= полю "linkedid" из таблицы "cel" и поэтому получаем список звонков по таблице БЕЗ событий и потом просматриваем события детально
                 */
                $sql = 'SELECT calldate,did,duration,cnum,cnam,recordingfile,uniqueid,dst, disposition, src FROM cdr WHERE uniqueid=:uniqueid';

                $query = YiiBase::app()->db2->createCommand($sql);

                $query->bindValue(':uniqueid', $link_did['linkedid'], PDO::PARAM_STR);

                $row = $query->queryRow();

                if(!empty($row)){
                    $model = new Report();
                    //уникальный ID звонка это поле "linkedid" в таблице событий(cdr),т.е. может быть несколько "uniqueid" подвязанных к одному звонку(linkedid)
                    $model->uniqueid = $row['uniqueid'];
                    $model->linkedid = $this->getUniqueIdCall($row['uniqueid']);
                    $model->date_call  = self::getDateFromDateTime($row['calldate']);//'дата звонка в формате год месяц число',
                    $model->time_start_call  = self::getTimeFromDateTime($row['calldate']);//Время начала разговора
                    $model->rec_call  = $row['recordingfile'];//'Запись звонка',
                    $model->duration_call  = $row['duration'];//'Продолжительность звонка',
                    $model->destination_call = $row['dst'];//'Destination звонка',
                    //отлавливаем и просчитываем события и пишим их в модель
                    $model = $this->callPhoneEvents($model);
                    $model->call_city  = City::getCityByPhone($model->did);//'Город звонка',
                    //'Офис звонка',
                    if(empty($model->office_call_id)){
                        $find_office = OfficeManager::getIdByCode($model->destination_call);
                        if(!empty($find_office)){$model->office_call_id = $find_office;}
                    }
                    if($model->validate()){
                        $model->save();
                    }else{
                        echo '<pre>'; print_r($model->errors);
                        echo '<pre>'; print_r($model->attributes);
                    }
                }
            }
        }
    }

    /*
     * преобразовываем дату_время в дату для mysql
     */
    static function getDateFromDateTime($date){
        $new_date = date('Y-m-d',strtotime($date));
        return $new_date;
    }

    /*
     * получаем время из даты для поля Mysql
     */
    static function getTimeFromDateTime($date){
        $new_date = date('H:i:s',strtotime($date));
        return $new_date;
    }


    static function getManagerByCode($code){
        if(!empty($code)){
            $id = Manager::getIdByCode($code);
            if(empty($id)){
                return 0;
            }else{
                return $id;
            }
        }else{
            return 0;
        }
    }

    /*
     * получаем уникальный идентификатор звонка, а не очредеи
     * $unique_channel_id - Айди очереди, которая входит с звонок, несколько может быть очередй на звонке
     */
    public function getUniqueIdCall($unique_channel_id){

        $sql = 'SELECT linkedid FROM cel WHERE uniqueid=:uniqueid LIMIT 1';

        $query = YiiBase::app()->db2->createCommand($sql);

        $query->bindValue(':uniqueid', $unique_channel_id, PDO::PARAM_STR);

        $row = $query->queryRow();

        return $row['linkedid'];
    }

    /*
     * делаем выборку Уникальных идентификаторов звонков за интервал времени
     * интервал времени - $this->intervalMinuts
     * выборка нужна, чтобы не выбирать посторяющие данные по звонкам из таблицы "cdr"
     */
    public function getUniqueLinkDidList(){

        $sql = 'SELECT DISTINCT (linkedid)
                FROM cel
                WHERE `eventtime` > SUBDATE(CURRENT_TIMESTAMP , INTERVAL :minute MINUTE)
                ORDER BY eventtime DESC';

        $query = YiiBase::app()->db2->createCommand($sql);

        $query->bindValue(':minute', $this->intervalMinuts, PDO::PARAM_INT);

        return $query->queryAll();
    }

    /*
     * анализируем цепочку событий по Идентификатору звонка
     * $model - строка с первичными данными для сохранения, на основании их собираем остальные и пишим строку целиком
     * выбираем данные по Уникальному идентификатору звонка, а не очереди(linkedid)
     */
    public function callPhoneEvents($model){

        //собираем события по Уникальному идентификатору звонка,собираем необходимые данные по столбцам
        $sql = 'SELECT * FROM cel WHERE linkedid=:linkedid';// OR uniqueid=:uniqueid

        $query = YiiBase::app()->db2->createCommand($sql);

        $query->bindValue(':linkedid', $model->linkedid);
        //$query->bindValue(':uniqueid', $model->linkedid);

        $events = $query->queryAll();

        $time_connect_server = '';//начальное время получения запроса на сервере(событие звонок)
        $time_last_answer = '';//последнее событие ответа, менеджер поднял трубку и ответил на звонок, последний ФТНСВЕР это поднятие трубки

        //массив редиректов по звонку(менеджер переключил на другого менеджера)
        $redirect_list = array();

        //echo '<pre>'; print_r($events); die();

        $answered_call = false;//был ли отвечен звонок

        //отлавливаем нужные события и пишим их в массив
        foreach($events as $index=>$event){

            //по первой строке событий определяем исходящий или входящий звонок
            if($event['eventtype']=='CHAN_START' && empty($model->call_diraction)){
                //если длина номера с которого звонят более 6цифр, значит ВХодящий звонок иначе Исходящий
                if(strlen($event['cid_num'])>6){
                    $model->call_diraction = Report::INCOMING_CALL;//входящий звонок
                }else{
                    $model->call_diraction = Report::OUTGOING_CALL;//исходящий звонок

                    if(empty($model->manager_call_id)){
                        $model->manager_call_id = CallPhone::getManagerByCode($event['cid_num']);
                    }

                }
            }

            //определим - Номер клиента (Caller ID)
            if(empty($model->caller_id) && !empty($event['cid_num'])){
                $model->caller_id = str_replace('+','',$event['cid_num']);
            }

            //ищем номер на который позвонил клиент в списке событий+проверим его наличие в БД на совпадение
            //поле в события "exten" - хранит DID номера на который позвонил клент
            if(!empty($event['exten']) && empty($model->did)){
                //делаем поиск DID в списке номеров подвязанных
                $find_did = PhoneRegions::findPhoneByNumber($event['exten']);
                //'Виртуальный номер на который позвонил клиент (DID)',
                if(!empty($find_did)){
                    $model->did = $event['exten'];//нашли соответсвие по ДИД
                }
            }
            //Время конца разговора
            if($event['eventtype']=='LINKEDID_END'){
                $model->time_end_call = self::getTimeFromDateTime($event['eventtime']);
            }

            //'waiting_time' =>'время от соединения с сервером до взятия трубки менеджером в секундах',
            if($event['eventtype']=='CHAN_START'){//$time_connect_server
                $time_connect_server = $event['eventtime'];
            }

            if($event['eventtype']=='ANSWER'){

                $answered_call = true;//ответил кто-то на звонок

                $time_last_answer = $event['eventtime'];

                //определяем ОФИС для ответа оп звонку
                if(strlen($event['exten'])>2 && strlen($event['exten'])<6 && empty($model->office_call_id)){//если длина строки подходит - ищием воспадение по коду-строке
                    //$model->office_call_id = Manager::getIdByCode($event['exten']);//'Офис звонка',
                    $find_office_id = OfficeManager::getIdByCode($event['exten']);//'Офис звонка',
                    if(!empty($find_office_id)){
                        $model->office_call_id = $find_office_id;
                    }
                }

                //определим менеджера по звонку
                if(!empty($event['cid_num']) && strlen($event['cid_num'])<6 ){//1403882248.44748   && empty($model->manager_call_id)

                    $find_manager = CallPhone::getManagerByCode($event['cid_num']);

                    if(!empty($find_manager)){
                        $model->manager_call_id  = $find_manager;
                    }
                }

            }


            //отлавливаем редиректы по звонкам
            if($event['eventtype']=='ATTENDEDTRANSFER'){//нашли переадресацию звонка,фиксируем цепочку переадресаций
                $redirect_list[] = $event['cid_num'];
                //откуда перенаправили звонок(с менеджера А)
                //array_push($redirect_list, $event['cid_num']);//добавим в конец массива менеджера
                $redirect_list[] = $event['cid_dnid'];
                // куда перенаправили звонок (на менеджера Б)
                //array_push($redirect_list, $event['cid_dnid']);//добавим в конец массива менеджера
            }
        }

        //если нашли менеджера по звонку, значит звонок был - отвечен, если НЕ нашли, значит не отвечен
        if(empty($model->manager_call_id)){
            $model->status_call =  Report::CALL_NO_ANSWER;
        }else{
            $model->status_call =  Report::CALL_ANSWERED;
        }

        //unset($events);

        $model->chain_passed_redirects = '';
        if(!empty($redirect_list)){
            //убираем дубли при формировании списка менеджеров переадресации
            $redirect_list = array_unique($redirect_list);

            //ищем соответствия кодам - менеджерам, что к ним подвязаны, чтобы получить"сева-катя-джамал"
            $managers = Manager::findByCodeList($redirect_list);//получаем массив соответствий менеджеров по коду

            if(!empty($managers)){

                $managers_list = implode('-',CHtml::listData($managers, 'fio', 'fio'));
                /*'chain_passed_redirects' => 'Цепочка пройденных переадресаций в формате имен менеджеров "сева-катя-джамал" и пр',*/
                $model->chain_passed_redirects = $managers_list;
            }

        }

        //'count_redirect' => 'сколько раз звонок был переадресован между менеджерами, прежде чем трубка была поднята',
        $model->count_redirect = sizeof($redirect_list);

        //проверим статус звонка, если на него ответили - считаем дельтту если не ответили - нет смысла
        if($model->status_call=='ANSWERED'){
            //если нашли значения старта звонка и последнего поднятия трубки менеджером, считаем дельту
            if(!empty($time_connect_server) && !empty($time_last_answer)){
                $model->waiting_time = intval(strtotime($time_last_answer)-strtotime($time_connect_server));
            }else{
                $model->waiting_time = 0;
            }
        }else{
            $model->waiting_time = 0;
        }

        return $model;
    }

    /*
     * отправляем запрос по АПИ КОл-тача для получения данных по звонку
     */
    public function getInfoByCallTouch($model){
        /*
        'source' => 'Источник звонка(API calltoch)',
        'search_word' => 'Поисковая фраза(API calltouch)',
        */

        return $model;
    }

}