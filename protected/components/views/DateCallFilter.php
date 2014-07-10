<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 08.07.14
 * Time: 9:04
 */
?>
<style>
    #DateCallInterval{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'DateCallInterval',
    'options' => array(
        'title' => 'Фильтрация:Дата звонка',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'interval-form-DateCall',
));

$DateCall_from = '';
if(isset($_GET['DateCall_from'])){
    if(!empty($_GET['DateCall_from'])){
        $DateCall_from = $_GET['DateCall_from'];
    }
}

$DateCall_to = '';
if(isset($_GET['DateCall_to'])){
    if(!empty($_GET['DateCall_to'])){
        $DateCall_to = $_GET['DateCall_to'];
    }
}

echo CHtml::label('От','От');
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name' => 'DateCall_from',
    'value' => $DateCall_from,
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
    ),
));
echo /*CHtml::telField('DateCall_from',$DateCall_from).*/'<br>';


echo CHtml::label('До', 'До');
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name' => 'DateCall_to',
    'value' => $DateCall_to,
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
    ),
));

echo /*CHtml::telField('DateCall_to',$DateCall_to).*/'<br>';

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_DateCall'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'filter_interval_DateCall'));

$data = '';

if(isset($_GET['Report[date_call]'])){
    $data = $_GET['Report[date_call]'];
}

echo CHtml::hiddenField('Report[date_call]', $data, array('id'=>'interval_filter_DateCall'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_DateCall',function(){

            var cheked_ps =  $('#interval-form-DateCall').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#interval_filter_DateCall').val(cheked_ps);

            $( "#DateCallInterval" ).dialog( "close" );

            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#filter_interval_DateCall',function(){
            $("#DateCallInterval").dialog("open");
            return false;
        })
    })
</script>