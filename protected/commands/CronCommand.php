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

        //проверим не отключили ли мы запуск по крону задания, если интервал более "0" запускаем задание
        if(Yii::app()->params['cronInterval']>0){
            //синхронизируем справочники
            $sync = new Synchronization();
            $sync->run();

            //копируем Новые данные за интервал времени
            $report = new CallPhone();
            $report->intervalMinuts = Yii::app()->params['cronInterval'];
            $report->run();

        }
    }

    /*
     * отправка заявок на перезвон клиенту
     * проверяем очередь из заявок на перезвон и отправляем запрос через сокеты в систему астерикса
     * В период с 9.30 МСК по 17.00 МСК скрипт отправляет номера из базы на обработку, с частотой 1 номер в 5 минут
     * разрешено запускать ТОЛЬКО В РАБОЧИЕ ДНИ с понедельника по пятницу
     */
    public function actionCallBack(){
        //получаем номер дня в неделе текущего дня
        $day = date('N', time());
        // если выходные дни, 6,7 - дни недели, то не запускаем
        if($day>5){
        }else{
            //определяем время запуска - можно ли отправлять заявки
            $current_hour = date('H', time());//текущий час запуска
            $current_minute = date('i', time());//текущая минута запуска
            //переводим минуты и часы в секунды
            $current_sec = ($current_minute*60)+($current_hour*3600);

            $time_from = (11*3600);
            $time_to = 18*3600;

            if($current_sec>=$time_from && $current_sec<=$time_to){
                //echo $current_sec;
                //die(YiiBase::app()->params['call_back_host']);
                //получаем заявку на автодозвон из списка и отправляем её через сокеты
                Callback::sendCallBackRequest();
            }
        }
    }
}