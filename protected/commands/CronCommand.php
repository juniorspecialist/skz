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
}