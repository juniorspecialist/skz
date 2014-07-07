<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.07.14
 * Time: 11:23
 */
?>
<style>
    #redirectFilter{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'redirectFilter',
    'options' => array(
        'title' => 'Фильтрация:Цепочка пройденных переадресаций',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'search-word-form-redirect',
));

if(isset($_GET['radio_selected_redirect'])){
    if(!empty($_GET['radio_selected_redirect'])){
        $selected = $_GET['radio_selected_redirect'];
    }
}else{
    $selected = 'search_word_accept_redirect';
}


echo CHtml::radioButtonList('radio_selected_redirect',
    $selected,
    array('search_word_accept_redirect'=>'Удовлетворяет регулярному выражению',
        'search_word_not_accept_redirect'=>'Не удовлетворяет регулярному выражению'
    ),
    array(
        //'labelOptions'=>array('style'=>'display:inline'), // add this code
        'separator'=>'<br>',
    ));


$reg_expression = '';

if(isset($_GET['search_word_accept_reg_redirect'])){$reg_expression = $_GET['search_word_accept_reg_redirect'];}

echo '<br>'.CHtml::telField('search_word_accept_reg_redirect',$reg_expression);

echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_search_word_redirect'));


$this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'redirectFilter_dialog'));

if(isset($_GET['Report[chain_passed_redirects]'])){
    $data = $_GET['Report[chain_passed_redirects]'];
}else{
    $data = '';
}

echo CHtml::hiddenField('Report[chain_passed_redirects]', $data, array('id'=>'serch_word_filter_redirect'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_search_word_redirect',function(){

            var cheked_ps =  $('#search-word-form-redirect').serialize();

            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#serch_word_filter_redirect').val(cheked_ps);

            $( "#redirectFilter" ).dialog( "close" );

            //$('#statistics-parsing-grid').yiiGridView('update');
            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#redirectFilter_dialog',function(){
            $("#redirectFilter").dialog("open");
            return false;
        })
    })
</script>