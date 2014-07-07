<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24.06.14
 * Time: 11:22
 */
$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'report-grid',
    'dataProvider'=>$dataProvider,
    'filter'=>$model,
    'template'=>'{pager}{items}{pager}',
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
        ),
        array(
            'header'=>'Время начала разговора',
            'type'=>'raw',
            'name'=>'time_start_call',
            'value'=>'$data->time_start_call',
        ),
        array(
            'header'=>'Время конца разговора',
            'type'=>'raw',
            'name'=>'time_end_call',
            'value'=>'$data->time_end_call',
        ),
        array(
            'header'=>'Продолжительность звонка',
            'type'=>'raw',
            'name'=>'duration_call',
            'value'=>'$data->duration_call',
        ),
        array(
            'header'=>'Destination звонка',
            'type'=>'raw',
            'name'=>'destination_call',
            'value'=>'$data->destination_call',
            'filter'=>$this->widget('application.components.DestFilter', array(), true)
        ),
        array(
            'header'=>'Офис звонка',
            'type'=>'raw',
            'name'=>'office_call_id',
            'value'=>'$data->callOffice',
            //'value'=>'$data->officeсall->title',
            //'value'=>'($data->office_call_id!==0)?$data->officeсall->title:""',
            'filter'=>CHtml::activeDropDownList($model, 'office_call_id', CHtml::listData(OfficeManager::model()->findAll(), 'id', 'title'), array('empty'=>'Все')),
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
            'value'=>'($data->status_call==1)? "Отвечен":"Не отвечен"',
            'filter' => array(Report::CALL_ANSWERED => 'Отвечен', Report::CALL_NO_ANSWER => 'Не отвечен'),
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
        ),
        array(
            'header'=>'Кол-во переадресаций',
            'type'=>'raw',
            'name'=>'count_redirect',
            'value'=>'$data->count_redirect',
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
            'value'=>'Chtml::link("Скачать", "#")',
        ),
        array(
            'header'=>'Источник звонка',
            'type'=>'raw',
            'name'=>'source',
            'value'=>'$data->source',
        ),
        array(
            'header'=>'Поисковая фраза',
            'type'=>'raw',
            'name'=>'search_word',
            'value'=>'$data->search_word',
        ),
        array(
            'class'=>'CButtonColumn',
            'visible'=>false,
        ),
    ),
));