<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.07.14
 * Time: 11:07
 */
?>
<style>
#destFilter{
display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'destFilter',
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
    'id'=>'search-word-form-dest',
));

if(isset($_GET['radio_selected_dest'])){
    if(!empty($_GET['radio_selected_dest'])){
        $selected = $_GET['radio_selected_dest'];
    }
}else{
    $selected = 'search_word_accept_dest';
}


echo CHtml::radioButtonList('radio_selected_dest',
    $selected,
    array('search_word_accept_dest'=>'Удовлетворяет регулярному выражению',
        'search_word_not_accept_dest'=>'Не удовлетворяет регулярному выражению'
    ),
    array(
        //'labelOptions'=>array('style'=>'display:inline'), // add this code
        'separator'=>'<br>',
    ));


$reg_expression = '';

if(isset($_GET['search_word_accept_reg_dest'])){$reg_expression = $_GET['search_word_accept_reg_dest'];}

echo '<br>'.CHtml::telField('search_word_accept_reg_dest',$reg_expression);

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_search_word_dest'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'destFilter_dialog'));

if(isset($_GET['Report[destination_call]'])){
    $data = $_GET['Report[destination_call]'];
}else{
    $data = '';
}

echo CHtml::hiddenField('Report[destination_call]', $data, array('id'=>'serch_word_filter_dest'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_search_word_dest',function(){

            var cheked_ps =  $('#search-word-form-dest').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#serch_word_filter_dest').val(cheked_ps);

            $( "#destFilter" ).dialog( "close" );

            //$('#statistics-parsing-grid').yiiGridView('update');
            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#destFilter_dialog',function(){
            $("#destFilter").dialog("open");
            return false;
        })
    })
</script>