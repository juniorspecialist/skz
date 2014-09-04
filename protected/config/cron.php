<?php
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Система контроля звонков',
    // preloading 'log' component
    'preload'=>array('log'),
    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.models.Callback',
        'application.components.*',
        'application.extensions.*',
    ),
    // application components
    'components'=>array(

            /*
        'cache'=>array(
            'class'=>'system.caching.CFileCache',
        ),*/

        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=skz',
            'class'=>'system.db.CDbConnection',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'tablePrefix' => 'tbl_',
        ),

        //коннект к БД-Астерикса для выборки данных по крону и формирования справочнков
        'db1'=>array(
            'connectionString' => 'mysql:host=80.84.116.238;dbname=asterisk',
            'class'=>'system.db.CDbConnection',
            'emulatePrepare' => true,
            'username' => 'freepbxu',
            'password' => 'xahDi4no',
            'charset' => 'utf8',
        ),

        //коннект к БД-Астерикса для выборки данных по крону и формирования справочнков
        'db2'=>array(
            'connectionString' => 'mysql:host=80.84.116.238;dbname=asteriskcdrdb',
            'class'=>'system.db.CDbConnection',
            'emulatePrepare' => true,
            'username' => 'freepbxu',
            'password' => 'xahDi4no',
            'charset' => 'utf8',
        ),

        'errorHandler'=>array(
            // use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
                // uncomment the following to show log messages on web pages

                array(
                    'class'=>'CWebLogRoute',
                ),

            ),
        ),
    ),
    // using Yii::app()->params['paramName']
    'params'=>array(
        // this is used in contact page
        'cronInterval'=>8330,

        /*
         * настройки для отправки запросов на обратный звонок
         * запросы в астерикс отправляем через промежуточное звено - скрипт, т.к. астерикс не открыт нарушу, только локально можно ему отправлять запросы
         */
        'call_back_host'=>'80.84.116.238',//IP сервера где лежит скрипт, который будет перенаправлять запросы в астерикс
        'call_back_port'=>5038,//порт используемый для отправки данные через сокеты
        'call_back_admin'=>'admin',//логин админа в системе астерикса
        'call_back_pass'=>'GgFyygFX96',//пароль админа для авторизации и отправки запросов
        //список соответсвий по номеру офиса
        'call_back_office_list'=>array(
            3=>'local/300@from-queue',//электрозавод
            4=>'local/400@from-queue',//юбилейный
            5=>'local/500@from-queue',//яблочково
        ),
        //список номеров по которым НЕ делаем автоперезвон
        'call_back_exeption_list'=>array('74952139652', '74952139177','anonymous'),

        'call_back_context'=>'from-internal',
    ),
);