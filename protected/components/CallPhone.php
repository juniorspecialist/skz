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


    public $intervalMinuts = 360;//за какой интервал времени делать выборку по звонкам, за последние N-минут

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
     * получаем списоке событий по Linkdid-звонку
     * проверяем звонок нужно ли нам его выводить в список(таблицу), если он внутренний(один менеджер позвонил другому, то не выводим его)
     */
    public function checkCall($linkdid){
        //собираем события по Уникальному идентификатору звонка,собираем необходимые данные по столбцам
        $sql = 'SELECT * FROM cel WHERE linkedid=:linkedid';// OR uniqueid=:uniqueid

        $query = YiiBase::app()->db2->createCommand($sql);

        $query->bindValue(':linkedid', $linkdid);

        $events = $query->queryAll();

        $accept_event = false;

        foreach($events as $index=>$event){
            if(strlen($event['cid_num'])>6 || strlen($event['cid_dnid'])>6){
                $accept_event = true; break;
            }
        }

        //если нам подходит linkdid то мы возращаем массив на обработку, если не подходит - пустое значение возращаем
        if($accept_event){
            return $events;
        }else{
            return '';
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

                //проверим отфильтровываем ли мы этот ЛИНК_ДИД или нет
                $events = $this->checkCall($link_did['linkedid']);

                //если нет инфы, звонок нам не подходит, то пропускаем его
                if(empty($events)){continue;}

                $model = new Report();

                if(!empty($row)){
                    //уникальный ID звонка это поле "linkedid" в таблице событий(cdr),т.е. может быть несколько "uniqueid" подвязанных к одному звонку(linkedid)
                    $model->uniqueid = $row['uniqueid'];
                    $model->linkedid = $this->getUniqueIdCall($row['uniqueid']);
                    $model->date_call  = self::getDateFromDateTime($row['calldate']);//'дата звонка в формате год месяц число',
                    $model->time_start_call  = self::getTimeFromDateTime($row['calldate']);//Время начала разговора
                    $model->rec_call  = $row['recordingfile'];//'Запись звонка',
                }else{

                    //уникальный ID звонка это поле "linkedid" в таблице событий(cdr),т.е. может быть несколько "uniqueid" подвязанных к одному звонку(linkedid)
                    $model->uniqueid = $link_did['linkedid'];
                    $model->linkedid = $link_did['linkedid'];

                    $model->rec_call  = '';//'Запись звонка',appname
                }

                //отлавливаем и просчитываем события и пишим их в модель
                $model = $this->callPhoneEvents($model, $events);

                //определяем сайт для входящего звонка
                if(empty($model->site_id)){
                    $site_id = PhoneRegions::getSiteByDid($model->did);
                    if(!empty($site_id)){
                        $model->site_id = $site_id['site_id'];
                    }
                }


                $model->caller_id = str_replace('+', '',$model->caller_id);

                //если звонок ИСХОДЯЩИЙ, не пишим Дестинейшин
                if($model->call_diraction == Report::OUTGOING_CALL){
                    $model->destination_call = '';//'Destination звонка',
                    //при исходящем звонке определяем город по тому, куда звонит менеджер, по номеру клиента
                    $model->call_city  = City::getCityByPhone($model->caller_id);//'Город звонка',
                }else{
                    $model->destination_call = $row['dst'];//'Destination звонка',
                    $model->call_city  = City::getCityByPhone($model->did);//'Город звонка',
                }

                /*
                 * сброшен клиентом
                 * s в столбце дестинейшен) описывает ситуацию, когда вызов сброшен клиентом на стадии "приветствие".
                 * Необходимо ввести статус обработки звонка "сброшен клиентом" в столбце Статус обработки звонка
                 */
                if($model->destination_call=='s'){$model->status_call = Report::CALL_RESET_CLIENT;}


                //echo '<pre>'; print_r($model->attributes);echo ('call_back='.$model->call_back); die();
                if($model->validate()){
                    $model->save();
                }else{
                    echo '<pre>'; print_r($model->errors);
                    echo '<pre>'; print_r($model->attributes);
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

        //AND `linkedid`="1407399661.31900"
        //AND `linkedid`="1407736201.70375"
        //AND `linkedid`="1407850917.92741"
        $sql = 'SELECT DISTINCT (linkedid)
                FROM cel
                WHERE `eventtime` > SUBDATE(CURRENT_TIMESTAMP , INTERVAL :minute MINUTE)
                AND eventtype="LINKEDID_END"

                ORDER BY eventtime DESC';

        $query = YiiBase::app()->db2->createCommand($sql);

        $query->bindValue(':minute', intval($this->intervalMinuts+4), PDO::PARAM_INT);



        $rows = $query->queryAll();

        return $rows;
    }

    public function findRecByUnique($unique){
        $sql = 'SELECT recordingfile  FROM `cdr` WHERE `uniqueid` LIKE :unique';
        $rec = YiiBase::app()->db2->createCommand($sql)->bindParam(':unique',$unique, PDO::PARAM_STR)->queryScalar();
        return $rec;
    }

    /*
     * анализируем цепочку событий по Идентификатору звонка
     * $model - строка с первичными данными для сохранения, на основании их собираем остальные и пишим строку целиком
     * выбираем данные по Уникальному идентификатору звонка, а не очереди(linkedid)
     */
    public function callPhoneEvents($model,$events){


        $time_connect_server = '';//начальное время получения запроса на сервере(событие звонок)
        $time_last_answer = '';//последнее событие ответа, менеджер поднял трубку и ответил на звонок, последний ФТНСВЕР это поднятие трубки
        $time_disconnect = '';//отключение от сервере, последнее событие по звонку

        //массив редиректов по звонку(менеджер переключил на другого менеджера)
        $redirect_list = array();

        $answered_call = false;//был ли отвечен звонок

        $second_can_start = false;

        foreach($events as $index=>$event){

            //определяем файл записи разговора
            if(empty($model->rec_call) && $event['appname']=='Answer'){
                $model->rec_call  = $this->findRecByUnique($event['uniqueid']);//'Запись звонка',
            }

            //проверка на АВТОДОЗВОН - совпадение в описании звонка по регулярке
            if(!$model->call_back){
                if(preg_match('/(.*?)_(.*?):(.*?)_(.*?)/',$event['cid_name'])){$model->call_back = true;$model->call_diraction = Report::OUTGOING_CALL;}
            }

            //заглушка, для исходящих звонков
            if(preg_match('/CID:/',$event['cid_name']) && empty($model->caller_id)){
                $model->caller_id = $event['cid_num'];
                $model->call_diraction = Report::OUTGOING_CALL;//исходящий звонок
            }

            //отлавливаем направление звонка, определяем по короткому коду во втором "chan_start" в списке событий по звонку
            if($event['eventtype']=='CHAN_START' && $second_can_start && empty($model->office_call_id)){
                //по корооткому коду определяем направление звонка, по первой цифре в номере
                $course_call = mb_substr($event['exten'],0,1);
                //по первой цифре определяем направление звонка
                $model->office_call_id = $course_call;
            }
            //отлавливаем ВТОРОЕ открытие канала для звонка
            if($event['eventtype']=='CHAN_START' && empty($model->office_call_id) && !$second_can_start){

                $model->date_call  = self::getDateFromDateTime($event['eventtime']);//'дата звонка в формате год месяц число',
                $model->time_start_call  = self::getTimeFromDateTime($event['eventtime']);//Время начала разговора


                if(empty($event['cid_name']) && empty($event['cid_num'])){
                    $model->call_back = true;$model->call_diraction = Report::OUTGOING_CALL;
                }
                $second_can_start = true;
            }

            if($event['eventtype']=='LINKEDID_END' && empty($time_disconnect)){
                $time_disconnect = $event['eventtime'];
            }

            //отлавливаем цепочку переадресаций по звонку
            if($event['eventtype']=='CHAN_START' && !empty($event['cid_num']) && strlen($event['cid_num'])<6 ){
                $redirect_list[] = $event['cid_num'];
            }


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

                    if(strlen($event['cid_num'])>8){
                        //УКАЖИМ НА КАКОЙ НОМЕР ЗВОНИЛ МЕНЕДЖЕР
                        $model->caller_id = $event['cid_num'];
                    }else{
                        if($event['exten']=='29934'){$model->caller_id = '78007756046';}if($event['exten']=='167465'){$model->caller_id = '74952409192';}
                    }
                    //определяем ОФИС исходящего звонка по первой цифре из КОДА менеджера
                    $model->office_call_id = mb_substr($event['cid_num'],0,1);
                }
            }

            //определим - Номер клиента (Caller ID)
            if(empty($model->caller_id) && !empty($event['cid_num']) && strlen($event['cid_num'])>8){
                //$model->caller_id = str_replace('+','',$event['cid_num']);
            }else{
                //if($event['cid_num']=='29934'){$model->caller_id = '78007756046';}if($event['cid_num']=='167465'){$model->caller_id = '74952409192';}
            }

            //ищем номер на который позвонил клиент в списке событий+проверим его наличие в БД на совпадение
            //поле в события "exten" - хранит DID номера на который позвонил клент
            if(!empty($event['exten']) && empty($model->did)){
                //делаем поиск DID в списке номеров подвязанных
                $find_did = PhoneRegions::findPhoneByNumber($event['exten']);
                //'Виртуальный номер на который позвонил клиент (DID)',
                if(!empty($find_did)){
                    $model->did = $event['exten'];//нашли соответсвие по ДИД
                    $model->phone_region_id = $find_did;
                }
            }
            //Время конца разговора
            if($event['eventtype']=='LINKEDID_END'){
                $model->time_end_call = self::getTimeFromDateTime($event['eventtime']);
            }

            //'waiting_time' =>'время от соединения с сервером до взятия трубки менеджером в секундах',
            if($event['eventtype']=='CHAN_START' && empty($time_connect_server)){//$time_connect_server
                $time_connect_server = $event['eventtime'];
            }

            //подсчитаем продолжительность звонка
            if(!empty($time_disconnect) && !empty($time_connect_server)){
                $model->duration_call = intval(strtotime($time_disconnect)-strtotime($time_connect_server));
            }


            if($event['eventtype']=='ANSWER'){

                $answered_call = true;//ответил кто-то на звонок
                if(!$model->call_back){

                    //если не указан номер телефона клиента, поищем по длине номера
                    if(empty($model->caller_id) && strlen($event['cid_num'])>7){
                        $model->caller_id = str_replace('+','',$event['cid_num']);
                    }

                    //определим менеджера по звонку
                    if(!empty($event['cid_num']) && strlen($event['cid_num'])<6 ){//1403882248.44748   && empty($model->manager_call_id)

                        $find_manager = CallPhone::getManagerByCode($event['cid_num']);

                        if(!empty($find_manager)){

                            $model->manager_call_id  = $find_manager;

                            if(empty($time_last_answer)){$time_last_answer = $event['eventtime'];}
                        }
                    }
                }else{
                    if(preg_match('/(.*?)_(.*?):(.*?)_(.*?)/',$event['cid_name']) && strlen($event['cid_num'])>8){
                        $model->caller_id = str_replace('+','',$event['cid_num']);
                    }else{
                        if($event['exten']=='29934'){$model->caller_id = '78007756046';}if($event['exten']=='167465'){$model->caller_id = '74952409192';}
                    }
                }
            }
        }

        //если номер телефона не определили из списка событий, то пройдёмся про списку событий ещё раз+ берём первый самый длшинный номер из всех
        if(empty($model->caller_id)){
            foreach($events as $index=>$event_new){
                if(preg_match('/[0-9]{6,11}/',$event_new['cid_num'])){ $model->caller_id = $event_new['cid_num']; break;}
                if(preg_match('/[0-9]{6,11}/',$event_new['exten'])){ $model->caller_id = $event_new['exten']; break;}
            }
        }

        //если нашли менеджера по звонку, значит звонок был - отвечен, если НЕ нашли, значит не отвечен(ТОЛЬКО для ИСХОДЯЩЕГО звонка)
        if($model->call_diraction == Report::INCOMING_CALL){
            //для входящего звонка проверим по событию ANSWER
            if($answered_call && !empty($model->manager_call_id)){
                $model->status_call =  Report::CALL_ANSWERED;
            }else{
                $model->status_call =  Report::CALL_NO_ANSWER;
            }
        }else{
            if(!$answered_call){
                $model->status_call =  Report::CALL_NO_ANSWER;
            }else{
                $model->status_call =  Report::CALL_ANSWERED;
            }
        }


        $model->chain_passed_redirects = '';
        if(!empty($redirect_list)){
            //убираем дубли при формировании списка менеджеров переадресации
            //$redirect_list = array_unique($redirect_list);

            //ищем соответствия кодам - менеджерам, что к ним подвязаны, чтобы получить"сева-катя-джамал"
            $managers = Manager::findByCodeList($redirect_list);//получаем массив соответствий менеджеров по коду

            if(!empty($managers)){

                $managers_list = $this->arrayToString($managers, 'fio');

                //$managers_list = implode('-',CHtml::listData($managers, 'fio', 'fio'));
                /*'chain_passed_redirects' => 'Цепочка пройденных переадресаций в формате имен менеджеров "сева-катя-джамал" и пр',*/
                $model->chain_passed_redirects = $managers_list;
            }

        }

        //логика проставления полей для ИСХОДЯЩЕГО ЗВОНКА
        if($model->call_diraction == Report::INCOMING_CALL){//входящий звонок

        }

        //'count_redirect' => 'сколько раз звонок был переадресован между менеджерами, прежде чем трубка была поднята',
        $model->count_redirect = sizeof($redirect_list);

        //проверим статус звонка, если на него ответили - считаем дельтту если не ответили - нет смысла
        if($model->status_call==Report::CALL_ANSWERED){
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

    /*
     * преобразовываем массив(по указанному полю) в строку с разделителем-тире
     */
    function arrayToString($list,$index_array){
        $new_list = array();
        foreach($list as $j=>$row){
            $new_list[] = $row[$index_array];
        }
        return implode('-', $new_list);
    }


}