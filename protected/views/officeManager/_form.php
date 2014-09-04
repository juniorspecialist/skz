<?php
/* @var $this OfficeManagerController */
/* @var $model OfficeManager */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'office-manager-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

<!--	<p class="note">Fields with <span class="required">*</span> are required.</p>-->

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>256)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'code'); ?>
		<?php echo $form->textField($model,'code',array('size'=>40,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'rus_title'); ?>
		<?php echo $form->textField($model,'rus_title',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'rus_title'); ?>
	</div>


    <?php
        //выводим список менеджеров и отмечаем галочками тех, кто подвязан к текущему офису
        $this->renderPartial('_managers', array('managers'=>$managers, 'listManagers'=>Manager::getListManagers()));
    ?>

    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Сохранить'); ?>
    </div>


<?php $this->endWidget(); ?>

</div><!-- form -->