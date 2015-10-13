<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.04.14
 * Time: 14:27
 */
/*
 * запускаем парсинг статистики по списку аккаунтов из БД
 */
class CronCommand extends CConsoleCommand{

    /*
     * выбираем информацию о звонках из Астерикса и записываем в БД системы
     */
    public function actionStart(){

        //date_default_timezone_set( 'Europe/Moscow' );

        //проверим не отключили ли мы запуск по крону задания, если интервал более "0" запускаем задание
        if(Yii::app()->params['cronInterval']>0){
            //синхронизируем справочники
//            $sync = new Synchronization();
//            $sync->run();

            //копируем Новые данные за интервал времени
            $report = new CallPhone();
            //$report->intervalMinuts = Yii::app()->params['cronInterval'];
            //$report->run();
            $date_from = date('Y-m-d H:i:s', strtotime('-67 minutes'));
            $date_to = date('Y-m-d H:i:s', strtotime('-57 minutes'));
            $report->sendToAPI(urlencode($date_from), urlencode($date_to));

            //возможно пропустили какие-то звонки повторно выгребаем звонки, проверка на дубли - есть
            $date_from = date('Y-m-d H:i:s', strtotime('-87 minutes'));
            $date_to = date('Y-m-d H:i:s', strtotime('-75 minutes'));
            $report->sendToAPI(urlencode($date_from), urlencode($date_to));
      
        }
    }

    /*
     * отправка заявок на перезвон клиенту
     * проверяем очередь из заявок на перезвон и отправляем запрос через сокеты в систему астерикса
     *  обзвон с пнд по пятницу
в пнд с 12.00 до 18.00
в другие дни с 09.30 до 18.00
     */
    public function actionCallBack(){

        //получаем номер дня в неделе текущего дня
        $day = date('N', time());
        // если выходные дни, 6,7 - дни недели, то не запускаем
        if($day>0 && $day<6){
            //определяем время запуска - можно ли отправлять заявки
            $current_hour = date('H', time());//текущий час запуска
            $current_minute = date('i', time());//текущая минута запуска
            //переводим минуты и часы в секунды
            $current_sec = ($current_minute*60)+($current_hour*3600);


            //в пнд с 12.00 до 18.00
            if($day==1){
                $time_from = (12*3600);
                $time_to = 19*3600;
            }else{
                //в другие дни с 09.30 до 18.00
                $time_from = (9*3600)+(30*60);
                $time_to = 19*3600;
            }

            if($current_sec>=$time_from && $current_sec<=$time_to){

                //чистка старых заявок на перезвон
                Callback::clearOldCalls();

                //копируем Новые данные за интервал времени - ВДРУГ есть звонки по которым НЕ надо делать перезвон
                $report = new CallPhone();
                //$report->intervalMinuts = Yii::app()->params['cronInterval'];
                //$report->run();
                $date_from = date('Y-m-d H:i:s', strtotime('-70 minutes'));
                $date_to = date('Y-m-d H:i:s', strtotime('-55 minutes'));
                $report->sendToAPI(urlencode($date_from), urlencode($date_to));

                //echo $current_sec;
                //die(YiiBase::app()->params['call_back_host']);
                //получаем заявку на автодозвон из списка и отправляем её через сокеты
                Callback::sendCallBackRequest();
            }
        }
    }

    /*
     * опрашиваем сервер на получение списка занятых каналов на тек. момент
     * цикл расчитан на 2 минуты работы+крон запуск задачи каждые 2 минуты
     */
    public function actionBusychannels(){
        $i = 0;
        //echo 'start='.date('H:i:s',strtotime('-1 hours')).PHP_EOL;die();
        for($i=0;$i<11;$i++){
            //array('time', 'json')
            $data = Channels::busyChannels();
            //echo date('H:i:s', $data['time']).PHP_EOL;
            if(!empty($data['json'])){
                $channel = new Channels();
                $channel->event_time = $data['time'];
                $channel->busy_channels = json_encode($data['json']);
                if($channel->validate()){
                    $channel->save();
                }else{
                    echo '<pre>'; print_r($channel->errors);
                }
            }
            unset($data);
            if($i!==10){
                sleep(10);
            }
        }
        //очищаем старые данные
        $old_time = strtotime('-3 hours');
        YiiBase::app()->db->createCommand('DELETE FROM {{channels}} WHERE event_time<:old_time')->bindValue(':old_time', $old_time, PDO::PARAM_INT)->execute();
    }
}