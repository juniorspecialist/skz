<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.07.14
 * Time: 10:14
 */
?>
<style>
    #CallerIdFilter{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'CallerIdFilter',
    'options' => array(
        'title' => 'Фильтрация:Номер клиента',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'search-word-form-CallerId',
));

if(isset($_GET['radio_selected_CallerId'])){
    if(!empty($_GET['radio_selected_CallerId'])){
        $selected = $_GET['radio_selected_CallerId'];
    }
}else{
    $selected = 'search_word_accept_CallerId';
}


echo CHtml::radioButtonList('radio_selected_CallerId',
    $selected,
    array('search_word_accept_CallerId'=>'Удовлетворяет регулярному выражению',
        'search_word_not_accept_CallerId'=>'Не удовлетворяет регулярному выражению'
    ),
    array(
        //'labelOptions'=>array('style'=>'display:inline'), // add this code
        'separator'=>'<br>',
    ));


$reg_expression = '';

if(isset($_GET['search_word_accept_reg_CallerId'])){$reg_expression = $_GET['search_word_accept_reg_CallerId'];}

echo '<br>'.CHtml::telField('search_word_accept_reg_CallerId',$reg_expression);

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_search_word_CallerId'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'CallerIdFilter_dialog'));

if(isset($_GET['Report[caller_id]'])){
    $data = $_GET['Report[caller_id]'];
}else{
    $data = '';
}

echo CHtml::hiddenField('Report[caller_id]', $data, array('id'=>'serch_word_filter_CallerId'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_search_word_CallerId',function(){

            var cheked_ps =  $('#search-word-form-CallerId').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#serch_word_filter_CallerId').val(cheked_ps);

            $( "#CallerIdFilter" ).dialog( "close" );

            //$('#statistics-parsing-grid').yiiGridView('update');
            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#CallerIdFilter_dialog',function(){
            $("#CallerIdFilter").dialog("open");
            return false;
        })
    })
</script>