<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 08.07.14
 * Time: 9:33
 */
?>
<style>
    #TimeStartCallInterval{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'TimeStartCallInterval',
    'options' => array(
        'title' => 'Фильтрация:Время начала звонка',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'interval-form-TimeStartCall',
));

$TimeStartCall_from = '';
if(isset($_GET['TimeStartCall_from'])){
    if(!empty($_GET['TimeStartCall_from'])){
        $TimeStartCall_from = $_GET['TimeStartCall_from'];
    }
}

$TimeStartCall_to = '';
if(isset($_GET['TimeStartCall_to'])){
    if(!empty($_GET['TimeStartCall_to'])){
        $TimeStartCall_to = $_GET['TimeStartCall_to'];
    }
}

echo CHtml::label('От','От');
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name' => 'TimeStartCall_from',
    'value' => $TimeStartCall_from,
    'options'=>array(
        'showButtonPanel'=>true,
        'dateFormat'=>'yy-mm-dd',//Date format 'mm/dd/yy','yy-mm-dd','d M, y','d MM, y','DD, d MM, yy'
        'showOtherMonths' => true,      // show dates in other months
        'selectOtherMonths' => true,    // can seelect dates in other months
        'changeYear' => true,           // can change year
        'changeMonth' => true,
    ),
    'htmlOptions' => array(
        'size' => '20',         // textField size
        'maxlength' => '12',    // textField maxlength
        'id'=>'time_start_call_from'
    ),
));

echo /*CHtml::telField('TimeStartCall_from',$TimeStartCall_from).*/'<br>';


echo CHtml::label('До', 'До');
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name' => 'TimeStartCall_to',
    'value' => $TimeStartCall_to,
    'options'=>array(
        'showButtonPanel'=>true,
        'dateFormat'=>'yy-mm-dd',//Date format 'mm/dd/yy','yy-mm-dd','d M, y','d MM, y','DD, d MM, yy'
        'showOtherMonths' => true,      // show dates in other months
        'selectOtherMonths' => true,    // can seelect dates in other months
        'changeYear' => true,           // can change year
        'changeMonth' => true,
    ),
    'htmlOptions' => array(
        'size' => '20',         // textField size
        'maxlength' => '12',    // textField maxlength
        'id'=>'time_start_call_to'
    ),
));

echo /*CHtml::telField('TimeStartCall_to',$TimeStartCall_to).*/'<br>';

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_TimeStartCall'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'filter_interval_TimeStartCall'));

$data = '';

if(isset($_GET['Report[date_call]'])){
    $data = $_GET['Report[date_call]'];
}

echo CHtml::hiddenField('Report[date_call]', $data, array('id'=>'interval_filter_TimeStartCall'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<?php
//Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery-ui-timepicker-addon.js',CClientScript::POS_END);
//Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/jquery-ui-timepicker-addon.css');
?>
<script>
    $(function(){

        $('#time_start_call_from,#time_start_call_to').timepicker({
            timeOnlyTitle: 'Выберите время',
            timeText: 'Время',
            hourText: 'Часы',
            minuteText: 'Минуты',
            secondText: 'Секунды',
            currentText: 'Сейчас',
            closeText: 'Закрыть'
        });

        $(document).on('click', '#btn_accept_TimeStartCall',function(){

            var cheked_ps =  $('#interval-form-TimeStartCall').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#interval_filter_TimeStartCall').val(cheked_ps);

            $( "#TimeStartCallInterval" ).dialog( "close" );

            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#filter_interval_TimeStartCall',function(){
            $("#TimeStartCallInterval").dialog("open");
            return false;
        })
    })
</script>