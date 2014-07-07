<?php
/* @var $this OfficeManagerController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Office Managers',
);

$this->menu=array(
	array('label'=>'Create OfficeManager', 'url'=>array('create')),
	array('label'=>'Manage OfficeManager', 'url'=>array('admin')),
);
?>

<h1>Office Managers</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
