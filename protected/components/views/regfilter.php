<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.07.14
 * Time: 11:13
 */
/*
 * фильтр создаёт ссылку которая при клике показывает диалоговое окно - модельное
 * диалоговое окно служит для выбора использовать или Не использовать(ищем совпадение или отрицание от совпадения) по регулярному выражению из текстового поля
 */
?>
<style>
    .modal_filter{
        display: none;
    }
</style>
<?php
echo CHtml::link('Фильтр', '#',
    array(
        'class'=>'custom_filter',
        'onclick'=>'$("#'.$this->params['id_modal'].'").dialog("open"); return false;',
        'model'=>$this->params['model'],
        'attribute'=>$this->params['attribute'],
    )
);

$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => $this->params['id_modal'],

    'options' => array(
        'title' => $this->params['label_field'],
        'class'=>'modal_filter',
        'autoOpen' => false,
        'modal' => true,
        'resizable'=> false,
        'width'=>'450px',

    ),
));


    $form = $this->beginWidget('CActiveForm', array(
        'id' => $this->params['id_modal'].'-form',
        'enableClientValidation' => false,
        'clientOptions' => array(
            'validateOnSubmit' => true,

        ),
    ));


    if(isset($_GET['radio_selected_'.$this->params['attribute']])){
        if(!empty($_GET['radio_selected_'.$this->params['attribute']])){
            $selected = $_GET['radio_selected_'.$this->params['attribute']];
        }
    }else{
        $selected = '';
    }


    echo CHtml::radioButtonList('radio_selected',
        $selected,
        array('search_word_accept_'.$this->params['attribute']=>'Удовлетворяет регулярному выражению',
            'search_word_not_accept_'.$this->params['attribute']=>'Не удовлетворяет регулярному выражению'
        ),
        array(
            //'labelOptions'=>array('style'=>'display:inline'), // add this code
            'separator'=>'<br>',
        ));


    $reg_expression = '';

    if(isset($_GET['search_word_accept_reg'])){
        $reg_expression = $_GET['search_word_accept_reg'];
    }
    echo '<br>'.CHtml::telField('search_word_accept_reg',$reg_expression);

    echo '<br>'.CHtml::button('Применить', array('id'=>'btn_'.$this->params['id_modal']));


    $this->endWidget();

$this->endWidget('zii.widgets.jui.CJuiDialog');

//echo CHtml::link('Фильтр', '#', array('id'=>'filter_search_word_dialog'));

if(isset($_GET[$this->params['model'].'['.$this->params['attribute'].']'])){
    $data = $_GET[$this->params['model'].'['.$this->params['attribute'].']'];
}else{
    $data = '';
}

echo CHtml::hiddenField($this->params['model'].'['.$this->params['attribute'].']', $data, array('style'=>'display:none'));

?>

<!--Обработчик выбора чекбоксов из списка диалогового окна и применение их как фильтра   -->
<script>
    $(function(){
        $(document).on('click', '#btn_'.<?php echo $this->params['id_modal'];?>,function(){

            var checked_params =  $('#'.<?php echo $this->params['id_modal'].'-form'; ?>).serialize();

            //console.log(cheked_ps);
            //выбрали/не выбрали галочки - нажали на кнопку применения галочек к выборке
            $('#').val(checked_params);

            $( "#".<?php echo $this->params['id_modal'];?>).dialog( "close" );

            $('#report-grid').yiiGridView('update', {
                data: checked_params
            });
        })
    })
</script>