<?php
/* @var $this OfficeManagerController */
/* @var $model OfficeManager */
/*
$this->breadcrumbs=array(
	'Office Managers'=>array('index'),
	'Manage',
);*/

$this->menu=array(
	array('label'=>'List OfficeManager', 'url'=>array('index')),
	array('label'=>'Create OfficeManager', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#office-manager-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Офисы</h1>


<?php //echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php
/*$this->renderPartial('_search',array(
	'model'=>$model,
));*/
 ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'office-manager-grid',
	'dataProvider'=>$dataProvider,
	//'filter'=>$model,
    'template'=>'{items}{pager}',
	'columns'=>array(
		//'id',
		//
		'code',
        'title',
		'rus_title',
		array(
			'class'=>'CButtonColumn',
            'template'=>'{update}'
		),
	),
)); ?>
