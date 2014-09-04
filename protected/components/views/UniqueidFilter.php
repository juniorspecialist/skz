<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.07.14
 * Time: 8:49
 */
?>
<style>
    #UniqueidFilter{
        display: none;
    }
</style>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'UniqueidFilter',
    'options' => array(
        'title' => 'Фильтрация:ID звонка',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'auto',
        'height'=>'auto',
    ),
));

    $form=$this->beginWidget('CActiveForm', array(
        'id'=>'search-word-form-uniqueid',
        // Please note: When you enable ajax validation, make sure the corresponding
        // controller action is handling ajax validation correctly.
        // There is a call to performAjaxValidation() commented in generated controller code.
        // See class documentation of CActiveForm for details on this.
        //'enableAjaxValidation'=>false,
    ));


    if(isset($_GET['radio_selected_uniqueid'])){
        if(!empty($_GET['radio_selected_uniqueid'])){
            $selected = $_GET['radio_selected_uniqueid'];
        }
    }else{
        $selected = 'search_word_accept_uniqueid';
    }


    echo CHtml::radioButtonList('radio_selected_uniqueid',
        $selected,
        array('search_word_accept_uniqueid'=>'Удовлетворяет регулярному выражению',
            'search_word_not_accept_uniqueid'=>'Не удовлетворяет регулярному выражению'
        ),
        array(
            //'labelOptions'=>array('style'=>'display:inline'), // add this code
            'separator'=>'<br>',
        ));


    $reg_expression = '';

    if(isset($_GET['search_word_accept_reg_uniqueid'])){$reg_expression = $_GET['search_word_accept_reg_uniqueid'];}

    echo '<br>'.CHtml::telField('search_word_accept_reg_uniqueid',$reg_expression);

    echo '<br>'.CHtml::button('Применить', array('id'=>'btn_accept_search_word_uniqueid'));


    $this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

echo CHtml::link('Фильтр', '#', array('id'=>'UniqueidFilter_dialog'));

if(isset($_GET['Report[uniqueid]'])){
    $data = $_GET['Report[uniqueid]'];
}else{
    $data = '';
}

echo CHtml::hiddenField('Report[uniqueid]', $data, array('id'=>'serch_word_filter_uniqueid'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_accept_search_word_uniqueid',function(){

            var cheked_ps =  $('#search-word-form-uniqueid').serialize();

            //console.log(cheked_ps);
            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#serch_word_filter_uniqueid').val(cheked_ps);

            //$('#mydialog').hide();
            $( "#UniqueidFilter" ).dialog( "close" );

            //$('#statistics-parsing-grid').yiiGridView('update');
            $('#report-grid').yiiGridView('update', {
                data: cheked_ps
            });
            //*/
        })

        /*
         кликаем по ссылке и вызываем окно фильтра
         */
        $(document).on('click', '#UniqueidFilter_dialog',function(){
            $("#UniqueidFilter").dialog("open");
            return false;
        })
    })
</script>