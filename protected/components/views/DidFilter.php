<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.07.14
 * Time: 10:31
 */
?>
<style>
    #DidFilter{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'DidFilter',
    'options' => array(
        'title' => 'Фильтрация:DID',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'search-word-form-Did',
));

if(isset($_GET['radio_selected_Did'])){
    if(!empty($_GET['radio_selected_Did'])){
        $selected = $_GET['radio_selected_Did'];
    }
}else{
    $selected = 'search_word_accept_Did';
}


echo CHtml::radioButtonList('radio_selected_Did',
    $selected,
    array('search_word_accept_Did'=>'Удовлетворяет регулярному выражению',
        'search_word_not_accept_Did'=>'Не удовлетворяет регулярному выражению'
    ),
    array(
        //'labelOptions'=>array('style'=>'display:inline'), // add this code
        'separator'=>'<br>',
    ));


$reg_expression = '';

if(isset($_GET['search_word_accept_reg_Did'])){$reg_expression = $_GET['search_word_accept_reg_Did'];}

echo '<br>'.CHtml::telField('search_word_accept_reg_Did',$reg_expression);

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_search_word_Did'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'DidFilter_dialog'));

if(isset($_GET['Report[did]'])){
    $data = $_GET['Report[did]'];
}else{
    $data = '';
}

echo CHtml::hiddenField('Report[did]', $data, array('id'=>'serch_word_filter_Did'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_search_word_Did',function(){

            var cheked_ps =  $('#search-word-form-Did').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#serch_word_filter_Did').val(cheked_ps);

            $( "#DidFilter" ).dialog( "close" );

            //$('#statistics-parsing-grid').yiiGridView('update');
            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#DidFilter_dialog',function(){
            $("#DidFilter").dialog("open");
            return false;
        })
    })
</script>