<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.07.14
 * Time: 9:37
 */ 
?>

<style>
    #TimeWaitInterval{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'TimeWaitInterval',
    'options' => array(
        'title' => 'Фильтрация:Время ожидания',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'interval-form-TimeWait',
));

$TimeWait_from = '';
if(isset($_GET['TimeWait_from'])){
    if(!empty($_GET['TimeWait_from'])){
        $TimeWait_from = $_GET['TimeWait_from'];
    }
}

$TimeWait_to = '';
if(isset($_GET['TimeWait_to'])){
    if(!empty($_GET['TimeWait_to'])){
        $TimeWait_to = $_GET['TimeWait_to'];
    }
}

echo CHtml::label('От','От');
echo CHtml::telField('TimeWait_from',$TimeWait_from).'<br>';



echo CHtml::label('До', 'До');
echo CHtml::telField('TimeWait_to',$TimeWait_to).'<br>';

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_TimeWait'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'filter_interval_TimeWait'));

$data = '';

if(isset($_GET['Report[date_call]'])){
    $data = $_GET['Report[date_call]'];
}

echo CHtml::hiddenField('Report[date_call]', $data, array('id'=>'interval_filter_TimeWait'));

?>

<script>
    $(function(){

        $(document).on('click', '#btn_accept_TimeWait',function(){

            var cheked_ps =  $('#interval-form-TimeWait').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#interval_filter_TimeWait').val(cheked_ps);

            $( "#TimeWaitInterval" ).dialog( "close" );

            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#filter_interval_TimeWait',function(){
            $("#TimeWaitInterval").dialog("open");
            return false;
        })
    })
</script>