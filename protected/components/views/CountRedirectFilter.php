<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.07.14
 * Time: 9:42
 */
?>

<style>
    #CountRedirectInterval{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'CountRedirectInterval',
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
    'id'=>'interval-form-CountRedirect',
));

$CountRedirect_from = '';
if(isset($_GET['CountRedirect_from'])){
    if(!empty($_GET['CountRedirect_from'])){
        $CountRedirect_from = $_GET['CountRedirect_from'];
    }
}

$CountRedirect_to = '';
if(isset($_GET['CountRedirect_to'])){
    if(!empty($_GET['CountRedirect_to'])){
        $CountRedirect_to = $_GET['CountRedirect_to'];
    }
}

echo CHtml::label('От','От');
echo CHtml::telField('CountRedirect_from',$CountRedirect_from).'<br>';



echo CHtml::label('До', 'До');
echo CHtml::telField('CountRedirect_to',$CountRedirect_to).'<br>';

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_CountRedirect'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'filter_interval_CountRedirect'));

$data = '';

if(isset($_GET['Report[date_call]'])){
    $data = $_GET['Report[date_call]'];
}

echo CHtml::hiddenField('Report[date_call]', $data, array('id'=>'interval_filter_CountRedirect'));

?>

<script>
    $(function(){

        $(document).on('click', '#btn_accept_CountRedirect',function(){

            var cheked_ps =  $('#interval-form-CountRedirect').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#interval_filter_CountRedirect').val(cheked_ps);

            $( "#CountRedirectInterval" ).dialog( "close" );

            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#filter_interval_CountRedirect',function(){
            $("#CountRedirectInterval").dialog("open");
            return false;
        })
    })
</script>