<?php
/* @var $this OfficeManagerController */
/* @var $model OfficeManager */

$this->breadcrumbs=array(
	'Office Managers'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List OfficeManager', 'url'=>array('index')),
	array('label'=>'Manage OfficeManager', 'url'=>array('admin')),
);
?>

<h1>Create OfficeManager</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>