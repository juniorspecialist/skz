<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 08.07.14
 * Time: 10:24
 */ 
?>

<style>
    #DurationCallCallInterval{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'DurationCallCallInterval',
    'options' => array(
        'title' => 'Фильтрация:Продолжительность звонка',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'interval-form-DurationCallCall',
));

$DurationCallCall_from = '';
if(isset($_GET['DurationCallCall_from'])){
    if(!empty($_GET['DurationCallCall_from'])){
        $DurationCallCall_from = $_GET['DurationCallCall_from'];
    }
}

$DurationCallCall_to = '';
if(isset($_GET['DurationCallCall_to'])){
    if(!empty($_GET['DurationCallCall_to'])){
        $DurationCallCall_to = $_GET['DurationCallCall_to'];
    }
}

echo CHtml::label('От','От');
echo CHtml::telField('DurationCallCall_from',$DurationCallCall_from).'<br>';



echo CHtml::label('До', 'До');
echo CHtml::telField('DurationCallCall_to',$DurationCallCall_to).'<br>';

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_DurationCallCall'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'filter_interval_DurationCallCall'));

$data = '';

if(isset($_GET['Report[date_call]'])){
    $data = $_GET['Report[date_call]'];
}

echo CHtml::hiddenField('Report[date_call]', $data, array('id'=>'interval_filter_DurationCallCall'));

?>

<script>
    $(function(){

        $(document).on('click', '#btn_accept_DurationCallCall',function(){

            var cheked_ps =  $('#interval-form-DurationCallCall').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#interval_filter_DurationCallCall').val(cheked_ps);

            $( "#DurationCallCallInterval" ).dialog( "close" );

            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#filter_interval_DurationCallCall',function(){
            $("#DurationCallCallInterval").dialog("open");
            return false;
        })
    })
</script>