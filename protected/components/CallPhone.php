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

    /**
     * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
     * array containing the HTTP server response header fields and content.
     */
    function get_web_page( $url )
    {
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST  =>"GET",        //set request type post or get
            CURLOPT_POST           =>false,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            //CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            //CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => false,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );
        //echo $url.PHP_EOL;
        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;

        if ( $header['errno'] != 0  || $header['http_code'] != 200 ){
            echo 'curl get errors'.PHP_EOL;
            print_r($header['errmsg']);
        }


        return $header;
    }

    /*
     * отравляем запрос на АПИ Астериска и выбираем данные за интервал
     * $from - интервал ОТ
     * $to - интервал ДО
     */
    public function sendToAPI($from, $to){

        //пример урлы лоя АПИ http://80.84.116.238/restapi/aster_api.php?action=call&filter_from=2015-01-19%2010:22:00&filter_to=2015-01-19%2011:22:22
        //обязательно надо указывать интервал дат для выборки
        //echo 'http://80.84.116.238/restapi/aster_api.php?action=call&filter_from='.$from.'&filter_to='.$to.PHP_EOL;die();

        //$from = '2015-07-22%2001:22:00';
        //$to = '2015-07-031%2016:22:22';


        $json = $this->get_web_page('http://89.108.105.108/restapi/aster_api_.php?action=call&filter_from='.$from.'&filter_to='.$to);



        $calls = json_decode($json['content'], true);

        //echo '<pre>'; print_r($calls); die();

        if($calls){

            foreach($calls as $call){

                //echo $call['linkedid'].PHP_EOL;

                //проверим существование информации о звонке - проверка на дублирование
                $find = Report::issetRepostByLinkedid($call['linkedid']);

                //не нашли записи по идентификатору звонка
                if($find==false){


                    $report = new Report();

                    //определим недостающие параметры для записи в модель
                    if($call['call_diraction']==Report::INCOMING_CALL){//1 - входящий звонок, 2 - исходящий

                        //заглушка
                        if($call['did']=='78007753079' || $call['did']=='00030055' || $call['did']=='74993482371'){
                            $report->site = 'theservice.ru';
                            $report->call_city = 'Москва';
                        }else{
                            //для входящего звонка определим город и сайт
                            $info = PhoneRegions::getInfoByDid($call['did']);
                            if($info){
                                $report->site = $info['site'];
                                $report->call_city = $info['region'];
                            }

                            //если не удалось определить город, тогда считаем что это была заявка ан перезвон с сайта, через форму, а значит ИСХОДЯЩИЙ звонок
                            if(empty($call['did']) && empty($report->call_city)){
                                $call['call_diraction'] = Report::OUTGOING_CALL;//исходящий звонок
                            }
                        }
                    }

                    //основные параметры звонка
                    $report->caller_id = CallPhone::preparePhone($call['caller_id']);//номер звонившего
                    $report->uniqueid = $call['linkedid'];//ID звонка
                    $report->rec_call = $call['rec_call'];//запись разговора
                    $report->waiting_time = $call['waiting_time'];//время ожидания клиента
                    $report->date_call  = self::getDateFromDateTime($call['date_call']);//'дата звонка в формате год месяц число',
                    if(isset($call['redirect_list'])){
                        $report->count_redirect = sizeof($call['redirect_list']);//кол-во редиректов по менеджерам
                    }else{
                        $report->count_redirect = 0;
                    }

                    $report->duration_call = $call['duration_call'];//продолжительность звонка
                    $report->groups = $call['manager_group'];//группа - юр. лица, физ. лица или другие
                    $report->call_diraction = $call['call_diraction'];//направление звонка
                    $report->status_call = Report::statusCallToInt($call['status_call']);
                    $report->did = CallPhone::preparePhone($call['did']);//кто звонит
                    $report->time_start_call  = self::getTimeFromDateTime($call['date_call']);//Время начала разговора
                    $report->time_end_call = self::getTimeFromDateTime(date('Y-m-d H:i:s',(int)strtotime($call['date_call'])+(int)$call['duration_call']));//время завершения разговора

                    if(isset($call['destination_call']) && $call['call_diraction']==Report::INCOMING_CALL){
                        $report->destination_call = $call['destination_call'];
                    }

                    //answer_manager
                    //поиск менеджера по коду
                    if(isset($call['answer_manager'])){
                        $report->manager_call_id = Manager::getIdByCode($call['answer_manager']);
                    }

                    //список редиректов по звонку
                    if(isset($call['redirect_list'])){

                        //ищем соответствия кодам - менеджерам, что к ним подвязаны, чтобы получить"сева-катя-джамал"
                        $managers = Manager::findByCodeList($call['redirect_list']);//получаем массив соответствий менеджеров по коду

                        //проверим всех ли менеджеров нашли по кодам, если кого-то НЕ нашли, значит надо обновить данные - синхронизировать справочник менеджеров
                        if(sizeof($managers)!==sizeof($call['redirect_list'])){

                            $sync = new Synchronization();

                            $sync->catalogManager();

                            //а теперь перезапустим обработку редиректов по менеджерам
                            $managers = Manager::findByCodeList($call['redirect_list']);//получаем массив соответствий менеджеров по коду
                        }

                        $managers_list = $this->arrayToString($managers, 'fio');

                        $report->chain_passed_redirects = implode('-',CHtml::listData($managers, 'fio', 'fio'));

                        //определяем менеджера звонка, последний повесивший трубку - его звонок
                        if($call['call_diraction']==Report::INCOMING_CALL){
                            $code_manager = end($call['redirect_list']);
                        }else{
                            //для исходящего звонка - номер звонившего менеджера и будет кодом менеджера к звонку
                            $code_manager = $report->caller_id;
                        }

                        if(!$report->manager_call_id){
                            //поиск менеджера по коду
                            $report->manager_call_id = Manager::getIdByCode($code_manager);
                        }

                        //если звонок исходящий то номер звонившего должен быть менеджер, а дестинейшин - номер клиента
                        if($report->call_diraction==Report::OUTGOING_CALL){
                            if(strlen($report->caller_id)>6){
                                //номер звонившего - это менеджер, а номер клиента дестинейшин
                                $report->caller_id = $code_manager;
                                $report->did = $report->destination_call;
                                $report->destination_call = '';
                            }
                        }
                    }


                    //если не указан менеджер по звонку+ звонок входящий, то статус у звонка не отвечен
                    if($report->call_diraction==Report::INCOMING_CALL && empty($report->manager_call_id)){
                        $report->status_call = Report::CALL_NO_ANSWER;
                    }

                    /*
                     * сброшен клиентом
                     * s в столбце дестинейшен) описывает ситуацию, когда вызов сброшен клиентом на стадии "приветствие".
                     * Необходимо ввести статус обработки звонка "сброшен клиентом" в столбце Статус обработки звонка
                     */
                    if($call['destination_call']=='s'){$report->status_call = Report::CALL_RESET_CLIENT;}



                    //определяем к Какому пользователю. отнести звонок
                    if($report->call_diraction==Report::INCOMING_CALL){
                        $number = $report->did;
                    }else{
                        $number = $report->caller_id;
                    }

                    $report->user_id = User::wharUserForCall($number, $report->call_diraction);

                    //заглушка - звонок ИСХОДЯЩИЙ+номер звонившего(более 7 цифр), а номер куда звоним - менее 11 цифр(перепутано местами) и менеджер не указан
                    if($report->call_diraction==Report::OUTGOING_CALL){
                        if(empty($report->did) && strlen($report->caller_id)==11 && empty($report->user_id)){
                            $report->did = $report->caller_id;
                            if($report->count_redirect==1){
                                $find_manager = Manager::model()->findByAttributes(array('fio'=>trim($report->chain_passed_redirects)));
                                if($find_manager){
                                    $report->manager_call_id = $find_manager->id;
                                    $report->caller_id = $find_manager->code;
                                    $report->user_id =  $find_manager->user_id;
                                }
                            }
                            //$report->user_id = User::wharUserForCall($number, $report->call_diraction);
                        }
                    }


                    //пропущенный звонок либо занятый - проставляем мписок свободных менеджеров на момент звонка+ кто не взял трубку
                    if($report->call_diraction==Report::INCOMING_CALL){

                        //$manager_by_code = Manager::model()->findByAttributes(['code'=>]);

                        if($report->status_call==Report::CALL_BUSY || $report->status_call==Report::CALL_FAILED || $report->status_call==Report::CALL_NO_ANSWER){
                            $report->busy_manager = Channels::getDataByCall(strtotime($call['date_call']));
                            //из списка переадресаций исключаем тех менеджеров которые были заняты в тек. момент и получаем тех, кто был типа свободен
                            if(!empty($report->busy_manager)){
                                $busy = explode('-',$report->busy_manager);//список менеджеров занятых
                            }else{
                                $busy = array();//список менеджеров занятых
                            }
                            if(!empty($report->chain_passed_redirects)){
                                $all = explode('-',$report->chain_passed_redirects);
                            }else{
                                $all = array();
                            }

                            $guilty = array();
                            if(count($all)>0){
                                foreach($all as $maybe_guilty){
                                    if(!in_array($maybe_guilty, $busy)){
                                        $guilty[] = $maybe_guilty;
                                    }
                                }
                            }
                            if($guilty){
                                $report->guilty_manager = implode('-', $guilty);
                            }
                        }
                    }

                    if($report->validate()){
                        $report->save();
                    }else{
                        echo '<pre>'; print_r($report->attributes);
                        echo '<pre>'; print_r($report->getErrors());
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


    public function findRecByUnique($unique){
        $sql = 'SELECT recordingfile  FROM `cdr` WHERE `uniqueid` LIKE :unique';
        $rec = YiiBase::app()->db2->createCommand($sql)->bindParam(':unique',$unique, PDO::PARAM_STR)->queryScalar();
        return $rec;
    }

    /*
     * очищаем номер телефона от лишних символов+заменяем первую цифры на "7"
     */
    static function preparePhone($phone){

        $result = '';

        if(strlen($phone)>7){
            $phone = str_replace(array('(',')','+'), '', $phone);
            $result = substr_replace($phone, '7', 0, 1);
        }else{
            $result = $phone;
        }
        return $result;
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