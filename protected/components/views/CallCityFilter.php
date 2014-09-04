<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.07.14
 * Time: 10:46
 * 
 */

?>
<style>
    #call_cityFilter{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'call_cityFilter',
    'options' => array(
        'title' => 'Фильтрация:Город звонка',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'search-word-form-call_city',
));

if(isset($_GET['radio_selected_call_city'])){
    if(!empty($_GET['radio_selected_call_city'])){
        $selected = $_GET['radio_selected_call_city'];
    }
}else{
    $selected = 'search_word_accept_call_city';
}


echo CHtml::radioButtonList('radio_selected_call_city',
    $selected,
    array('search_word_accept_call_city'=>'Удовлетворяет регулярному выражению',
        'search_word_not_accept_call_city'=>'Не удовлетворяет регулярному выражению'
    ),
    array(
        //'labelOptions'=>array('style'=>'display:inline'), // add this code
        'separator'=>'<br>',
    ));


$reg_expression = '';

if(isset($_GET['search_word_accept_reg_call_city'])){$reg_expression = $_GET['search_word_accept_reg_call_city'];}

echo '<br>'.CHtml::telField('search_word_accept_reg_call_city',$reg_expression);

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_search_word_call_city'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'call_cityFilter_dialog'));

if(isset($_GET['Report[call_city]'])){
    $data = $_GET['Report[call_city]'];
}else{
    $data = '';
}

echo CHtml::hiddenField('Report[call_city]', $data, array('id'=>'serch_word_filter_call_city'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_search_word_call_city',function(){

            var cheked_ps =  $('#search-word-form-call_city').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#serch_word_filter_call_city').val(cheked_ps);

            $( "#call_cityFilter" ).dialog( "close" );

            //$('#statistics-parsing-grid').yiiGridView('update');
            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#call_cityFilter_dialog',function(){
            $("#call_cityFilter").dialog("open");
            return false;
        })
    })
</script>