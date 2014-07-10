<?php
error_reporting(E_ALL);
// change the following paths if necessary
$yii=dirname(__FILE__).'/../framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();
 CHtml::listData(Site::getSitesList(),'id', 'site');

/*$data = YiiBase::app()->db1->createCommand('SELECT * FROM users')->queryAll();
echo '<pre>'; print_r($data);*/
/*
$sync = new Synchronization();
$sync->run();
$report = new CallPhone();
$report->run();
*/

//echo 'city='.City::getCityByPhone('74997077104');

//$data = csv_to_array('сайт-номер.csv');


//echo '<pre>'; print_r($data);
//foreach($data as $row){
    //echo '<pre>'; print_r($row);
//}

/*
$row = 1;//die();



function csv_to_array($filename='', $delimiter=',')
{
    ini_set('auto_detect_line_endings',TRUE);
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if (!$header) {
                $header = $row;
            }
            else {
                if (count($header) > count($row)) {
                    $difference = count($header) - count($row);
                    for ($i = 1; $i <= $difference; $i++) {
                        $row[count($row) + 1] = $delimiter;
                    }
                }
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}
*/
