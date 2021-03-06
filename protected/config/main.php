<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Система контроля звонков',

	// preloading 'log' component
	'preload'=>array('log'),
    'defaultController' => 'report/index',

    // язык поумолчанию
    'sourceLanguage' => 'en_US',
    'language' => 'ru',

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
        'application.extensions.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool

		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'1',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),

	),

	// application components
	'components'=>array(

        // установим некоторые значения - по умолчанию
        'widgetFactory'=>array(
            'widgets'=>array(
                'CLinkPager'=>array(
                    'maxButtonCount'=>5,
                    //'cssFile'=>false,
                    'pageSize'=>100,

                ),
                'CJuiDatePicker'=>array(
                    'language'=>'ru',
                ),
            ),
        ),

        'cache'=>array(
            'class'=>'system.caching.CFileCache',
        ),

		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format

		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
            'showScriptName'=>false,
		),


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
			'errorAction'=>'report/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages



                    /*
				array(
					'class'=>'CWebLogRoute',
				),*/

			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);