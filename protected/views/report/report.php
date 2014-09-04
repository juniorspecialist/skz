<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24.06.14
 * Time: 11:22
 */

echo  "<strong>Список примененных фильтров:".$model->AcceptList.'</strong>';

$this->widget('GridView', array(
    'id'=>'report-grid',
    'dataProvider'=>$dataProvider,
    'filter'=>$model,
    'distinct_caller_phone'=>$model->cnt,
    //'template'=>'{items}{pager}',
    'ajaxUpdate'=>false,
    //'enableSorting'=>true,
    'columns'=>array(
        array(
            'header'=>'ID звонка',
            'type'=>'raw',
            'value'=>'$data->uniqueid',
            'name'=>'uniqueid',
            'filter'=>$this->widget('application.components.UniqueidFilter', array(), true),
            //'htmlOptions'=>array('style'=>'width:80px;')
        ),
        array(
            'header'=>'Номер клиента',
            'type'=>'raw',
            'value'=>'$data->caller_id',
            'name'=>'caller_id',
            'filter'=>$this->widget('application.components.CallerIdFilter', array(), true)
        ),
        array(
            'header'=>'DID',
            'type'=>'raw',
            'value'=>'$data->did',
            'name'=>'did',
            'filter'=>$this->widget('application.components.DidFilter', array(), true)
        ),
        array(
            'type'=>'raw',
            'header'=>'Город звонка',
            'value'=>'$data->call_city',
            'name'=>'call_city',
            'filter'=>$this->widget('application.components.CallCityFilter', array(), true)
        ),
        array(
            'header'=>'Дата звонка',
            'type'=>'raw',
            'name'=>'date_call',
            'value'=>'$data->date_call',
            'filter'=>$this->widget('application.components.DateCallFilter', array(), true)
        ),
        array(
            'header'=>'Время начала разговора',
            'type'=>'raw',
            'name'=>'time_start_call',
            'value'=>'$data->time_start_call',
            'filter'=>$this->widget('application.components.TimeStartCallFilter', array(), true)
        ),
        array(
            'header'=>'Время конца разговора',
            'type'=>'raw',
            'name'=>'time_end_call',
            'value'=>'$data->time_end_call',
            'filter'=>$this->widget('application.components.TimeEndCallFilter', array(), true)
        ),
        array(
            'header'=>'Продолжительность звонка',
            'type'=>'raw',
            'name'=>'duration_call',
            'value'=>'$data->duration_call',  //
            'filter'=>$this->widget('application.components.DurationCallFilter', array(), true)
        ),
        array(
            'header'=>'Destination звонка',
            'type'=>'raw',
            'name'=>'destination_call',
            'value'=>'$data->destination_call',
            'filter'=>$this->widget('application.components.DestFilter', array(), true)
        ),
        array(
            'header'=>'Офис',
            'type'=>'raw',
            'name'=>'office_call_id',
            'value'=>'$data->callOffice',
            //'value'=>'$data->officeсall->title',
            //'value'=>'($data->office_call_id!==0)?$data->officeсall->title:""',
            'filter'=>CHtml::activeDropDownList($model, 'office_call_id', OfficeManager::getListOffice(), array('empty'=>'Все')),
        ),
        array(
            'header'=>'Сайт',
            'type'=>'raw',
            'value'=>'$data->site',
            'name'=>'site_id',
            'filter'=>CHtml::activeDropDownList($model, 'site_id', CHtml::listData(Site::getSitesList(), 'id', 'site') , array('empty'=>'Все')),
               //echo CHtml::dropDownList('categories', $category,$list,array('empty' => '(Select a category'))),
        ),
        array(
            'header'=>'Направление звонка',
            'type'=>'raw',
            'name'=>'call_diraction',
            'value'=>'$data->calldiraction',
            'filter' => array(1 => 'Входящий', 2 => 'Исходящий'),
        ),
        array(
            'header'=>'Статус обработки звонка',
            'type'=>'raw',
            //'value'=>'$data->statuscall',
             'name'=>'status_call',
            //'value'=>'$data->status_call',
            //'value'=>'($data->status_call==1)? "Отвечен":"Не отвечен"',
            'value'=>'$data->StatusToTbl',
            'filter' => array(Report::CALL_ANSWERED => 'Отвечен', Report::CALL_NO_ANSWER => 'Не отвечен', Report::CALL_RESET_CLIENT=>'Сброшен клиентом'),
        ),

        array(
            'header'=>'Менеджер звонка',
            //'type'=>'raw',
            'name'=>'manager_call_id',
            'value'=>'($data->manager_call_id!=0)?$data->managerCall->fio:""',
            //'filter' => CHtml::dropDownList($model,'manager_call_id',CHtml::listData(OfficeManager::model()->findAll(), 'id', 'title')),
            'filter'=>CHtml::activeDropDownList($model, 'manager_call_id', CHtml::listData(Manager::model()->findAll(), 'id', 'fio'), array('empty'=>'Все')),

        ),
        array(
            'header'=>'Время ожидания клиента',
            'type'=>'raw',
            'name'=>'waiting_time',
            'value'=>'$data->waiting_time',
            'filter'=>$this->widget('application.components.TimeWaitFilter', array(), true)
        ),
        array(
            'header'=>'Кол-во переадресаций',
            'type'=>'raw',
            'name'=>'count_redirect',
            'value'=>'$data->count_redirect',
            'filter'=>$this->widget('application.components.CountRedirectFilter', array(), true)
        ),
        array(
            'header'=>'Цепочка пройденных переадресаций',
            'type'=>'raw',
            'value'=>'$data->chain_passed_redirects',
            'name'=>'chain_passed_redirects',
            'filter'=>$this->widget('application.components.RedirectFilter', array(), true)
        ),
        array(
            'header'=>'Запись звонка',
            'type'=>'raw',
            //'value'=>'$data->rec_call',
            'value'=>'$data->LinkDownloadRec',
        ),

        array(
            'header'=>'Автоперезвон',
            'type'=>'raw',
            'name'=>'call_back_status',
            'value'=>'$data->callbackstatus',
            'filter' => array(Report::CALL_BACK_WAIT => 'Ждёт отправки заявки на перезвон', Report::CALL_BACK_SEND => 'Отправили заявку на перезвон', Report::CALL_BACK_ACTION_CLIENT=>'Перезвонили клиенту'),
        ),
            /*
        array(
            'header'=>'Поисковая фраза',
            'type'=>'raw',
            'name'=>'search_word',
            'value'=>'$data->search_word',
        ),*/
        array(
            'class'=>'CButtonColumn',
            'visible'=>false,
        ),
    ),
));

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery-ui-timepicker-addon.js',CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/jquery-ui-timepicker-addon.css');