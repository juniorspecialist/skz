<?php
/* @var $this OfficeManagerController */
/* @var $model OfficeManager */

$this->breadcrumbs=array(
	'Office Managers'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List OfficeManager', 'url'=>array('index')),
	array('label'=>'Create OfficeManager', 'url'=>array('create')),
	array('label'=>'View OfficeManager', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage OfficeManager', 'url'=>array('admin')),
);
?>

<h1>Редактирование информации об офисе <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model, 'managers'=>$managers)); ?>