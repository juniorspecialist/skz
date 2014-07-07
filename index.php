<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();

/*$data = YiiBase::app()->db1->createCommand('SELECT * FROM users')->queryAll();
echo '<pre>'; print_r($data);*/
$sync = new Synchronization();
$sync->run();
$report = new CallPhone();
//$report->run();


//echo 'city='.City::getCityByPhone('74997077104');